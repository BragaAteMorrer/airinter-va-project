<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LegacyIdentity;
use App\Models\SecurityEvent;
use App\Models\User;
use App\Services\AdaptiveRiskService;
use App\Services\TrustedDeviceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request, AdaptiveRiskService $risk, TrustedDeviceService $trustedDevices): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $login = trim($credentials['login']);
        $user = str_contains($login, '@')
            ? User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($login)])->first()
            : LegacyIdentity::query()
                ->where('provider', 'promethee')
                ->whereRaw('UPPER(external_ident) = ?', [mb_strtoupper($login)])
                ->with('user')
                ->first()?->user;

        if (!$user || !$user->password || !Hash::check($credentials['password'], $user->password)) {
            if ($user) {
                SecurityEvent::create([
                    'user_id' => $user->id,
                    'type' => 'login.failed',
                    'risk_score' => 20,
                    'severity' => 'medium',
                    'ip_address' => $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                    'created_at' => now(),
                ]);
            }

            throw ValidationException::withMessages([
                'login' => 'Identifiant Air Inter ou mot de passe incorrect.',
            ]);
        }

        if (!$user->canUseSso()) {
            throw ValidationException::withMessages([
                'login' => 'Ce compte ne peut pas actuellement utiliser Argos.',
            ]);
        }

        if (Hash::needsRehash($user->password)) {
            $user->forceFill(['password' => Hash::make($credentials['password'])])->save();
        }

        $trustedDevice = $trustedDevices->resolve($request, $user);
        $assessment = $risk->assess($request, $user, $trustedDevice);

        $request->session()->put('auth.risk_score', $assessment['score']);
        $request->session()->put('auth.risk_level', $assessment['level']);
        $request->session()->put('auth.risk_reasons', $assessment['reasons']);

        if ($user->two_factor_confirmed_at && $user->two_factor_secret) {
            $canBypassMfa = $trustedDevice && $assessment['allow_trusted_device_bypass'];

            if (!$canBypassMfa) {
                $request->session()->put('auth.two_factor_user_id', $user->id);
                $request->session()->put('auth.two_factor_remember', $request->boolean('remember'));
                $request->session()->put('auth.trusted_device_candidate', $trustedDevice?->id);

                return redirect()->route('two-factor.login');
            }
        }

        $knownContext = SecurityEvent::query()
            ->where('user_id', $user->id)
            ->where('type', 'login.succeeded')
            ->where('ip_address', $request->ip())
            ->where('user_agent', mb_substr((string) $request->userAgent(), 0, 1000))
            ->exists();

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        $user->forceFill(['last_login_at' => now()])->save();
        if (!$knownContext) {
            SecurityEvent::create([
                'user_id' => $user->id,
                'type' => 'login.new_context',
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'created_at' => now(),
            ]);
        }

        SecurityEvent::create([
            'user_id' => $user->id,
            'type' => 'login.succeeded',
            'risk_score' => $assessment['score'],
            'severity' => $risk->severityForScore($assessment['score']),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => [
                'login' => str_contains($login, '@') ? 'email' : 'pilot_ident',
                'risk_level' => $assessment['level'],
                'risk_reasons' => $assessment['reasons'],
                'trusted_device' => (bool) $trustedDevice,
            ],
            'created_at' => now(),
        ]);

        return redirect()->intended(route('account'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        if ($request->user()) {
            SecurityEvent::create([
                'user_id' => $request->user()->id,
                'type' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'created_at' => now(),
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
