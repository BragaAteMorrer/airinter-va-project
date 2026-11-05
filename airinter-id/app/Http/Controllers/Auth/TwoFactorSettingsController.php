<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountSecurityService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

class TwoFactorSettingsController extends Controller
{
    public function create(Request $request, TotpService $totp): Response
    {
        $user = $request->user();

        if ($user->two_factor_confirmed_at) {
            return response()->view('auth.two-factor-settings', ['enabled' => true])
                ->header('Cache-Control', 'no-store, private')
                ->header('Referrer-Policy', 'no-referrer');
        }

        if (!$request->session()->has('mfa.setup_secret')) {
            $request->session()->put('mfa.setup_secret', $totp->generateSecret());
        }

        $secret = (string) $request->session()->get('mfa.setup_secret');

        return response()->view('auth.two-factor-settings', [
            'enabled' => false,
            'secret' => $secret,
            'provisioningUri' => $totp->provisioningUri('Argos · Air Inter', $user->email, $secret),
        ])->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function confirm(
        Request $request,
        TotpService $totp,
        AccountSecurityService $security,
    ): RedirectResponse {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $secret = (string) $request->session()->get('mfa.setup_secret');
        if ($secret === '' || !$totp->verify($secret, (string) $request->input('code'))) {
            throw ValidationException::withMessages(['code' => 'Code TOTP incorrect.']);
        }

        $recoveryCodes = $totp->recoveryCodes();
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => array_map(
                static fn (string $code) => Hash::make($code),
                $recoveryCodes
            ),
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget('mfa.setup_secret');
        $request->session()->put('mfa.recovery_codes_plain', $recoveryCodes);

        $security->revokeOtherSecurityContexts($user, $request->session()->getId());
        $security->record($request, $user, 'mfa.enabled');

        return redirect()->route('account.mfa.recovery-codes');
    }

    public function recoveryCodes(Request $request): Response|RedirectResponse
    {
        $codes = $request->session()->pull('mfa.recovery_codes_plain');

        if (!$codes) {
            return redirect()->route('account');
        }

        return response()->view('auth.recovery-codes', ['codes' => $codes])
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function regenerate(
        Request $request,
        TotpService $totp,
        AccountSecurityService $security,
    ): RedirectResponse {
        $user = $request->user();
        abort_unless($user->two_factor_confirmed_at, 403, 'Activez la double authentification avant de générer des codes de secours.');
        $codes = $totp->recoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => array_map(
                static fn (string $code) => Hash::make($code),
                $codes
            ),
        ])->save();

        $request->session()->put('mfa.recovery_codes_plain', $codes);
        $security->record($request, $user, 'mfa.recovery_codes.regenerated');

        return redirect()->route('account.mfa.recovery-codes');
    }

    public function destroy(Request $request, AccountSecurityService $security): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $security->revokeOtherSecurityContexts($user, $request->session()->getId());
        $security->record($request, $user, 'mfa.disabled');

        return redirect()->route('account')->with('status', 'Authentification à deux facteurs désactivée.');
    }
}
