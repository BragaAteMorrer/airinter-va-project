<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Enums\AirframeSource;
use App\Models\SimBriefAirframe;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AircraftVariantService
{
    public function __construct(private readonly DemandProfileService $demand) {}

    /**
     * Pilot-facing library.
     *
     * Prométhée/phpVMS airframes are authoritative. The historical Hermès
     * catalogue remains as a compatibility fallback for add-ons which have not
     * yet been created under Administration > Airframes.
     */
    public function catalog(): array
    {
        $server = SimBriefAirframe::query()
            ->where('source', AirframeSource::INTERNAL)
            ->orderBy('icao')
            ->orderBy('name')
            ->get()
            ->map(fn (SimBriefAirframe $airframe) => $this->airframeVariant($airframe))
            ->values();

        $legacy = collect(config('acars.variants', []))
            ->flatMap(fn (array $variants, string $typeKey) => collect($variants)->map(
                fn (array $variant) => array_merge($variant, [
                    'type_key' => $typeKey,
                    'source' => $variant['source'] ?? 'hermes_catalog',
                ])
            ));

        return $server
            ->concat($legacy)
            ->unique(fn (array $variant) => $this->variantIdentity($variant))
            ->values()
            ->all();
    }

    public function accountLibrary(User $user, ?string $simulator = null): array
    {
        $saved = DB::table('promethee_pilot_aircraft_variants')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('variant_id');

        return collect($this->catalog())
            ->filter(fn (array $variant) => $simulator === null
                || in_array($simulator, $variant['simulators'] ?? [], true))
            ->map(function (array $variant) use ($saved) {
                $state = $saved->get($variant['id']);

                return array_merge($variant, [
                    // Server-managed airframes exist independently of the
                    // pilot's add-on library. They are always selectable.
                    'owned' => $this->isServerManaged($variant)
                        ? true
                        : ($state ? (bool) $state->enabled : false),
                    'preferred' => $state ? (bool) $state->preferred : false,
                ]);
            })
            ->values()
            ->all();
    }

    public function replaceAccountLibrary(User $user, array $variantIds, ?string $preferredVariantId = null): array
    {
        $catalogIds = collect($this->catalog())->pluck('id')->all();
        $variantIds = array_values(array_unique(array_filter(
            $variantIds,
            fn ($id) => in_array($id, $catalogIds, true)
        )));

        if ($preferredVariantId !== null && !in_array($preferredVariantId, $variantIds, true)) {
            $preferredVariantId = null;
        }

        DB::transaction(function () use ($user, $variantIds, $preferredVariantId) {
            DB::table('promethee_pilot_aircraft_variants')->where('user_id', $user->id)->delete();

            foreach ($variantIds as $variantId) {
                DB::table('promethee_pilot_aircraft_variants')->insert([
                    'user_id' => $user->id,
                    'variant_id' => $variantId,
                    'enabled' => true,
                    'preferred' => $preferredVariantId === $variantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return $this->accountLibrary($user);
    }

    public function optionsForBid(Bid $bid, User $user, string $simulator): array
    {
        if (!$bid->aircraft) {
            return [
                'type_key' => null,
                'selected_variant_id' => null,
                'variants' => [],
            ];
        }

        $typeKey = $this->demand->typeKey($bid->aircraft);
        $saved = DB::table('promethee_pilot_aircraft_variants')
            ->where('user_id', $user->id)
            ->where('enabled', true)
            ->get()
            ->keyBy('variant_id');

        $configured = collect(config('acars.variants.'.$typeKey, []))
            ->filter(fn (array $variant) => in_array($simulator, $variant['simulators'] ?? [], true))
            ->map(fn (array $variant) => array_merge($variant, [
                'type_key' => $typeKey,
                'source' => $variant['source'] ?? 'hermes_catalog',
            ]));

        // The phpVMS/Prométhée SimBrief airframe catalogue is the source of
        // truth. It must come before the legacy desktop catalogue so a matching
        // hard-coded Internal ID can never shadow an admin-managed airframe.
        $database = collect($this->databaseAirframesForAircraft($bid->aircraft))
            ->filter(fn (array $variant) => in_array($simulator, $variant['simulators'] ?? [], true));

        $variants = $database
            ->concat($configured)
            ->unique(fn (array $variant) => $this->variantIdentity($variant))
            ->map(function (array $variant) use ($saved, $configured) {
                $state = $saved->get($variant['id']);

                // Migrate preference/ownership transparently from a historical
                // Hermès catalogue ID to its equivalent server airframe.
                if ($state === null && $this->isServerManaged($variant)) {
                    $legacy = $configured->first(
                        fn (array $candidate) => $this->variantIdentity($candidate) === $this->variantIdentity($variant)
                    );
                    if ($legacy) {
                        $state = $saved->get($legacy['id']);
                    }
                }

                return array_merge($variant, [
                    'owned' => $this->isServerManaged($variant)
                        ? true
                        : $state !== null,
                    'preferred' => $state ? (bool) $state->preferred : false,
                ]);
            })
            ->values();

        $selection = DB::table('promethee_operation_aircraft_variants')
            ->where('bid_id', $bid->id)
            ->first();
        $selectedId = $selection?->variant_id;

        // If an operation still references an old hard-coded variant ID, map
        // it to the equivalent database airframe instead of silently falling
        // back to "Default".
        if ($selectedId && !$variants->contains(fn (array $variant) => $variant['id'] === $selectedId)) {
            $legacySelected = $configured->first(fn (array $variant) => $variant['id'] === $selectedId);
            if ($legacySelected) {
                $replacement = $variants->first(
                    fn (array $variant) => $this->variantIdentity($variant) === $this->variantIdentity($legacySelected)
                );
                $selectedId = $replacement['id'] ?? null;
            }
        }

        if (!$selectedId || !$variants->contains(fn (array $variant) => $variant['id'] === $selectedId)) {
            $selectedId = optional($variants->first(fn ($variant) => ($variant['preferred'] ?? false)))['id']
                ?? optional($variants->first(fn ($variant) => ($variant['source'] ?? null) === 'phpvms_admin_airframe'))['id']
                ?? optional($variants->first(fn ($variant) => ($variant['default'] ?? false)))['id']
                ?? optional($variants->first())['id'];
        }

        return [
            'type_key' => $typeKey,
            'selected_variant_id' => $selectedId,
            'variants' => $variants->map(fn (array $variant) => array_merge($variant, [
                'selected' => $variant['id'] === $selectedId,
            ]))->all(),
        ];
    }

    public function selectForBid(Bid $bid, User $user, string $variantId, string $simulator): array
    {
        abort_if(!$bid->aircraft, 409, 'Sélectionnez d’abord un appareil précis.');

        $options = $this->optionsForBid($bid, $user, $simulator);
        $variant = collect($options['variants'])->first(fn ($item) => $item['id'] === $variantId);
        abort_if(!$variant, 409, 'Cette variante n’est pas compatible avec le type d’appareil ou le simulateur sélectionné.');

        DB::table('promethee_operation_aircraft_variants')->updateOrInsert(
            ['bid_id' => $bid->id],
            [
                'user_id' => $user->id,
                'variant_id' => $variantId,
                'simulator' => $simulator,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return array_merge($options, [
            'selected_variant_id' => $variantId,
            'selected_variant' => array_merge($variant, ['selected' => true]),
            'variants' => collect($options['variants'])->map(fn (array $item) => array_merge($item, [
                'selected' => $item['id'] === $variantId,
            ]))->all(),
        ]);
    }

    public function selectedForBid(Bid $bid, ?User $user = null): ?array
    {
        if (!$bid->aircraft) return null;

        $selection = DB::table('promethee_operation_aircraft_variants')
            ->where('bid_id', $bid->id)
            ->first();
        $simulator = $selection?->simulator ?: 'msfs2020';
        $options = $this->optionsForBid($bid, $user ?? $bid->user, $simulator);

        return collect($options['variants'])->first(
            fn ($variant) => $variant['id'] === $options['selected_variant_id']
        );
    }

    public function defaultForAircraft(Aircraft $aircraft): ?array
    {
        return collect($this->databaseAirframesForAircraft($aircraft))
            ->sortByDesc(fn (array $variant) => (bool) ($variant['default'] ?? false))
            ->first();
    }

    private function databaseAirframesForAircraft(Aircraft $aircraft): array
    {
        $icaos = collect([
            $aircraft->icao,
            $aircraft->subfleet?->type,
            $aircraft->subfleet?->simbrief_type,
        ])->filter()
            ->map(fn ($value) => strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $value)))
            ->filter(fn ($value) => strlen($value) >= 3 && strlen($value) <= 4)
            ->unique()
            ->values();

        if ($icaos->isEmpty()) return [];

        return SimBriefAirframe::query()
            ->whereIn('icao', $icaos)
            ->orderBy('source')
            ->orderBy('name')
            ->get()
            ->map(fn (SimBriefAirframe $airframe) => $this->airframeVariant($airframe))
            ->all();
    }

    private function airframeVariant(SimBriefAirframe $airframe): array
    {
        $details = json_decode((string) $airframe->details, true) ?: [];
        $options = $airframe->decodedOptions();
        $planning = $airframe->simbriefProfile();

        $simulators = $this->normaliseStringList(
            $details['simulators']
                ?? $details['simulator']
                ?? $options['simulators']
                ?? $options['simulator']
                ?? []
        );
        if ($simulators === []) {
            $simulators = ['fs2004', 'fsx', 'p3d', 'msfs2020', 'msfs2024', 'xplane'];
        }

        $adapterIds = $this->normaliseStringList(
            $details['adapter_ids']
                ?? $details['adapter_id']
                ?? $options['adapter_ids']
                ?? $options['adapter_id']
                ?? []
        );

        $vendor = trim((string) (
            $details['vendor']
                ?? $details['manufacturer']
                ?? $options['vendor']
                ?? ''
        ));

        $strategy = $planning['strategy'];
        $simbriefType = match ($strategy) {
            'custom_airframe' => $planning['internal_id'],
            'proxy' => $planning['proxy_type'],
            default => strtoupper((string) $airframe->icao),
        };

        return [
            'id' => 'sbaf:'.$airframe->id,
            'label' => trim((string) $airframe->name) ?: strtoupper((string) $airframe->icao),
            'vendor' => $vendor !== '' ? $vendor : null,
            'simbrief_type' => filled($simbriefType) ? (string) $simbriefType : null,
            'simbrief_strategy' => $strategy,
            'simbrief_profile' => $planning,
            'simulators' => $simulators,
            'adapter_ids' => $adapterIds,
            'default' => (bool) ($details['default'] ?? $options['default'] ?? false),
            'source' => ((int) $airframe->source === AirframeSource::INTERNAL)
                ? 'phpvms_admin_airframe'
                : 'simbrief_airframe',
            'airframe_db_id' => $airframe->id,
            'icao' => strtoupper((string) $airframe->icao),
            'type_key' => strtoupper((string) $airframe->icao),
            'image_url' => $this->airframeImage($details, $options),
            'options' => $options,
            'details' => $details,
        ];
    }

    private function variantIdentity(array $variant): string
    {
        $simbrief = strtoupper(trim((string) ($variant['simbrief_type'] ?? '')));
        if (($variant['simbrief_strategy'] ?? null) === 'proxy') {
            $actual = strtoupper(trim((string) ($variant['icao'] ?? $variant['type_key'] ?? '')));
            return 'PROXY:'.$actual.':'.$simbrief;
        }
        if ($simbrief !== '') return 'SB:'.$simbrief;

        $icao = strtoupper(trim((string) ($variant['icao'] ?? $variant['type_key'] ?? '')));
        $label = strtoupper(trim((string) ($variant['label'] ?? $variant['id'] ?? '')));

        return 'FALLBACK:'.$icao.':'.$label;
    }

    private function isServerManaged(array $variant): bool
    {
        return in_array($variant['source'] ?? null, ['phpvms_admin_airframe', 'simbrief_airframe'], true);
    }

    private function normaliseStringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = preg_split('/[;,\s]+/', trim($value)) ?: [];
        }

        if (!is_array($value)) return [];

        return collect($value)
            ->filter(fn ($item) => is_scalar($item) && trim((string) $item) !== '')
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->unique()
            ->values()
            ->all();
    }

    private function airframeImage(array ...$sources): ?string
    {
        foreach ($sources as $source) {
            foreach (['image_url', 'airframe_image', 'aircraft_image', 'image', 'thumbnail'] as $key) {
                $value = $source[$key] ?? null;
                if (is_string($value)
                    && filter_var($value, FILTER_VALIDATE_URL)
                    && str_starts_with(strtolower($value), 'https://')) {
                    return $value;
                }
            }
        }

        return null;
    }

    public function matchesDetectedAdapter(?array $variant, ?string $adapterId): ?bool
    {
        if (!$variant || !$adapterId) return null;
        $accepted = $variant['adapter_ids'] ?? [];
        if ($accepted === []) return null;

        return in_array(strtolower($adapterId), array_map('strtolower', $accepted), true);
    }
}
