<?php

namespace App\Services;

use App\Models\LegacyIdentity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrometheeUserImporter
{
    public function import(bool $syncPasswords = false, ?callable $progress = null): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'linked' => 0, 'skipped' => 0];

        DB::connection('promethee')
            ->table('users')
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$stats, $syncPasswords, $progress) {
                foreach ($rows as $legacy) {
                    $email = mb_strtolower(trim((string) $legacy->email));
                    if ($email === '') {
                        $stats['skipped']++;
                        continue;
                    }

                    DB::transaction(function () use ($legacy, $email, &$stats, $syncPasswords, $progress) {
                        $user = User::query()->where('email', $email)->first();
                        $created = false;

                        if (!$user) {
                            $user = new User([
                                'subject' => (string) Str::uuid(),
                                'email' => $email,
                            ]);
                            $created = true;
                        }

                        $state = $legacy->deleted_at !== null
                            ? 'deleted'
                            : match ((int) $legacy->state) {
                                0 => 'pending',
                                1 => 'active',
                                2 => 'rejected',
                                3 => 'on_leave',
                                4 => 'suspended',
                                5 => 'deleted',
                                default => 'suspended',
                            };

                        $user->display_name = (string) $legacy->name;
                        $user->preferred_locale ??= 'fr';
                        $user->timezone = filled($legacy->timezone ?? null) ? (string) $legacy->timezone : 'Europe/Paris';
                        $user->state = $state;
                        $user->email_verified_at = $legacy->email_verified_at ?? null;

                        if ($created || $syncPasswords) {
                            // phpVMS uses Laravel Hash::make(). Keeping the encoded hash
                            // means existing pilots do not need a forced password reset.
                            $user->password = $legacy->password;
                        }

                        $user->save();

                        $identity = LegacyIdentity::firstOrNew([
                            'provider' => 'promethee',
                            'external_user_id' => (string) $legacy->id,
                        ]);
                        $identity->fill([
                            'user_id' => $user->id,
                            'external_email' => $email,
                            'external_ident' => $this->legacyIdent($legacy),
                            'metadata' => [
                                'pilot_id' => (int) ($legacy->pilot_id ?? 0),
                                'airline_id' => (int) ($legacy->airline_id ?? 0),
                                'rank_id' => $legacy->rank_id === null ? null : (int) $legacy->rank_id,
                                'legacy_state' => (int) $legacy->state,
                                'legacy_deleted_at' => $legacy->deleted_at,
                            ],
                            'last_synced_at' => now(),
                        ]);
                        if (!$identity->exists) {
                            $identity->linked_at = now();
                        }
                        $identity->save();

                        $stats[$created ? 'created' : 'updated']++;
                        if ($identity->wasRecentlyCreated) {
                            $stats['linked']++;
                        }

                        if ($progress) {
                            $progress($legacy, $user);
                        }
                    });
                }
            });

        return $stats;
    }

    private function legacyIdent(object $legacy): string
    {
        // Air Inter VA currently uses IT + a zero-padded pilot id. Store the
        // existing public identity as a convenience alias; the immutable link
        // remains provider + external_user_id.
        $prefix = (string) config('airinter-id.legacy.pilot_id_prefix', 'IT');
        $length = max(1, (int) config('airinter-id.legacy.pilot_id_length', 3));

        return $prefix.str_pad((string) ((int) ($legacy->pilot_id ?? 0)), $length, '0', STR_PAD_LEFT);
    }
}
