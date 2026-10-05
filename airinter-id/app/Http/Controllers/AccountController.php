<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Laravel\Passport\Passport;

class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['identities', 'oauthTokenFamilies' => fn ($query) => $query->latest('updated_at')]);

        $families = $user->oauthTokenFamilies
            ->groupBy('client_id')
            ->map(function ($items, $clientId) {
                $client = Passport::client()->newQuery()->find($clientId);

                return [
                    'client_id' => $clientId,
                    'name' => $client?->name ?: 'Application OAuth',
                    'active' => $items->where('status', 'active')->count(),
                    'last_used_at' => $items->max('last_used_at'),
                    'last_used_human' => $items->max('last_used_at')
                        ? \Illuminate\Support\Carbon::parse($items->max('last_used_at'))->diffForHumans()
                        : null,
                ];
            })
            ->values();

        $passkeys = $user->passkeys()
            ->orderByDesc('last_used_at')
            ->orderByDesc('created_at')
            ->get();

        $securityEvents = $user->securityEvents()
            ->latest('created_at')
            ->limit(30)
            ->get();

        $trustedDevices = $user->trustedDevices()
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->latest('last_used_at')
            ->get();

        $sessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) {
                $session->last_activity_human = \Illuminate\Support\Carbon::createFromTimestamp((int) $session->last_activity)->diffForHumans();

                return $session;
            });

        $securityScore = 25
            + ($user->hasVerifiedEmail() ? 20 : 0)
            + ($user->two_factor_confirmed_at ? 25 : 0)
            + ($passkeys->isNotEmpty() ? 20 : 0)
            + ($user->password_changed_at ? 10 : 0);

        $securityLevel = match (true) {
            $securityScore >= 90 => 'Renforcée',
            $securityScore >= 70 => 'Bonne',
            default => 'À renforcer',
        };

        $linkedPromethee = $user->identities->firstWhere('provider', 'promethee');
        $activeAppNames = $families->filter(fn ($app) => $app['active'] > 0)
            ->pluck('name')
            ->map(fn ($name) => mb_strtolower((string) $name));

        $products = collect([
            [
                'name' => 'Air Inter VA',
                'code' => 'WEB',
                'description' => 'Communauté et espace membre',
                'url' => config('airinter-id.public_url'),
                'connected' => $activeAppNames->contains(fn ($name) => str_contains($name, 'air inter')),
            ],
            [
                'name' => 'Prométhée',
                'code' => 'OPS',
                'description' => 'Opérations et carrière pilote',
                'url' => config('airinter-id.promethee_url'),
                'connected' => (bool) $linkedPromethee || $activeAppNames->contains(fn ($name) => str_contains($name, 'prométhée') || str_contains($name, 'promethee')),
            ],
            [
                'name' => config('airinter-id.hermes_name', 'Hermès'),
                'code' => 'ACARS',
                'description' => 'Poste équipage et suivi de vol',
                'url' => null,
                'connected' => $activeAppNames->contains(fn ($name) => str_contains($name, 'hermès') || str_contains($name, 'hermes')),
            ],
        ]);

        return view('account', [
            'user' => $user,
            'applications' => $families,
            'products' => $products,
            'sessions' => $sessions,
            'passkeys' => $passkeys,
            'securityEvents' => $securityEvents,
            'trustedDevices' => $trustedDevices,
            'securityScore' => min(100, $securityScore),
            'securityLevel' => $securityLevel,
            'currentSessionId' => $request->session()->getId(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email:rfc', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'country' => ['nullable', 'string', 'regex:/^[A-Za-z]{2}$/'],
            'home_airport_id' => ['nullable', 'string', 'max:10', 'regex:/^[A-Za-z0-9-]+$/'],
            'vatsim_id' => ['nullable', 'string', 'max:32', 'regex:/^[0-9]+$/'],
            'ivao_id' => ['nullable', 'string', 'max:32', 'regex:/^[0-9]+$/'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $emailChanged = !hash_equals(mb_strtolower((string) $user->email), mb_strtolower((string) $data['email']));

        $user->display_name = trim((string) $data['display_name']);
        $user->email = mb_strtolower(trim((string) $data['email']));
        $user->country = filled($data['country'] ?? null) ? strtoupper(trim((string) $data['country'])) : null;
        $user->home_airport_id = filled($data['home_airport_id'] ?? null) ? strtoupper(trim((string) $data['home_airport_id'])) : null;
        $user->vatsim_id = filled($data['vatsim_id'] ?? null) ? trim((string) $data['vatsim_id']) : null;
        $user->ivao_id = filled($data['ivao_id'] ?? null) ? trim((string) $data['ivao_id']) : null;

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            if (filled($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $avatar = $request->file('avatar');
            $user->avatar_path = $avatar->storeAs(
                'avatars',
                $user->subject.'.'.$avatar->extension(),
                'public'
            );
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('account', ['#' => 'profile'])
            ->with('status', 'Profil Argos mis à jour. Les applications Air Inter utiliseront ces données à la prochaine synchronisation.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'preferred_locale' => ['required', Rule::in(['fr', 'en'])],
            'timezone' => ['required', 'timezone'],
        ]);

        $request->user()->forceFill($data)->save();

        return back()->with('status', 'Préférences Argos mises à jour.');
    }
}
