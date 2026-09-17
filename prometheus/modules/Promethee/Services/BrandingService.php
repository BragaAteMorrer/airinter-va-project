<?php

namespace Modules\Promethee\Services;

use Illuminate\Support\Facades\DB;

class BrandingService
{
    private const SETTING_KEY = 'brand_logo';

    /** @return array<string, array{label: string, file: string, description: string}> */
    public function logos(): array
    {
        return [
            'stacked' => [
                'label' => 'Classique vertical',
                'file' => 'air-inter-stacked.png',
                'description' => 'Le mot-symbole AIR / INTER imprimé, bleu et rouge.',
            ],
            'compact' => [
                'label' => 'Classique compact',
                'file' => 'air-inter-compact.png',
                'description' => 'La version courte, adaptée aux espaces étroits.',
            ],
            'chevron' => [
                'label' => 'Double chevron',
                'file' => 'air-inter-chevron.png',
                'description' => 'Le grand emblème rouge et bleu avec le nom de la compagnie.',
            ],
            'seventies' => [
                'label' => 'Chevron historique',
                'file' => 'air-inter-1970s.png',
                'description' => 'Le chevron horizontal et le logotype historique.',
            ],
            'eighties' => [
                'label' => 'Ligne 1980',
                'file' => 'air-inter-1980.png',
                'description' => 'Le mot-symbole horizontal avec son petit chevron.',
            ],
        ];
    }

    /** @return array{key: string, label: string, file: string, description: string, url: string} */
    public function active(): array
    {
        $logos = $this->logos();
        $key = DB::table('promethee_settings')->where('key', self::SETTING_KEY)->value('value') ?: 'stacked';
        $key = array_key_exists($key, $logos) ? $key : 'stacked';

        return ['key' => $key, ...$logos[$key], 'url' => asset('promethee-assets/logos/'.$logos[$key]['file'])];
    }

    public function save(string $key): void
    {
        DB::table('promethee_settings')->updateOrInsert(
            ['key' => self::SETTING_KEY],
            ['value' => $key, 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
