<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LegacyIdentity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email:rfc', 'max:191'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'timezone' => ['required', 'timezone'],
            'vatsim_id' => ['nullable', 'string', 'max:32', 'regex:/^[0-9]+$/'],
            'ivao_id' => ['nullable', 'string', 'max:32', 'regex:/^[0-9]+$/'],
            'toc_accepted' => ['accepted'],
            'opt_in' => ['nullable', 'boolean'],
        ]);

        $email = mb_strtolower(trim((string) $data['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && $user->state !== 'pending_provisioning') {
            throw ValidationException::withMessages([
                'email' => 'Un compte Argos existe déjà avec cette adresse e-mail.',
            ]);
        }

        if ($user) {
            if (!$user->password || !Hash::check((string) $data['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => 'Une inscription incomplète existe déjà pour cette adresse. Utilisez le même mot de passe ou la récupération de compte.',
                ]);
            }
        } else {
            $user = User::create([
                'subject' => (string) Str::uuid(),
                'display_name' => trim((string) $data['display_name']),
                'email' => $email,
                'password' => Hash::make((string) $data['password']),
                'preferred_locale' => 'fr',
                'timezone' => (string) $data['timezone'],
                'vatsim_id' => filled($data['vatsim_id'] ?? null) ? (string) $data['vatsim_id'] : null,
                'ivao_id' => filled($data['ivao_id'] ?? null) ? (string) $data['ivao_id'] : null,
                'state' => 'pending_provisioning',
            ]);
        }

        $url = (string) config('airinter-id.promethee_provisioning_url');
        $token = (string) config('airinter-id.promethee_provisioning_token');

        if ($url === '' || $token === '') {
            throw ValidationException::withMessages([
                'email' => 'L’inscription Air Inter est temporairement indisponible.',
            ]);
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['X-Argos-Provisioning-Token' => $token])
                ->timeout(12)
                ->post($url, [
                    'subject' => $user->subject,
                    'name' => $user->display_name,
                    'email' => $user->email,
                    'timezone' => $user->timezone,
                    'vatsim_id' => $user->vatsim_id,
                    'ivao_id' => $user->ivao_id,
                    'opt_in' => (bool) ($data['opt_in'] ?? false),
                ]);
        } catch (\Throwable $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'email' => 'Prométhée n’est pas joignable pour finaliser votre candidature. Votre brouillon Argos est conservé : réessayez avec les mêmes informations.',
            ]);
        }

        if (!$response->successful()) {
            $message = trim((string) $response->json('message'));
            throw ValidationException::withMessages([
                'email' => $message !== '' ? $message : 'Prométhée a refusé la création du dossier pilote.',
            ]);
        }

        $pilotId = (string) $response->json('id');
        $ident = (string) $response->json('ident');

        if ($pilotId === '' || $ident === '') {
            throw ValidationException::withMessages([
                'email' => 'Prométhée a créé un dossier incomplet. Contactez le staff avant de réessayer.',
            ]);
        }

        LegacyIdentity::updateOrCreate(
            [
                'provider' => 'promethee',
                'external_user_id' => $pilotId,
            ],
            [
                'user_id' => $user->id,
                'external_email' => $user->email,
                'external_ident' => $ident,
                'metadata' => [
                    'pilot_id' => (int) $response->json('pilot_id'),
                    'airline_id' => (int) $response->json('airline_id'),
                    'legacy_state' => (int) $response->json('state'),
                    'registration_origin' => 'argos',
                ],
                'linked_at' => now(),
                'last_synced_at' => now(),
            ]
        );

        $user->forceFill(['state' => 'active'])->save();

        if (!$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()->route('account')
            ->with('status', 'Votre identité Argos est créée. Votre candidature pilote Prométhée est maintenant en attente de validation par le staff.');
    }
}
