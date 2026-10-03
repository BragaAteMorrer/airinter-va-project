<?php

namespace Modules\Promethee\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SimBriefCompanyKeyService
{
    private const KEY = 'simbrief.company_api_key';

    public function configured(): bool
    {
        try {
            return filled($this->get());
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }

    public function get(): ?string
    {
        $stored = DB::table('promethee_settings')->where('key', self::KEY)->value('value');

        if (filled($stored)) {
            try {
                $value = Crypt::decryptString((string) $stored);
                return filled($value) ? trim($value) : null;
            } catch (\Throwable $exception) {
                throw new RuntimeException('La clé API SimBrief enregistrée est illisible. Réenregistrez-la depuis Prométhée.', 0, $exception);
            }
        }

        // Transitional fallback for installations where the key had already
        // been registered through phpVMS settings.
        $legacy = setting('simbrief.api_key');

        return filled($legacy) ? trim((string) $legacy) : null;
    }

    public function save(string $apiKey): void
    {
        $apiKey = trim($apiKey);
        if ($apiKey === '') {
            throw new RuntimeException('La clé API SimBrief ne peut pas être vide.');
        }

        DB::table('promethee_settings')->updateOrInsert(
            ['key' => self::KEY],
            [
                'value' => Crypt::encryptString($apiKey),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function delete(): void
    {
        DB::table('promethee_settings')->where('key', self::KEY)->delete();
    }
}
