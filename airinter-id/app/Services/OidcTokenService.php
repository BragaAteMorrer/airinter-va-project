<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

class OidcTokenService
{
    public function issuer(): string
    {
        return rtrim((string) config('app.url'), '/');
    }

    public function issueIdToken(User $user, string $clientId, string $nonce, array $scopes): string
    {
        $now = time();
        $claims = [
            'iss' => $this->issuer(),
            'sub' => $user->subject,
            'aud' => $clientId,
            'exp' => $now + 300,
            'iat' => $now,
            'auth_time' => optional($user->last_login_at)->timestamp ?? $now,
            'nonce' => $nonce,
        ];

        if (in_array('profile', $scopes, true)) {
            $claims['name'] = $user->display_name;
            $claims['locale'] = $user->preferred_locale;
            $claims['zoneinfo'] = $user->timezone;
        }

        if (in_array('email', $scopes, true)) {
            $claims['email'] = $user->email;
            $claims['email_verified'] = $user->email_verified_at !== null;
        }

        return $this->sign($claims);
    }

    public function jwks(): array
    {
        $pem = @file_get_contents(storage_path('oauth-public.key'));
        if (!$pem) {
            throw new RuntimeException('Passport public key is missing.');
        }

        $key = openssl_pkey_get_public($pem);
        $details = $key ? openssl_pkey_get_details($key) : false;
        if (!$details || !isset($details['rsa']['n'], $details['rsa']['e'])) {
            throw new RuntimeException('Passport public key is not a readable RSA key.');
        }

        return ['keys' => [[
            'kty' => 'RSA',
            'use' => 'sig',
            'kid' => $this->keyId($pem),
            'alg' => 'RS256',
            'n' => $this->base64Url($details['rsa']['n']),
            'e' => $this->base64Url($details['rsa']['e']),
        ]]];
    }

    private function sign(array $claims): string
    {
        $privatePem = @file_get_contents(storage_path('oauth-private.key'));
        $publicPem = @file_get_contents(storage_path('oauth-public.key'));

        if (!$privatePem || !$publicPem) {
            throw new RuntimeException('Passport signing keys are missing.');
        }

        $header = ['alg' => 'RS256', 'typ' => 'JWT', 'kid' => $this->keyId($publicPem)];
        $segments = [
            $this->base64Url(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64Url(json_encode($claims, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
        ];

        $input = implode('.', $segments);
        $signature = '';
        if (!openssl_sign($input, $signature, $privatePem, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign OIDC ID Token.');
        }

        return $input.'.'.$this->base64Url($signature);
    }

    private function keyId(string $publicPem): string
    {
        return substr(hash('sha256', $publicPem), 0, 24);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
