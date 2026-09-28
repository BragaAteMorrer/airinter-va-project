<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountSecurityService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('auth.two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(
        Request $request,
        TotpService $totp,
        AccountSecurityService $security,
    ): RedirectResponse {
        $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $user = User::query()->find($request->session()->get('auth.two_factor_user_id'));
        if (!$user || !$user->canUseSso() || !$user->two_factor_confirmed_at) {
            $request->session()->forget(['auth.two_factor_user_id', 'auth.two_factor_remember']);

            return redirect()->route('login');
        }

        $code = trim((string) $request->input('code'));
        $valid = false;

        if ($user->two_factor_secret) {
            $valid = $totp->verify($user->two_factor_secret, $code);
        }

        $usedRecovery = false;
        if (!$valid) {
            $usedRecovery = $security->consumeRecoveryCode($user, $code);
            $valid = $usedRecovery;
        }

        if (!$valid) {
            throw ValidationException::withMessages([
                'code' => 'Code d’authentification incorrect.',
            ]);
        }

        $remember = (bool) $request->session()->pull('auth.two_factor_remember', false);
        $request->session()->forget('auth.two_factor_user_id');

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->put('auth.password_confirmed_at', time());

        $user->forceFill(['last_login_at' => now()])->save();
        $security->record($request, $user, $usedRecovery ? 'mfa.recovery_code.used' : 'mfa.challenge.succeeded');

        return redirect()->intended(route('account'));
    }
}
