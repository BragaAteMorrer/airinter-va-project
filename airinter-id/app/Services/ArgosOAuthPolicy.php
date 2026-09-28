<?php

namespace App\Services;

use Illuminate\Support\Arr;

class ArgosOAuthPolicy
{
    public function clientDefinitions(): array
    {
        return (array) config('airinter-id.clients', []);
    }

    public function definitionForClientName(string $name): ?array
    {
        foreach ($this->clientDefinitions() as $key => $definition) {
            if ((string) ($definition['name'] ?? $key) === $name) {
                return array_merge(['key' => $key], $definition);
            }
        }

        return null;
    }

    public function allowedScopes(array $definition): array
    {
        return array_values(array_unique(array_map(
            static fn ($scope) => trim((string) $scope),
            (array) ($definition['scopes'] ?? [])
        )));
    }

    public function requestedScopes(string|array|null $scope): array
    {
        if (is_array($scope)) {
            $scopes = $scope;
        } else {
            $scopes = preg_split('/\s+/', trim((string) $scope)) ?: [];
        }

        return array_values(array_filter(array_unique(array_map(
            static fn ($value) => trim((string) $value),
            $scopes
        ))));
    }

    public function disallowedScopes(array $definition, string|array|null $requested): array
    {
        return array_values(array_diff(
            $this->requestedScopes($requested),
            $this->allowedScopes($definition)
        ));
    }

    public function redirectUriIsAllowed(array $definition, string $redirectUri): bool
    {
        $allowed = (array) ($definition['redirect_uris'] ?? []);

        if ($allowed === [] && filled($definition['redirect_uri'] ?? null)) {
            $allowed = [(string) $definition['redirect_uri']];
        }

        return in_array($redirectUri, array_map('strval', $allowed), true);
    }

    public function pkceIsRequired(array $definition): bool
    {
        return (bool) Arr::get($definition, 'security.require_pkce', true);
    }

    public function stateIsRequired(array $definition): bool
    {
        return (bool) Arr::get($definition, 'security.require_state', true);
    }

    public function allowedGrantTypes(array $definition): array
    {
        return array_values((array) ($definition['grant_types'] ?? [
            'authorization_code',
            'refresh_token',
        ]));
    }
}
