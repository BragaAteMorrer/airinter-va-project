<?php

namespace App\Http\Middleware;

use App\Services\ArgosOAuthPolicy;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response;

class EnforceOAuthSecurityPolicy
{
    public function __construct(private readonly ArgosOAuthPolicy $policy)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('oauth/authorize') && $request->isMethod('GET')) {
            $failure = $this->validateAuthorizationRequest($request);

            if ($failure) {
                return $failure;
            }
        }

        if ($request->is('oauth/token') && $request->isMethod('POST')) {
            $failure = $this->validateTokenRequest($request);

            if ($failure) {
                return $failure;
            }
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

        $disallowed = $this->policy->disallowedScopes(
            $definition,
            $request->query('scope')
        );

        if ($disallowed !== []) {
            return $this->oauthError(
                'invalid_scope',
                'This client is not allowed to request: '.implode(', ', $disallowed)
            );
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
            // Confidential clients may authenticate through HTTP Basic.
            $clientId = (string) $request->getUser();
            $client = $this->passportClient($clientId);
        }

        if (!$client) {
            return null; // Let Passport return the canonical invalid_client response.
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
