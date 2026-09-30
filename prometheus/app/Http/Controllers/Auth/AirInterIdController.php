<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Controller;
use App\Models\Enums\UserState;
use App\Models\User;
use App\Services\ArgosOidcTokenValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class AirInterIdController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $config = $this->config();

        $nonce = $this->randomBase64Url(32);
        $codeVerifier = $this->randomBase64Url(64);
        $codeChallenge = $this->base64UrlEncode(
            hash('sha256', $codeVerifier, true)
        );

        // Keep the OAuth handshake independent from the PHP session. Some
        // shared-hosting/cPanel setups can lose the Laravel session during the
        // round-trip to Argos. The state is authenticated + encrypted with
        // APP_KEY and expires quickly, so PKCE/nonce/intended remain protected
        // without depending on phpvms_session surviving the redirect.
        $state = Crypt::encryptString(json_encode([
            'nonce' => $nonce,
            'code_verifier' => $codeVerifier,
            'intended' => $this->safeTarget((string) $request->query(
                'return_to',
                config('phpvms.login_redirect', '/dashboard')
            )),
            'issued_at' => time(),
        ], JSON_UNESCAPED_SLASHES));

        $query = http_build_query([
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect'],
            'response_type' => 'code',
            'scope' => 'openid profile email promethee:read',
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
        ], '', '&', PHP_QUERY_RFC3986);

        return redirect()->away(
            rtrim($config['base_url'], '/').'/oauth/authorize?'.$query
        );
    }

    public function callback(
        Request $request,
        ArgosOidcTokenValidator $oidc
    ): RedirectResponse {
        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        try {
            $statePayload = json_decode(
                Crypt::decryptString((string) $request->input('state')),
                true,
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\Throwable $exception) {
            report($exception);
            abort(419, 'Contexte Argos invalide ou expiré.');
        }

        $expectedNonce = (string) ($statePayload['nonce'] ?? '');
        $codeVerifier = (string) ($statePayload['code_verifier'] ?? '');
        $intended = (string) ($statePayload['intended'] ?? config('phpvms.login_redirect', '/dashboard'));
        $issuedAt = (int) ($statePayload['issued_at'] ?? 0);

        if (
            $expectedNonce === ''
            || $codeVerifier === ''
            || $issuedAt <= 0
            || (time() - $issuedAt) > 600
        ) {
            abort(419, 'Contexte OIDC Argos incomplet ou expiré.');
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
                'code_verifier' => $codeVerifier,
            ]);

        if (!$tokenResponse->successful()) {
            $oauthError = trim((string) $tokenResponse->json('error'));
            $oauthDescription = trim((string) $tokenResponse->json('error_description'));

            report(new RuntimeException(
                'Argos token exchange failed: HTTP '.$tokenResponse->status().
                ($oauthError !== '' ? ' '.$oauthError : '').
                ($oauthDescription !== '' ? ' — '.$oauthDescription : '')
            ));

            $message = 'Argos a refusé l’échange du code OAuth';
            if ($oauthError !== '') {
                $message .= ' ('.$oauthError.')';
            }
            if ($oauthDescription !== '') {
                $message .= ' : '.$oauthDescription;
            }

            abort(502, $message);
        }

        if (!$tokenResponse->json('access_token')) {
            report(new RuntimeException(
                'Argos token exchange succeeded without access_token.'
            ));

            abort(502, 'Argos a répondu sans access_token.');
        }

        if (!$tokenResponse->json('id_token')) {
            report(new RuntimeException(
                'Argos token exchange succeeded without id_token.'
            ));

            abort(502, 'Argos a répondu sans id_token OIDC.');
        }

        try {
            $idTokenClaims = $oidc->validate(
                (string) $tokenResponse->json('id_token'),
                (string) $config['issuer'],
                (string) $config['client_id'],
                $expectedNonce,
            );
        } catch (RuntimeException $exception) {
            report($exception);
            abort(502, 'Argos a renvoyé une identité qui n’a pas pu être vérifiée.');
        }

        $profileResponse = Http::withToken(
            (string) $tokenResponse->json('access_token')
        )
            ->acceptJson()
            ->timeout(10)
            ->get(rtrim($config['base_url'], '/').'/api/v1/me');

        if (!$profileResponse->successful()) {
            report(new RuntimeException(
                'Argos profile request failed: HTTP '.$profileResponse->status()
            ));

            abort(502, 'Argos n’a pas pu charger votre profil.');
        }

        $profile = (array) $profileResponse->json();

        if (
            empty($profile['sub'])
            || !hash_equals((string) $idTokenClaims['sub'], (string) $profile['sub'])
        ) {
            abort(502, 'Le profil Argos ne correspond pas à l’identité authentifiée.');
        }

        $identity = collect($profile['identities'] ?? [])
            ->first(
                fn (array $identity) =>
                    ($identity['provider'] ?? null) === 'promethee'
            );

        if (!$identity || empty($identity['id'])) {
            abort(403, 'Votre compte Argos n’est pas encore lié à Prométhée.');
        }

        $user = User::query()->find($identity['id']);

        if (!$user) {
            abort(403, 'Le compte Prométhée lié à Argos est introuvable.');
        }

        if (
            $user->state !== UserState::ACTIVE
            && $user->state !== UserState::ON_LEAVE
        ) {
            abort(403, 'Ce compte pilote ne peut pas se connecter actuellement.');
        }

        // Use the web guard explicitly and keep a remember cookie as a
        // resilient fallback for shared-hosting setups where PHP session
        // persistence can be unreliable across redirects.
        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();
        $request->session()->save();

        if (!Auth::guard('web')->check() || (int) Auth::guard('web')->id() !== (int) $user->getKey()) {
            abort(500, 'Prométhée n’a pas pu persister la session utilisateur après Argos.');
        }

        $user->forceFill([
            'last_ip' => $request->ip(),
            'lastlogin_at' => now(),
        ])->save();

        return redirect()->to($this->safeTarget($intended));
    }

    private function config(): array
    {
        $config = (array) config('services.airinter_id', []);

        if (!($config['enabled'] ?? false)) {
            throw new RuntimeException('Argos SSO is disabled.');
        }

        foreach (
            ['base_url', 'issuer', 'client_id', 'client_secret', 'redirect']
            as $key
        ) {
            if (empty($config[$key])) {
                throw new RuntimeException(
                    'Argos is not configured: missing '.$key
                );
            }
        }

        return $config;
    }

    private function safeTarget(string $target): string
    {
        if (
            str_starts_with($target, '/')
            && !str_starts_with($target, '//')
        ) {
            return $target;
        }

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
        $targetHost = parse_url($target, PHP_URL_HOST);

        if (
            $targetHost
            && $appHost
            && hash_equals((string) $appHost, (string) $targetHost)
        ) {
            return $target;
        }

        return (string) config('phpvms.login_redirect', '/dashboard');
    }

    private function randomBase64Url(int $bytes): string
    {
        return $this->base64UrlEncode(random_bytes($bytes));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(
            strtr(base64_encode($value), '+/', '-_'),
            '='
        );
    }
}
