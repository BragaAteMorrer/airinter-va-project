<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ArgosOidcTokenValidator
{
    public function validate(
        string $jwt,
        string $issuer,
        string $clientId,
        string $expectedNonce
    ): array {
        [$encodedHeader, $encodedPayload, $encodedSignature] = $this->segments($jwt);

        $header = $this->decodeJsonSegment($encodedHeader, 'header');
        $claims = $this->decodeJsonSegment($encodedPayload, 'payload');

        if (($header['alg'] ?? null) !== 'RS256') {
            throw new RuntimeException('Argos ID Token uses an unexpected signing algorithm.');
        }

        $kid = (string) ($header['kid'] ?? '');
        if ($kid === '') {
            throw new RuntimeException('Argos ID Token has no signing key identifier.');
        }

        $jwk = $this->jwkForKid($issuer, $kid);
        $pem = $this->rsaJwkToPem($jwk);
        $signature = $this->base64UrlDecode($encodedSignature);

        $verified = openssl_verify(
            $encodedHeader.'.'.$encodedPayload,
            $signature,
            $pem,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            throw new RuntimeException('Argos ID Token signature is invalid.');
        }

        $this->validateClaims($claims, $issuer, $clientId, $expectedNonce);

        return $claims;
    }

    private function validateClaims(
        array $claims,
        string $issuer,
        string $clientId,
        string $expectedNonce
    ): void {
        if (!isset($claims['iss']) || !hash_equals(rtrim($issuer, '/'), rtrim((string) $claims['iss'], '/'))) {
            throw new RuntimeException('Argos ID Token issuer is invalid.');
        }

        $audience = $claims['aud'] ?? null;
        $audiences = is_array($audience) ? array_map('strval', $audience) : [(string) $audience];

        if (!in_array($clientId, $audiences, true)) {
            throw new RuntimeException('Argos ID Token audience is invalid.');
        }

        $now = time();
        $clockSkew = 60;

        if (!isset($claims['exp']) || (int) $claims['exp'] < ($now - $clockSkew)) {
            throw new RuntimeException('Argos ID Token has expired.');
        }

        if (isset($claims['nbf']) && (int) $claims['nbf'] > ($now + $clockSkew)) {
            throw new RuntimeException('Argos ID Token is not valid yet.');
        }

        if (isset($claims['iat']) && (int) $claims['iat'] > ($now + $clockSkew)) {
            throw new RuntimeException('Argos ID Token issued-at time is invalid.');
        }

        if (empty($claims['sub'])) {
            throw new RuntimeException('Argos ID Token has no subject.');
        }

        if (empty($claims['nonce']) || !hash_equals($expectedNonce, (string) $claims['nonce'])) {
            throw new RuntimeException('Argos ID Token nonce is invalid.');
        }
    }

    private function jwkForKid(string $issuer, string $kid): array
    {
        $jwks = Cache::remember(
            'argos_oidc_jwks_'.sha1($issuer),
            now()->addMinutes(5),
            function () use ($issuer): array {
                $response = Http::acceptJson()
                    ->timeout(10)
                    ->get(rtrim($issuer, '/').'/.well-known/jwks.json');

                if (!$response->successful()) {
                    throw new RuntimeException('Argos JWKS endpoint returned HTTP '.$response->status().'.');
                }

                return (array) $response->json();
            }
        );

        foreach ((array) ($jwks['keys'] ?? []) as $key) {
            if (($key['kid'] ?? null) === $kid && ($key['kty'] ?? null) === 'RSA') {
                return $key;
            }
        }

        Cache::forget('argos_oidc_jwks_'.sha1($issuer));

        $response = Http::acceptJson()
            ->timeout(10)
            ->get(rtrim($issuer, '/').'/.well-known/jwks.json');

        if (!$response->successful()) {
            throw new RuntimeException('Argos JWKS refresh returned HTTP '.$response->status().'.');
        }

        $jwks = (array) $response->json();
        Cache::put('argos_oidc_jwks_'.sha1($issuer), $jwks, now()->addMinutes(5));

        foreach ((array) ($jwks['keys'] ?? []) as $key) {
            if (($key['kid'] ?? null) === $kid && ($key['kty'] ?? null) === 'RSA') {
                return $key;
            }
        }

        throw new RuntimeException('Argos signing key is unknown.');
    }

    private function rsaJwkToPem(array $jwk): string
    {
        if (empty($jwk['n']) || empty($jwk['e'])) {
            throw new RuntimeException('Argos RSA JWK is incomplete.');
        }

        $modulus = $this->base64UrlDecode((string) $jwk['n']);
        $exponent = $this->base64UrlDecode((string) $jwk['e']);

        $rsaPublicKey = $this->derSequence(
            $this->derInteger($modulus).
            $this->derInteger($exponent)
        );

        $rsaAlgorithmIdentifier = hex2bin('300d06092a864886f70d0101010500');
        $subjectPublicKeyInfo = $this->derSequence(
            $rsaAlgorithmIdentifier.
            $this->derBitString($rsaPublicKey)
        );

        return "-----BEGIN PUBLIC KEY-----\n".
            chunk_split(base64_encode($subjectPublicKeyInfo), 64, "\n").
            "-----END PUBLIC KEY-----\n";
    }

    private function derInteger(string $value): string
    {
        $value = ltrim($value, "\x00");
        if ($value === '') {
            $value = "\x00";
        }

        if ((ord($value[0]) & 0x80) !== 0) {
            $value = "\x00".$value;
        }

        return "\x02".$this->derLength(strlen($value)).$value;
    }

    private function derSequence(string $value): string
    {
        return "\x30".$this->derLength(strlen($value)).$value;
    }

    private function derBitString(string $value): string
    {
        $value = "\x00".$value;

        return "\x03".$this->derLength(strlen($value)).$value;
    }

    private function derLength(int $length): string
    {
        if ($length < 128) {
            return chr($length);
        }

        $temp = '';
        while ($length > 0) {
            $temp = chr($length & 0xff).$temp;
            $length >>= 8;
        }

        return chr(0x80 | strlen($temp)).$temp;
    }

    private function segments(string $jwt): array
    {
        $segments = explode('.', $jwt);

        if (count($segments) !== 3) {
            throw new RuntimeException('Argos ID Token is malformed.');
        }

        return $segments;
    }

    private function decodeJsonSegment(string $segment, string $name): array
    {
        $decoded = json_decode($this->base64UrlDecode($segment), true);

        if (!is_array($decoded)) {
            throw new RuntimeException('Argos ID Token '.$name.' is malformed.');
        }

        return $decoded;
    }

    private function base64UrlDecode(string $value): string
    {
        $value = strtr($value, '-_', '+/');
        $padding = strlen($value) % 4;

        if ($padding !== 0) {
            $value .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($value, true);

        if ($decoded === false) {
            throw new RuntimeException('Argos base64url value is invalid.');
        }

        return $decoded;
    }
}
