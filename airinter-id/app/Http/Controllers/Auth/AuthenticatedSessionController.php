<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LegacyIdentity;
use App\Models\SecurityEvent;
use App\Models\User;
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

    public function store(Request $request): RedirectResponse
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
            throw ValidationException::withMessages([
                'login' => 'Identifiant Air Inter ou mot de passe incorrect.',
            ]);
        }

        if (!$user->canUseSso()) {
            throw ValidationException::withMessages([
                'login' => 'Ce compte ne peut pas actuellement utiliser Air Inter ID.',
            ]);
        }

        if (Hash::needsRehash($user->password)) {
            $user->forceFill(['password' => Hash::make($credentials['password'])])->save();
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        SecurityEvent::create([
            'user_id' => $user->id,
            'type' => 'login.succeeded',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => ['login' => str_contains($login, '@') ? 'email' : 'pilot_ident'],
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
