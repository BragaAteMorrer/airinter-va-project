<?php

namespace App\Console\Commands;

use App\Models\LegacyIdentity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPrometheePasswordFromArgos extends Command
{
    protected $signature = 'airinter-id:sync-promethee-password
        {--email= : Limit to one Argos e-mail address}
        {--pilot-id= : Limit to one linked Prométhée pilot ident, e.g. IT199}
        {--force : Required when APP_ENV=production}';

    protected $description = 'Temporarily copy the Argos password hash back to the linked Prométhée account for legacy Hermès authentication.';

    public function handle(): int
    {
        if (app()->isProduction() && !$this->option('force')) {
            $this->error('Production password sync requires --force.');
            return self::FAILURE;
        }

        $email = mb_strtolower(trim((string) $this->option('email')));
        $pilotId = strtoupper(trim((string) $this->option('pilot-id')));

        if ($email === '' && $pilotId === '') {
            $this->error('Provide --email or --pilot-id. Bulk password sync is intentionally disabled.');
            return self::FAILURE;
        }

        $query = LegacyIdentity::query()
            ->with('user')
            ->where('provider', 'promethee');

        if ($email !== '') {
            $query->whereHas('user', fn ($q) => $q->whereRaw('LOWER(email) = ?', [$email]));
        }

        if ($pilotId !== '') {
            $query->whereRaw('UPPER(external_ident) = ?', [$pilotId]);
        }

        /** @var LegacyIdentity|null $identity */
        $identity = $query->first();

        if (!$identity || !$identity->user) {
            $this->error('No linked Argos/Prométhée identity matched the requested account.');
            return self::FAILURE;
        }

        if (empty($identity->user->password)) {
            $this->error('The Argos account does not currently have a password hash to synchronize.');
            return self::FAILURE;
        }

        $updated = DB::connection('promethee')
            ->table('users')
            ->where('id', $identity->external_user_id)
            ->update([
                'password' => $identity->user->password,
                'updated_at' => now(),
            ]);

        if ($updated < 1) {
            $this->error('The linked Prométhée user was not updated.');
            return self::FAILURE;
        }

        $this->info(sprintf(
            'Password hash synchronized for %s -> Prométhée user #%s (%s).',
            $identity->user->email,
            $identity->external_user_id,
            $identity->external_ident ?: 'no ident'
        ));
        $this->warn('Compatibility bridge only: remove the legacy password dependency once Hermès PKCE/Argos login is deployed.');

        return self::SUCCESS;
    }
}
