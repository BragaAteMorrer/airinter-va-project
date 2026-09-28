<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RotateArgosKeys extends Command
{
    protected $signature = 'argos:keys:rotate {--force : Rotate without interactive confirmation}';
    protected $description = 'Rotate Argos RSA signing keys and revoke active Passport tokens.';

    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('Rotate Argos signing keys and revoke all active OAuth tokens?')) {
            return self::SUCCESS;
        }

        $privatePath = storage_path('oauth-private.key');
        $publicPath = storage_path('oauth-public.key');
        $oldPublic = is_file($publicPath) ? file_get_contents($publicPath) : null;

        if ($oldPublic) {
            $kid = substr(hash('sha256', $oldPublic), 0, 24);
            $archive = storage_path('argos-jwks');
            if (!is_dir($archive) && !mkdir($archive, 0700, true) && !is_dir($archive)) {
                throw new RuntimeException('Unable to create JWKS archive directory.');
            }

            file_put_contents($archive.'/'.$kid.'.pem', $oldPublic, LOCK_EX);
            @chmod($archive.'/'.$kid.'.pem', 0644);
        }

        $key = openssl_pkey_new([
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if (!$key) {
            throw new RuntimeException('Unable to generate RSA key pair.');
        }

        $private = '';
        if (!openssl_pkey_export($key, $private)) {
            throw new RuntimeException('Unable to export RSA private key.');
        }

        $details = openssl_pkey_get_details($key);
        if (!$details || empty($details['key'])) {
            throw new RuntimeException('Unable to export RSA public key.');
        }

        file_put_contents($privatePath, $private, LOCK_EX);
        file_put_contents($publicPath, $details['key'], LOCK_EX);
        @chmod($privatePath, 0600);
        @chmod($publicPath, 0644);

        if (DB::getSchemaBuilder()->hasTable('oauth_access_tokens')) {
            DB::table('oauth_access_tokens')->where('revoked', false)->update(['revoked' => true]);
        }
        if (DB::getSchemaBuilder()->hasTable('oauth_refresh_tokens')) {
            DB::table('oauth_refresh_tokens')->where('revoked', false)->update(['revoked' => true]);
        }
        if (DB::getSchemaBuilder()->hasTable('oauth_token_families')) {
            DB::table('oauth_token_families')->where('status', 'active')->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'revoke_reason' => 'signing_key_rotation',
            ]);
        }

        $newKid = substr(hash('sha256', $details['key']), 0, 24);
        $this->info('Argos signing keys rotated.');
        $this->line('New kid: '.$newKid);
        $this->warn('All active OAuth access/refresh tokens were revoked and clients must authenticate again.');

        return self::SUCCESS;
    }
}
