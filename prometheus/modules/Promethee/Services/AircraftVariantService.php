<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\User;
use App\Models\SimBriefAirframe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AircraftVariantService
{
    public function __construct(private readonly DemandProfileService $demand) {}

    public function catalog(): array
    {
        return collect(config('acars.variants', []))
            ->flatMap(fn (array $variants, string $typeKey) => collect($variants)->map(
                fn (array $variant) => array_merge($variant, ['type_key' => $typeKey])
            ))
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
            ->filter(fn (array $variant) => $simulator === null || in_array($simulator, $variant['simulators'] ?? [], true))
            ->map(function (array $variant) use ($saved) {
                $state = $saved->get($variant['id']);
                return array_merge($variant, [
                    'owned' => $state ? (bool) $state->enabled : false,
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
            ->map(function (array $variant) use ($saved) {
                $state = $saved->get($variant['id']);
                return array_merge($variant, [
                    'owned' => $state !== null,
                    'preferred' => $state ? (bool) $state->preferred : false,
                    'source' => $variant['source'] ?? 'hermes_catalog',
                ]);
            });

        // phpVMS already maintains the SimBrief airframe catalogue used by
        // /admin/airframes. Surface those same profiles in Hermès so the
        // desktop client and web flight planner cannot disagree.
        $database = collect($this->databaseAirframesForAircraft($bid->aircraft))
            ->map(function (array $variant) use ($saved) {
                $state = $saved->get($variant['id']);
                return array_merge($variant, [
                    // Server-managed airframes are usable without requiring a
                    // duplicate "owned add-on" toggle in the pilot library.
                    'owned' => true,
                    'preferred' => $state ? (bool) $state->preferred : false,
                ]);
            });

        $variants = $configured
            ->concat($database)
            ->unique(fn (array $variant) => strtoupper((string) ($variant['simbrief_type'] ?? $variant['id'])))
            ->values();

        $selection = DB::table('promethee_operation_aircraft_variants')->where('bid_id', $bid->id)->first();
        $selectedId = $selection?->variant_id;

        if (!$selectedId || !$variants->contains(fn ($variant) => $variant['id'] === $selectedId)) {
            $selectedId = optional($variants->first(fn ($variant) => ($variant['preferred'] ?? false) && ($variant['owned'] ?? false)))['id']
                ?? optional($variants->first(fn ($variant) => $variant['owned'] ?? false))['id']
                ?? optional($variants->first(fn ($variant) => $variant['default'] ?? false))['id']
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

        $selection = DB::table('promethee_operation_aircraft_variants')->where('bid_id', $bid->id)->first();
        $simulator = $selection?->simulator ?: 'msfs2020';
        $options = $this->optionsForBid($bid, $user ?? $bid->user, $simulator);

        return collect($options['variants'])->first(
            fn ($variant) => $variant['id'] === $options['selected_variant_id']
        );
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
            ->map(function (SimBriefAirframe $airframe) {
                $details=json_decode((string) $airframe->details,true) ?: [];
                $options=json_decode((string) $airframe->options,true) ?: [];
                $image=$this->airframeImage($details);

                return [
                    'id' => 'sbaf:'.$airframe->id,
                    'label' => trim((string) $airframe->name) ?: strtoupper((string) $airframe->icao),
                    'simbrief_type' => filled($airframe->airframe_id)
                        ? (string) $airframe->airframe_id
                        : strtoupper((string) $airframe->icao),
                    'simulators' => ['fs2004','fsx','p3d','msfs2020','msfs2024','xplane'],
                    'adapter_ids' => [],
                    'default' => false,
                    'source' => ((int) $airframe->source === 0) ? 'phpvms_admin_airframe' : 'simbrief_airframe',
                    'airframe_db_id' => $airframe->id,
                    'icao' => strtoupper((string) $airframe->icao),
                    'image_url' => $image,
                    'options' => $options,
                ];
            })
            ->all();
    }

    private function airframeImage(array $details): ?string
    {
        foreach (['image_url','airframe_image','aircraft_image','image','thumbnail'] as $key) {
            $value=$details[$key] ?? null;
            if (is_string($value) && filter_var($value,FILTER_VALIDATE_URL)
                && str_starts_with(strtolower($value),'https://')) {
                return $value;
            }
        }

        return null;
    }

    public function matchesDetectedAdapter(?array $variant, ?string $adapterId): ?bool
    {
        if (!$variant || !$adapterId) return null;
        $accepted = $variant['adapter_ids'] ?? [];
        if ($accepted === []) return null;

        return in_array($adapterId, $accepted, true);
    }
}
