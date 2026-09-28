<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Controller;
use App\Models\Enums\UserState;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class AirInterIdController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $config = $this->config();
        $state = Str::random(64);

        $request->session()->put('airinter_id.oauth_state', $state);
        $request->session()->put(
            'airinter_id.intended',
            $request->query('return_to', config('phpvms.login_redirect', '/dashboard'))
        );

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect'],
            'response_type' => 'code',
            'scope' => 'profile email promethee:read',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away(rtrim($config['base_url'], '/').'/oauth/authorize?'.$query);
    }

    public function callback(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $expectedState = (string) $request->session()->pull('airinter_id.oauth_state', '');
        if ($expectedState === '' || !hash_equals($expectedState, (string) $request->input('state'))) {
            abort(419, 'Session Air Inter ID invalide ou expirée.');
        }

        $config = $this->config();

        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->timeout(10)
            ->post(rtrim($config['base_url'], '/').'/oauth/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'redirect_uri' => $config['redirect'],
                'code' => $request->input('code'),
            ]);

        if (!$tokenResponse->successful() || !$tokenResponse->json('access_token')) {
            report(new RuntimeException('Air Inter ID token exchange failed: HTTP '.$tokenResponse->status()));
            abort(502, 'Air Inter ID n’a pas pu finaliser la connexion.');
        }

        $profileResponse = Http::withToken((string) $tokenResponse->json('access_token'))
            ->acceptJson()
            ->timeout(10)
            ->get(rtrim($config['base_url'], '/').'/api/v1/me');

        if (!$profileResponse->successful()) {
            report(new RuntimeException('Air Inter ID profile request failed: HTTP '.$profileResponse->status()));
            abort(502, 'Air Inter ID n’a pas pu charger votre profil.');
        }

        $profile = $profileResponse->json();
        $identity = collect($profile['identities'] ?? [])
            ->first(fn (array $identity) => ($identity['provider'] ?? null) === 'promethee');

        if (!$identity || empty($identity['id'])) {
            abort(403, 'Votre compte Air Inter ID n’est pas encore lié à Prométhée.');
        }

        $user = User::query()->find($identity['id']);
        if (!$user) {
            abort(403, 'Le compte Prométhée lié à Air Inter ID est introuvable.');
        }

        if ($user->state !== UserState::ACTIVE && $user->state !== UserState::ON_LEAVE) {
            abort(403, 'Ce compte pilote ne peut pas se connecter actuellement.');
        }

        Auth::login($user, false);
        $request->session()->regenerate();

        $user->forceFill([
            'last_ip' => $request->ip(),
            'lastlogin_at' => now(),
        ])->save();

        $target = (string) $request->session()->pull(
            'airinter_id.intended',
            config('phpvms.login_redirect', '/dashboard')
        );

        return redirect()->to($this->safeTarget($target));
    }

    private function config(): array
    {
        $config = (array) config('services.airinter_id', []);

        if (!($config['enabled'] ?? false)) {
            throw new RuntimeException('Air Inter ID SSO is disabled.');
        }

        foreach (['base_url', 'client_id', 'client_secret', 'redirect'] as $key) {
            if (empty($config[$key])) {
                throw new RuntimeException('Air Inter ID is not configured: missing '.$key);
            }
        }

        return $config;
    }

    private function safeTarget(string $target): string
    {
        if (str_starts_with($target, '/') && !str_starts_with($target, '//')) {
            return $target;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $targetHost = parse_url($target, PHP_URL_HOST);

        if ($targetHost && $appHost && hash_equals((string) $appHost, (string) $targetHost)) {
            return $target;
        }

        return (string) config('phpvms.login_redirect', '/dashboard');
    }
}
