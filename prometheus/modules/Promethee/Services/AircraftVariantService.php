<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\User;
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

        $variants = collect(config('acars.variants.'.$typeKey, []))
            ->filter(fn (array $variant) => in_array($simulator, $variant['simulators'] ?? [], true))
            ->map(function (array $variant) use ($saved) {
                $state = $saved->get($variant['id']);
                return array_merge($variant, [
                    'owned' => $state !== null,
                    'preferred' => $state ? (bool) $state->preferred : false,
                ]);
            })
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

    public function matchesDetectedAdapter(?array $variant, ?string $adapterId): ?bool
    {
        if (!$variant || !$adapterId) return null;
        $accepted = $variant['adapter_ids'] ?? [];
        if ($accepted === []) return null;

        return in_array($adapterId, $accepted, true);
    }
}
