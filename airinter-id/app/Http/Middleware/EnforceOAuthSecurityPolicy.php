<?php

namespace App\Http\Middleware;

use App\Models\OidcAuthorizationRequest;
use App\Models\User;
use App\Services\ArgosOAuthPolicy;
use App\Services\OidcTokenService;
use App\Services\TokenSecurityService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

class EnforceOAuthSecurityPolicy
{
    public function __construct(
        private readonly ArgosOAuthPolicy $policy,
        private readonly OidcTokenService $oidcTokens,
        private readonly TokenSecurityService $tokenSecurity,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('oauth/authorize') && $request->isMethod('GET')) {
            $failure = $this->validateAuthorizationRequest($request);

            if ($failure) {
                return $failure;
            }

            $this->rememberOidcRequest($request);
        }

        if ($request->is('oauth/token') && $request->isMethod('POST')) {
            $failure = $this->validateTokenRequest($request);

            if ($failure) {
                return $failure;
            }

            $refreshLineage = null;
            if ((string) $request->input('grant_type') === 'refresh_token') {
                $presented = (string) $request->input('refresh_token');
                if ($presented !== '') {
                    $refreshLineage = $this->tokenSecurity->inspectRefreshToken($presented);

                    if ($refreshLineage && $refreshLineage->status !== 'active') {
                        $this->tokenSecurity->handleReuse($refreshLineage);

                        return $this->oauthError(
                            'invalid_grant',
                            'Refresh token reuse detected. The complete token family has been revoked.'
                        );
                    }
                }
            }

            $response = $next($request);
            $response = $this->trackTokenSecurity($request, $response, $refreshLineage);

            return $this->appendIdTokenWhenRequired($request, $response);
        }

