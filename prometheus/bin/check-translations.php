<?php

declare(strict_types=1);

/**
 * Compare every application catalogue with resources/lang/fr.
 *
 * This deliberately does not bootstrap Laravel, so it can also run in a
 * build image before Composer dependencies have been installed:
 *     php bin/check-translations.php
 */

$root = dirname(__DIR__).'/resources/lang';
$referenceLocale = 'fr';
$locales = ['fr', 'en', 'de', 'es-es', 'it', 'pt-br', 'jp', 'tr'];
$referenceFiles = glob($root.'/'.$referenceLocale.'/*.php') ?: [];
sort($referenceFiles, SORT_STRING);

$flatten = static function (array $lines, string $prefix = '') use (&$flatten): array {
    $flat = [];

    foreach ($lines as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
        if (is_array($value)) {
            $flat += $flatten($value, $path);
        } else {
            $flat[$path] = $value;
        }
    }

    return $flat;
};

$placeholders = static function ($value): array {
    preg_match_all('/(?<!:):[A-Za-z_][A-Za-z0-9_]*/', (string) $value, $matches);
    $tokens = array_values(array_unique($matches[0]));
    sort($tokens, SORT_STRING);

    return $tokens;
};

$report = [];
$details = [];

foreach ($locales as $locale) {
    $report[$locale] = [
        'keys' => 0,
        'missing' => 0,
        'extra' => 0,
        'empty' => 0,
        'placeholder_errors' => 0,
        'english_fallback_sources' => 0,
        'english_duplicates' => 0,
    ];
    $details[$locale] = [];
}

foreach ($referenceFiles as $referencePath) {
    $catalogue = basename($referencePath);
    $reference = $flatten(require $referencePath);
    $englishPath = $root.'/en/'.$catalogue;
    $english = is_file($englishPath) ? $flatten(require $englishPath) : [];

    foreach ($locales as $locale) {
        $path = $root.'/'.$locale.'/'.$catalogue;
        if (!is_file($path)) {
            $report[$locale]['missing'] += count($reference);
            $details[$locale][] = $catalogue.': catalogue missing';
            continue;
        }

        $translated = $flatten(require $path);
        if ($locale !== 'en' && preg_match("~(?:require|include)(?:_once)?\\s*\\(?\\s*__DIR__\\s*\\.\\s*['\"]\\s*/\\.\\./en/~", (string) file_get_contents($path))) {
            ++$report[$locale]['english_fallback_sources'];
            $details[$locale][] = $catalogue.': imports the English catalogue as a fallback source';
        }
        $report[$locale]['keys'] += count($translated);
        $missing = array_keys(array_diff_key($reference, $translated));
        $extra = array_keys(array_diff_key($translated, $reference));
        $report[$locale]['missing'] += count($missing);
        $report[$locale]['extra'] += count($extra);

        foreach ($missing as $key) {
            $details[$locale][] = $catalogue.': missing '.$key;
        }
        foreach ($extra as $key) {
            $details[$locale][] = $catalogue.': extra '.$key;
        }

        foreach ($reference as $key => $value) {
            if (!array_key_exists($key, $translated)) {
                continue;
            }

            if (trim((string) $translated[$key]) === '') {
                ++$report[$locale]['empty'];
                $details[$locale][] = $catalogue.': empty '.$key;
            }

            if ($placeholders($value) !== $placeholders($translated[$key])) {
                ++$report[$locale]['placeholder_errors'];
                $details[$locale][] = $catalogue.': placeholder mismatch '.$key;
            }

            // Equal proper nouns and short technical labels are legitimate. The
            // diagnostic is therefore informational; it is not an exit failure.
            if ($locale !== 'en' && isset($english[$key]) && $translated[$key] === $english[$key]
                && preg_match('/[A-Za-z]{4,}/', (string) $translated[$key])) {
                ++$report[$locale]['english_duplicates'];
            }
        }
    }
}

$referenceKeyCount = 0;
foreach ($referenceFiles as $referencePath) {
    $referenceKeyCount += count($flatten(require $referencePath));
}

echo "French reference: {$referenceKeyCount} keys in ".count($referenceFiles)." catalogues\n\n";
$failed = false;
foreach ($locales as $locale) {
    $counts = $report[$locale];
    printf(
        "%s: keys=%d missing=%d extra=%d empty=%d placeholder_errors=%d english_fallback_sources=%d english_duplicates=%d\n",
        $locale,
        $counts['keys'],
        $counts['missing'],
        $counts['extra'],
        $counts['empty'],
        $counts['placeholder_errors'],
        $counts['english_fallback_sources'],
        $counts['english_duplicates'],
    );

    $failed = $failed || $counts['missing'] > 0 || $counts['extra'] > 0
        || $counts['empty'] > 0 || $counts['placeholder_errors'] > 0 || $counts['english_fallback_sources'] > 0;
}

if ($failed) {
    echo "\nErrors:\n";
    foreach ($details as $locale => $items) {
        foreach ($items as $item) {
            echo "  {$locale}: {$item}\n";
        }
    }
}

exit($failed ? 1 : 0);
