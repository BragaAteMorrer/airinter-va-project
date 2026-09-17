<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckTranslations extends Command
{
    protected $signature = 'translations:check {--group=promethee : Translation group to compare}';
    protected $description = 'Check configured language catalogues against French for missing or empty keys';

    public function handle(): int
    {
        $group = (string) $this->option('group');
        $referenceFile = resource_path("lang/fr/{$group}.php");
        if (!is_file($referenceFile)) {
            $this->error("French reference catalogue not found: {$referenceFile}");
            return self::FAILURE;
        }

        $reference = $this->flatten(require $referenceFile);
        $failed = false;
        foreach (array_keys(config('languages')) as $locale) {
            if ($locale === 'fr') continue;
            $file = resource_path("lang/{$locale}/{$group}.php");
            if (!is_file($file)) {
                $this->error("{$locale}: catalogue missing");
                $failed = true;
                continue;
            }
            $translations = $this->flatten(require $file);
            $missing = array_keys(array_diff_key($reference, $translations));
            $empty = array_keys(array_filter($translations, static fn ($value) => trim((string) $value) === ''));
            if ($missing || $empty) {
                $this->error("{$locale}: ".count($missing).' missing, '.count($empty).' empty');
                if ($missing) $this->line('  Missing: '.implode(', ', $missing));
                if ($empty) $this->line('  Empty: '.implode(', ', $empty));
                $failed = true;
            } else {
                $this->info("{$locale}: OK");
            }
        }
        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function flatten(array $items, string $prefix = ''): array
    {
        $flat = [];
        foreach ($items as $key => $value) {
            $path = $prefix === '' ? $key : "{$prefix}.{$key}";
            $flat += is_array($value) ? $this->flatten($value, $path) : [$path => $value];
        }
        return $flat;
    }
}