        return $next($request);
    }

    private function validateAuthorizationRequest(Request $request): ?Response
    {
        if ((string) $request->query('response_type') !== 'code') {
            return $this->oauthError('unsupported_response_type', 'Argos only supports Authorization Code for first-party clients.');
        }

        $client = $this->passportClient((string) $request->query('client_id'));
        if (!$client) {
            return $this->oauthError('invalid_client', 'Unknown or revoked OAuth client.');
        }

        $definition = $this->policy->definitionForClientName((string) $client->name);
        if (!$definition) {
            return $this->oauthError('unauthorized_client', 'This client is not registered in the Argos first-party policy.');
        }

        $redirectUri = (string) $request->query('redirect_uri');
        if ($redirectUri === '' || !$this->policy->redirectUriIsAllowed($definition, $redirectUri)) {
            return $this->oauthError('invalid_request', 'The redirect_uri does not exactly match an Argos registered callback.');
        }

        if ($this->policy->stateIsRequired($definition) && trim((string) $request->query('state')) === '') {
            return $this->oauthError('invalid_request', 'The state parameter is required.');
        }

        if ($this->policy->pkceIsRequired($definition)) {
            if (trim((string) $request->query('code_challenge')) === '') {
                return $this->oauthError('invalid_request', 'PKCE is required.');
            }

            if ((string) $request->query('code_challenge_method') !== 'S256') {
                return $this->oauthError('invalid_request', 'Argos requires PKCE with code_challenge_method=S256.');
            }
        }

        $requestedScopes = $this->policy->requestedScopes($request->query('scope'));
        $disallowed = $this->policy->disallowedScopes($definition, $requestedScopes);

        if ($disallowed !== []) {
            return $this->oauthError(
                'invalid_scope',
                'This client is not allowed to request: '.implode(', ', $disallowed)
            );
        }

        if (in_array('openid', $requestedScopes, true) && trim((string) $request->query('nonce')) === '') {
            return $this->oauthError('invalid_request', 'The nonce parameter is required when requesting the openid scope.');
        }

        return null;
    }

    private function validateTokenRequest(Request $request): ?Response
    {
        $grantType = (string) $request->input('grant_type');

        if (!in_array($grantType, ['authorization_code', 'refresh_token'], true)) {
            return $this->oauthError('unsupported_grant_type', 'Argos only accepts authorization_code and refresh_token grants.');
        }

        $client = $this->passportClient((string) $request->input('client_id'));
        if (!$client) {
            $client = $this->passportClient((string) $request->getUser());
        }

        if (!$client) {
            return null;
        }

        $definition = $this->policy->definitionForClientName((string) $client->name);
        if (!$definition) {
            return $this->oauthError('unauthorized_client', 'This client is not registered in the Argos first-party policy.');
        }

        if (!in_array($grantType, $this->policy->allowedGrantTypes($definition), true)) {
            return $this->oauthError('unauthorized_client', 'This grant type is not permitted for this client.');
        }

        if ($grantType === 'authorization_code' && $this->policy->pkceIsRequired($definition)) {
            if (trim((string) $request->input('code_verifier')) === '') {
                return $this->oauthError('invalid_request', 'The PKCE code_verifier is required.');
            }

            $redirectUri = (string) $request->input('redirect_uri');
            if ($redirectUri === '' || !$this->policy->redirectUriIsAllowed($definition, $redirectUri)) {
                return $this->oauthError('invalid_request', 'The redirect_uri does not exactly match an Argos registered callback.');
            }
        }

        return null;
    }

    private function rememberOidcRequest(Request $request): void
    {
        $scopes = $this->policy->requestedScopes($request->query('scope'));

        if (!in_array('openid', $scopes, true)) {
            return;
        }

        OidcAuthorizationRequest::query()->where('expires_at', '<=', now())->delete();

        OidcAuthorizationRequest::query()->updateOrCreate(
            [
                'client_id' => (string) $request->query('client_id'),
                'code_challenge' => (string) $request->query('code_challenge'),
            ],
            [
                'nonce' => (string) $request->query('nonce'),
                'scope' => implode(' ', $scopes),
                'expires_at' => now()->addMinutes(10),
            ],
        );
    }


    private function trackTokenSecurity(Request $request, Response $response, mixed $refreshLineage): Response
    {
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        $payload = json_decode($response->getContent() ?: '{}', true);
        if (!is_array($payload) || empty($payload['access_token']) || empty($payload['refresh_token'])) {
            return $response;
        }

        $claims = $this->decodeJwtPayload((string) $payload['access_token']);
        $userId = $claims['sub'] ?? null;
        $accessTokenId = isset($claims['jti']) ? (string) $claims['jti'] : null;
        $user = $userId !== null ? User::query()->find($userId) : null;
        $clientId = (string) ($request->input('client_id') ?: $request->getUser());

        if (!$user || $clientId === '') {
            return $response;
        }

        if ((string) $request->input('grant_type') === 'authorization_code') {
            $this->tokenSecurity->beginFamily(
                $user,
                $clientId,
                (string) $payload['refresh_token'],
                $accessTokenId,
            );
        } elseif ((string) $request->input('grant_type') === 'refresh_token') {
            if ($refreshLineage) {
                $this->tokenSecurity->rotate(
                    $refreshLineage,
                    (string) $payload['refresh_token'],
                    $accessTokenId,
                );
            } else {
                $this->tokenSecurity->beginFamily(
                    $user,
                    $clientId,
                    (string) $payload['refresh_token'],
                    $accessTokenId,
                );
            }
        }

        return $response;
    }

    private function appendIdTokenWhenRequired(Request $request, Response $response): Response
    {
        if ((string) $request->input('grant_type') !== 'authorization_code' || $response->getStatusCode() >= 400) {
            return $response;
        }

        $clientId = (string) ($request->input('client_id') ?: $request->getUser());
        $verifier = (string) $request->input('code_verifier');

        if ($clientId === '' || $verifier === '') {
            return $response;
        }

        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        $context = OidcAuthorizationRequest::query()
            ->where('client_id', $clientId)
            ->where('code_challenge', $challenge)
            ->where('expires_at', '>', now())
            ->first();

        if (!$context) {
            return $response;
        }

        $payload = json_decode($response->getContent() ?: '{}', true);
        if (!is_array($payload) || empty($payload['access_token'])) {
            return $response;
        }

        $accessClaims = $this->decodeJwtPayload((string) $payload['access_token']);
        $userId = $accessClaims['sub'] ?? null;
        $user = $userId !== null ? User::query()->find($userId) : null;

        if (!$user) {
            return $response;
        }

        $scopes = $this->policy->requestedScopes($context->scope);

        $payload['id_token'] = $this->oidcTokens->issueIdToken(
            $user,
            $clientId,
            $context->nonce,
            $scopes,
        );

        $context->delete();

        $response->setContent(json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $response;
    }

    private function decodeJwtPayload(string $jwt): array
    {
        $segments = explode('.', $jwt);

        if (count($segments) !== 3) {
            return [];
        }

        $encoded = strtr($segments[1], '-_', '+/');
        $padding = strlen($encoded) % 4;
        if ($padding !== 0) {
            $encoded .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($encoded, true);
        if ($decoded === false) {
            return [];
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) ? $payload : [];
    }

    private function passportClient(string $clientId): mixed
    {
        if ($clientId === '') {
            return null;
        }

        return Passport::client()
            ->newQuery()
            ->whereKey($clientId)
            ->where('revoked', false)
            ->first();
    }

    private function oauthError(string $error, string $description, int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => $error,
            'error_description' => $description,
        ], $status, [
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        ]);
    }
}
