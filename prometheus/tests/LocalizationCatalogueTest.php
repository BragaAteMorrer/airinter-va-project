<?php

namespace Tests;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use PHPUnit\Framework\TestCase;

class LocalizationCatalogueTest extends TestCase
{
    /**
     * This deliberately uses Laravel's file loader directly. It keeps the
     * catalogue contract covered without requiring the application's database
     * bootstrap, and proves that a resolved string did not come from fallback.
     */
    public function test_every_supported_promethee_locale_resolves_its_own_dashboard_translation(): void
    {
        $path = dirname(__DIR__).'/resources/lang';
        $translator = new Translator(new FileLoader(new Filesystem(), $path), 'fr');
        $translator->setFallback('__fallback_must_not_be_used__');
        $expectedHeadings = [
            'fr' => 'Tableau de situation Prométhée.',
            'en' => 'Promethee situation board.',
            'de' => 'Promethee-Lageübersicht.',
            'es-es' => 'Panel de situación Promethee.',
            'it' => 'Quadro della situazione Promethee.',
            'pt-br' => 'Painel de situação Promethee.',
            'jp' => 'Promethee 運航状況ボード。',
            'tr' => 'Promethee durum panosu.',
        ];

        foreach (['fr', 'en', 'de', 'es-es', 'it', 'pt-br', 'jp', 'tr'] as $locale) {
            $catalogue = require $path.'/'.$locale.'/promethee.php';

            self::assertArrayHasKey('dashboard', $catalogue, "{$locale} has no dashboard translation");

            $translator->setLocale($locale);

            self::assertSame($locale, $translator->getLocale());
            self::assertSame(
                $catalogue['dashboard'],
                $translator->get('promethee.dashboard'),
                "promethee.dashboard was not resolved from the {$locale} catalogue"
            );
            self::assertSame(
                $expectedHeadings[$locale],
                $translator->get('promethee.dashboard_page.heading'),
                "promethee.dashboard_page.heading was not translated for {$locale}"
            );
        }
    }
}
