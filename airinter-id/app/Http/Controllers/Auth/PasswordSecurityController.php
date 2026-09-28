<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AccountSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordSecurityController extends Controller
{
    public function update(Request $request, AccountSecurityService $security): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()],
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ])->save();

        $security->revokeOtherSecurityContexts($user, $request->session()->getId());
        $security->record($request, $user, 'password.changed');

        $request->session()->put('auth.password_confirmed_at', time());

        return back()->with('status', 'Mot de passe mis à jour. Les autres sessions et accès OAuth ont été révoqués.');
    }
}
