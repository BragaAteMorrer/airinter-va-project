<?php

namespace App\Http\Controllers\Oidc;

use App\Http\Controllers\Controller;
use App\Services\OidcTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;

class ProviderController extends Controller
{
    public function discovery(OidcTokenService $tokens): JsonResponse
    {
        $issuer = $tokens->issuer();
        $hermesClient = $this->hermesClientPayload($tokens);

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'userinfo_endpoint' => $issuer.'/oauth/userinfo',
            'jwks_uri' => $issuer.'/.well-known/jwks.json',
            'scopes_supported' => ['openid', 'profile', 'email', 'promethee:read', 'hermes:operate'],
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post', 'none'],
            'code_challenge_methods_supported' => ['S256'],
            'claims_supported' => ['sub', 'name', 'email', 'email_verified', 'locale', 'zoneinfo'],
            'hermes_client' => $hermesClient,
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function oauthMetadata(OidcTokenService $tokens): JsonResponse
    {
        $issuer = $tokens->issuer();

        return response()->json([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer.'/oauth/authorize',
            'token_endpoint' => $issuer.'/oauth/token',
            'jwks_uri' => $issuer.'/.well-known/jwks.json',
            'scopes_supported' => ['openid', 'profile', 'email', 'promethee:read', 'hermes:operate'],
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['client_secret_basic', 'client_secret_post', 'none'],
            'code_challenge_methods_supported' => ['S256'],
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function jwks(OidcTokenService $tokens): JsonResponse
    {
        return response()->json($tokens->jwks())
            ->header('Cache-Control', 'public, max-age=300');
    }

    /**
     * Public desktop-client metadata. OAuth client IDs are identifiers, not
     * secrets, so Hermès discovers the currently active client at runtime.
     * This keeps client rotation independent from desktop releases.
     */
    public function hermesClient(OidcTokenService $tokens): JsonResponse
    {
        $payload = $this->hermesClientPayload($tokens);

        if ($payload === null) {
            return response()->json([
                'error' => 'temporarily_unavailable',
                'error_description' => 'Hermès OAuth client is not provisioned.',
            ], 503, ['Cache-Control' => 'no-store']);
        }

        return response()->json($payload)
            ->header('Cache-Control', 'public, max-age=300');
    }

    private function hermesClientPayload(OidcTokenService $tokens): ?array
    {
        $definition = (array) config('airinter-id.clients.hermes', []);
        $name = (string) ($definition['name'] ?? 'Hermès');

        $client = Passport::client()
            ->newQuery()
            ->where('name', $name)
            ->where('revoked', false)
            ->latest('created_at')
            ->first();

        if (!$client) {
            return null;
        }

        return [
            'issuer' => $tokens->issuer(),
            'client_id' => (string) $client->getKey(),
            'redirect_uri' => (string) ($definition['redirect_uri'] ?? 'http://127.0.0.1:47821/callback'),
            'scope' => implode(' ', (array) ($definition['scopes'] ?? ['openid', 'profile', 'email', 'hermes:operate'])),
            'token_endpoint_auth_method' => 'none',
            'code_challenge_method' => 'S256',
        ];
    }

    public function userinfo(Request $request): JsonResponse
    {
        $user = $request->user();

        $claims = ['sub' => $user->subject];

        if ($user->tokenCan('profile')) {
            $claims += [
                'name' => $user->display_name,
                'locale' => $user->preferred_locale,
                'zoneinfo' => $user->timezone,
            ];
        }

        if ($user->tokenCan('email')) {
            $claims += [
                'email' => $user->email,
                'email_verified' => $user->email_verified_at !== null,
            ];
        }

        return response()->json($claims);
    }
}
