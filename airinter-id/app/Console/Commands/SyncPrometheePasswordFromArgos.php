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
        {--all : Synchronize every linked Argos account with a password}
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
        $all = (bool) $this->option('all');

        if ($email === '' && $pilotId === '' && !$all) {
            $this->error('Provide --email, --pilot-id or --all.');
            return self::FAILURE;
        }

        if ($all && ($email !== '' || $pilotId !== '')) {
            $this->error('--all cannot be combined with --email or --pilot-id.');
            return self::FAILURE;
        }

        if ($all && !$this->confirm(
            'Synchronize Argos password hashes to every linked Prométhée account for legacy Hermès login?',
            false
        )) {
            $this->warn('Bulk synchronization cancelled.');
            return self::SUCCESS;
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

        $identities = $all ? $query->get() : collect([$query->first()]);
        $matched = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($identities as $identity) {
            if (!$identity || !$identity->user) {
                $skipped++;
                continue;
            }

            $matched++;

            if (empty($identity->user->password)) {
                $skipped++;
                $this->warn(sprintf(
                    'Skipped %s: no Argos password hash.',
                    $identity->user->email
                ));
                continue;
            }

            $count = DB::connection('promethee')
                ->table('users')
                ->where('id', $identity->external_user_id)
                ->update([
                    'password' => $identity->user->password,
                    'updated_at' => now(),
                ]);

            if ($count < 1) {
                $skipped++;
                $this->warn(sprintf(
                    'Skipped %s: linked Prométhée user #%s was not updated.',
                    $identity->user->email,
                    $identity->external_user_id
                ));
                continue;
            }

            $updated++;
            $this->line(sprintf(
                '%s -> Prométhée user #%s (%s)',
                $identity->user->email,
                $identity->external_user_id,
                $identity->external_ident ?: 'no ident'
            ));
        }

        if ($matched === 0) {
            $this->error('No linked Argos/Prométhée identity matched the requested scope.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->table(
            ['Matched', 'Updated', 'Skipped'],
            [[$matched, $updated, $skipped]]
        );

        if ($updated === 0) {
            $this->error('No Prométhée password hash was synchronized.');
            return self::FAILURE;
        }

        $this->warn('Compatibility bridge only: remove the legacy password dependency once Hermès PKCE/Argos login is deployed.');

        return self::SUCCESS;
    }
}
