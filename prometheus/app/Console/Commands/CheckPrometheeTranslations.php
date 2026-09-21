<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class CheckPrometheeTranslations extends Command
{
    protected $signature = 'promethee:translations-check';

    protected $description = 'Check that each Promethee translation catalogue has the French reference keys and no empty values';

    public function handle(): int
    {
        $root = resource_path('lang');
        $locales = array_keys(config('languages'));
        $catalogues = ['promethee.php', 'promethee_board.php', 'promethee_javascript.php', 'promethee_accessibility.php', 'promethee_briefing.php'];
        $errors = [];

        foreach ($catalogues as $catalogue) {
            $reference = $this->flatten(require $root.'/fr/'.$catalogue);

            foreach ($locales as $locale) {
                $path = $root.'/'.$locale.'/'.$catalogue;
                if (!is_file($path)) {
                    $errors[] = sprintf('%s: missing %s', $locale, $catalogue);
                    continue;
                }

                $translations = $this->flatten(require $path);
                foreach ($reference as $key => $value) {
                    if (!array_key_exists($key, $translations)) {
                        $errors[] = sprintf('%s: %s is missing in %s', $locale, $key, $catalogue);
                    } elseif ($translations[$key] === null || trim((string) $translations[$key]) === '') {
                        $errors[] = sprintf('%s: %s is empty in %s', $locale, $key, $catalogue);
                    }
                }
            }
        }

        if ($errors !== []) {
            $this->error('Promethee translation catalogue check failed:');
            foreach ($errors as $error) {
                $this->line(' - '.$error);
            }

            return self::FAILURE;
        }

        $this->info('All Promethee catalogue keys are present and non-empty for: '.implode(', ', $locales));

        return self::SUCCESS;
    }

    /** @return array<string, mixed> */
    private function flatten(array $lines, string $prefix = ''): array
    {
        $result = [];
        foreach (Arr::dot($lines) as $key => $value) {
            $result[$prefix.$key] = $value;
        }

        return $result;
    }
}
