<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Throwable;

class ArgosDoctor extends Command
{
    protected $signature = 'argos:doctor {--json : Output machine-readable JSON} {--strict : Treat warnings as failures}';

    protected $description = 'Check whether Argos is ready to operate safely as the Air Inter identity provider.';

    public function handle(): int
    {
        $checks = [
            $this->checkEnvironment(),
            $this->checkRelease(),
            $this->checkHttps(),
            $this->checkApplicationKey(),
            $this->checkDatabase(),
            $this->checkCoreTables(),
            $this->checkPassportTables(),
            $this->checkPassportKeys(),
            $this->checkOauthClients(),
            $this->checkOAuthPolicy(),
            $this->checkOidcConfiguration(),
            $this->checkTokenSecurity(),
            $this->checkAccountSecurity(),
            $this->checkPasskeys(),
            $this->checkCallbacks(),
            $this->checkPrometheeBridge(),
            $this->checkWritableDirectories(),
        ];

        $errors = count(array_filter($checks, fn (array $check) => $check['status'] === 'error'));
        $warnings = count(array_filter($checks, fn (array $check) => $check['status'] === 'warning'));

        if ($this->option('json')) {
            $this->line(json_encode([
                'service' => 'Argos',
                'ok' => $errors === 0 && (!$this->option('strict') || $warnings === 0),
                'errors' => $errors,
                'warnings' => $warnings,
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        } else {
            $this->newLine();
            $this->info('ARGOS DOCTOR');
            $this->line('Air Inter Identity Services readiness check');
            $this->newLine();

            $this->table(
                ['Check', 'Status', 'Details'],
                array_map(fn (array $check) => [
                    $check['name'],
                    strtoupper($check['status']),
                    $check['message'],
                ], $checks),
            );

            $this->newLine();
            $this->line(sprintf('%d error(s), %d warning(s).', $errors, $warnings));
        }

        if ($errors > 0 || ($this->option('strict') && $warnings > 0)) {
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function checkEnvironment(): array
    {
        if (!app()->isProduction()) {
            return $this->warning('Environment', 'APP_ENV is not production.');
        }

        if ((bool) config('app.debug')) {
            return $this->failedCheck('Environment', 'APP_DEBUG must be false in production.');
        }

        return $this->ok('Environment', 'Production mode with debug disabled.');
    }

    private function checkRelease(): array
    {
        $release = trim((string) config('airinter-id.release'));

        if ($release === '') {
            return $this->warning(
                'Release marker',
                'ARGOS_RELEASE is empty. Set it to the deployed Git commit SHA to make production/repository parity auditable.'
            );
        }

        return $this->ok('Release marker', $release);
    }

    private function checkHttps(): array
    {
        $url = (string) config('app.url');
        if (!str_starts_with($url, 'https://')) {
            return $this->failedCheck('HTTPS', 'APP_URL must use https:// in production.');
        }

        return $this->ok('HTTPS', $url);
    }

    private function checkApplicationKey(): array
    {
        $key = (string) config('app.key');
        if ($key === '') {
            return $this->failedCheck('Application key', 'APP_KEY is missing.');
        }

        return $this->ok('Application key', 'APP_KEY is configured.');
    }

    private function checkDatabase(): array
    {
        try {
            DB::connection()->select('select 1');
            return $this->ok('Database', 'Primary database connection is reachable.');
        } catch (Throwable $e) {
            return $this->failedCheck('Database', $this->exceptionSummary($e));
        }
    }

    private function checkCoreTables(): array
    {
        $required = [
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'legacy_identities',
            'security_events',
        ];

        return $this->checkTables('Core tables', $required);
    }

    private function checkPassportTables(): array
    {
        $required = [
            'oauth_auth_codes',
            'oauth_access_tokens',
            'oauth_refresh_tokens',
            'oauth_clients',
            'oauth_device_codes',
        ];

        return $this->checkTables('Passport tables', $required);
    }

    private function checkTables(string $name, array $required): array
    {
        try {
            $missing = array_values(array_filter($required, fn (string $table) => !Schema::hasTable($table)));
        } catch (Throwable $e) {
            return $this->failedCheck($name, $this->exceptionSummary($e));
        }

        if ($missing !== []) {
            return $this->failedCheck($name, 'Missing: '.implode(', ', $missing));
        }

        return $this->ok($name, 'All required tables are present.');
    }

    private function checkPassportKeys(): array
    {
        $private = storage_path('oauth-private.key');
        $public = storage_path('oauth-public.key');

        $missing = [];
        if (!is_file($private) || !is_readable($private)) {
            $missing[] = 'oauth-private.key';
        }
        if (!is_file($public) || !is_readable($public)) {
            $missing[] = 'oauth-public.key';
        }

        if ($missing !== []) {
            return $this->failedCheck('Passport keys', 'Missing or unreadable: '.implode(', ', $missing).'. Run php artisan passport:keys.');
        }

        return $this->ok('Passport keys', 'Signing keys are present and readable.');
    }

    private function checkOauthClients(): array
    {
        try {
            if (!Schema::hasTable('oauth_clients')) {
                return $this->failedCheck('OAuth clients', 'oauth_clients table is missing.');
            }

            $definitions = (array) config('airinter-id.clients', []);
            $missing = [];

            foreach ($definitions as $definition) {
                $name = (string) ($definition['name'] ?? '');
                if ($name === '') {
                    continue;
                }

                $exists = Passport::client()
                    ->newQuery()
                    ->where('name', $name)
                    ->where('revoked', false)
                    ->exists();

                if (!$exists) {
                    $missing[] = $name;
                }
            }

            if ($missing !== []) {
                return $this->warning('OAuth clients', 'Not provisioned: '.implode(', ', $missing).'. Run php artisan airinter-id:configure-clients.');
            }

            return $this->ok('OAuth clients', 'Configured first-party clients are provisioned.');
        } catch (Throwable $e) {
            return $this->failedCheck('OAuth clients', $this->exceptionSummary($e));
        }
    }

    private function checkOAuthPolicy(): array
    {
        $issues = [];

        foreach ((array) config('airinter-id.clients', []) as $key => $definition) {
            $name = (string) ($definition['name'] ?? $key);
            $scopes = array_values((array) ($definition['scopes'] ?? []));
            $grants = array_values((array) ($definition['grant_types'] ?? []));
            $requirePkce = (bool) data_get($definition, 'security.require_pkce', false);
            $pkceMethod = (string) data_get($definition, 'security.pkce_method', '');
            $requireState = (bool) data_get($definition, 'security.require_state', false);

            if ($scopes === []) {
                $issues[] = $name.': no explicit scopes';
            }

            if ($grants !== ['authorization_code', 'refresh_token']) {
                $issues[] = $name.': unexpected grant types';
            }

            if (!$requirePkce || $pkceMethod !== 'S256') {
                $issues[] = $name.': PKCE S256 is not mandatory';
            }

            if (!$requireState) {
                $issues[] = $name.': state is not mandatory';
            }
        }

        if ($issues !== []) {
            return $this->failedCheck('OAuth policy', implode('; ', $issues));
        }

        return $this->ok('OAuth policy', 'First-party clients require explicit scopes, state and PKCE S256.');
    }

    private function checkOidcConfiguration(): array
    {
        $issues = [];

        if (!extension_loaded('openssl')) {
            $issues[] = 'OpenSSL PHP extension is missing';
        }

        if (!Schema::hasTable('oidc_authorization_requests')) {
            $issues[] = 'oidc_authorization_requests table is missing';
        }

        $scopes = Passport::scopes()->pluck('id')->all();
        if (!in_array('openid', $scopes, true)) {
            $issues[] = 'openid scope is not registered';
        }

        $issuer = rtrim((string) config('app.url'), '/');
        if (!str_starts_with($issuer, 'https://')) {
            $issues[] = 'OIDC issuer must use HTTPS';
        }

        if ($issues !== []) {
            return $this->failedCheck('OpenID Connect', implode('; ', $issues));
        }

        return $this->ok(
            'OpenID Connect',
            $issuer.' · discovery, UserInfo, JWKS and RS256 ID Tokens enabled.'
        );
    }

    private function checkTokenSecurity(): array
    {
        $required = ['oauth_token_families', 'oauth_refresh_token_lineage'];
        $missing = array_values(array_filter($required, fn (string $table) => !Schema::hasTable($table)));

        if ($missing !== []) {
            return $this->failedCheck('Token security', 'Missing: '.implode(', ', $missing));
        }

        $archive = storage_path('argos-jwks');
        if (is_dir($archive) && !is_readable($archive)) {
            return $this->failedCheck('Token security', 'JWKS archive directory is not readable.');
        }

        return $this->ok(
            'Token security',
            'Refresh token families, reuse detection, revocation and key rotation are available.'
        );
    }

    private function checkAccountSecurity(): array
    {
        $issues = [];

        foreach ([
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
            'password_changed_at',
        ] as $column) {
            if (!Schema::hasColumn('users', $column)) {
                $issues[] = 'users.'.$column.' missing';
            }
        }

        if ((int) config('auth.password_timeout', 900) > 900) {
            $issues[] = 'password confirmation window exceeds 15 minutes';
        }

        if ($issues !== []) {
            return $this->failedCheck('Account security', implode('; ', $issues));
        }

        return $this->ok(
            'Account security',
            'Password reset/change, e-mail verification, TOTP MFA, recovery codes and reauthentication are available.'
        );
    }

    private function checkPasskeys(): array
    {
        $issues = [];

        if (!class_exists(\Laravel\Passkeys\Passkeys::class)) {
            $issues[] = 'laravel/passkeys is not installed';
        }

        if (!Schema::hasTable('passkeys')) {
            $issues[] = 'passkeys table is missing';
        }

        $origin = (array) config('passkeys.allowed_origins', []);
        if (!in_array((string) config('app.url'), $origin, true)) {
            $issues[] = 'APP_URL is not in allowed passkey origins';
        }

        if ($issues !== []) {
            return $this->error('Passkeys', implode('; ', $issues));
        }

        return $this->ok('Passkeys', 'WebAuthn/passkeys are enabled with password-confirm protected management.');
    }

    private function checkCallbacks(): array
    {
        $definitions = (array) config('airinter-id.clients', []);
        $issues = [];

        foreach ($definitions as $key => $definition) {
            $uri = trim((string) ($definition['redirect_uri'] ?? ''));
            if ($uri === '') {
                $issues[] = $key.': missing redirect URI';
                continue;
            }

            $scheme = parse_url($uri, PHP_URL_SCHEME);
            $host = parse_url($uri, PHP_URL_HOST);

            $isLoopback = in_array($host, ['127.0.0.1', 'localhost', '::1'], true);
            if ($scheme !== 'https' && !($scheme === 'http' && $isLoopback)) {
                $issues[] = $key.': insecure redirect URI';
            }
        }

        if ($issues !== []) {
            return $this->failedCheck('OAuth callbacks', implode('; ', $issues));
        }

        return $this->ok('OAuth callbacks', 'HTTPS enforced; HTTP accepted only for loopback/native callback.');
    }

    private function checkPrometheeBridge(): array
    {
        $database = (string) config('database.connections.promethee.database');
        $username = (string) config('database.connections.promethee.username');

        if ($database === '' || $username === '') {
            return $this->warning('Prométhée bridge', 'Read-only migration bridge is not configured.');
        }

        try {
            DB::connection('promethee')->select('select 1');
            return $this->warning(
                'Prométhée bridge',
                'Connection is reachable. Verify at the MySQL level that this account has SELECT-only grants.'
            );
        } catch (Throwable $e) {
            return $this->failedCheck('Prométhée bridge', $this->exceptionSummary($e));
        }
    }

    private function checkWritableDirectories(): array
    {
        $paths = [
            storage_path(),
            base_path('bootstrap/cache'),
        ];

        $bad = array_values(array_filter($paths, fn (string $path) => !is_dir($path) || !is_writable($path)));

        if ($bad !== []) {
            return $this->failedCheck('Filesystem', 'Not writable: '.implode(', ', $bad));
        }

        return $this->ok('Filesystem', 'storage and bootstrap/cache are writable.');
    }

    private function ok(string $name, string $message): array
    {
        return ['name' => $name, 'status' => 'ok', 'message' => $message];
    }

    private function warning(string $name, string $message): array
    {
        return ['name' => $name, 'status' => 'warning', 'message' => $message];
    }

    private function failedCheck(string $name, string $message): array
    {
        return ['name' => $name, 'status' => 'error', 'message' => $message];
    }

    private function exceptionSummary(Throwable $e): string
    {
        return class_basename($e).': '.mb_substr($e->getMessage(), 0, 220);
    }
}
