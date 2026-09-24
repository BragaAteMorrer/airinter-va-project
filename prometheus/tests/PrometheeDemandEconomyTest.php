<?php

namespace Tests;

use App\Models\Enums\FareType;
use App\Models\Fare;
use App\Models\Flight;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\DemandProfileService;

final class PrometheeDemandEconomyTest extends TestCase
{
    public function test_bbr_profile_uses_aircraft_capacity_and_stays_stable(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $subfleet = $fleet['subfleet'];
        $aircraft = $fleet['aircraft']->first();

        $subfleet->update(['name' => 'A319-100', 'type' => 'A319']);
        $aircraft->update(['icao' => 'A319', 'name' => 'Airbus A319']);

        $fare = Fare::factory()->create([
            'code' => 'Y',
            'name' => 'Air Inter Economy',
            'type' => FareType::PASSENGER,
            'capacity' => 144,
            'active' => true,
        ]);
        $subfleet->fares()->syncWithoutDetaching([
            $fare->id => ['capacity' => 144, 'price' => null, 'cost' => null],
        ]);

        $flight = Flight::factory()->create([
            'airline_id' => $subfleet->airline_id,
            'load_factor' => 0,
            'load_factor_variance' => 0,
        ]);
        $flight->subfleets()->syncWithoutDetaching([$subfleet->id]);

        DB::table('promethee_pricing')->insert([
            'flight_id' => $flight->id,
            'fare_id' => $fare->id,
            'band' => 'blanc',
            'red_price' => 200,
            'multiplier' => .75,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        foreach ([
            'pricing.bands.enabled' => '1',
            'pricing.bands.blue' => '50',
            'pricing.bands.white' => '75',
            'pricing.demand.white_min' => '78.5',
            'pricing.demand.white_max' => '78.5',
        ] as $key => $value) {
            DB::table('promethee_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        /** @var DemandProfileService $service */
        $service = app(DemandProfileService::class);
        $first = $service->profile($aircraft->fresh('subfleet'), $flight->fresh(), 'OP_TEST_123');
        $second = $service->profile($aircraft->fresh('subfleet'), $flight->fresh(), 'OP_TEST_123');

        $this->assertSame('A319', $service->typeKey($aircraft->fresh('subfleet')));
        $this->assertSame('Airbus A319', $service->typeLabel($aircraft->fresh('subfleet')));
        $this->assertSame('blanc', $first['band']);
        $this->assertSame(144, $first['capacity']);
        $this->assertSame(78.5, $first['load_factor_percent']);
        $this->assertSame(113, $first['passengers']);
        $this->assertSame($first, $second);
    }

    public function test_flight_load_factor_overrides_default_bbr_load_range(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1);
        $subfleet = $fleet['subfleet'];
        $aircraft = $fleet['aircraft']->first();

        $fare = Fare::factory()->create([
            'code' => 'Y2',
            'name' => 'Economy Test',
            'type' => FareType::PASSENGER,
            'capacity' => 100,
            'active' => true,
        ]);
        $subfleet->fares()->syncWithoutDetaching([
            $fare->id => ['capacity' => 100, 'price' => null, 'cost' => null],
        ]);

        $flight = Flight::factory()->create([
            'airline_id' => $subfleet->airline_id,
            'load_factor' => 63,
            'load_factor_variance' => 0,
        ]);
        $flight->subfleets()->syncWithoutDetaching([$subfleet->id]);

        /** @var DemandProfileService $service */
        $service = app(DemandProfileService::class);
        $profile = $service->profile($aircraft->fresh('subfleet'), $flight->fresh(), 'OP_FIXED_LOAD');

        $this->assertSame(63.0, $profile['load_factor_percent']);
        $this->assertSame(63, $profile['passengers']);
    }

    public function test_economy_renders_one_selection_checkbox_per_flight_even_with_multiple_fares(): void
    {
        $admin = $this->createAdminUser();
        $flight = Flight::factory()->create(['active' => true]);

        $firstFare = Fare::factory()->create([
            'code' => 'YA',
            'name' => 'Economy A',
            'type' => FareType::PASSENGER,
            'active' => true,
        ]);
        $secondFare = Fare::factory()->create([
            'code' => 'YB',
            'name' => 'Economy B',
            'type' => FareType::PASSENGER,
            'active' => true,
        ]);
        $flight->fares()->syncWithoutDetaching([
            $firstFare->id => ['price' => 100, 'cost' => null, 'capacity' => 50],
            $secondFare->id => ['price' => 120, 'cost' => null, 'capacity' => 50],
        ]);

        $response = $this->actingAs($admin, 'web')->get('/admin/promethee/economy');

        $response->assertOk();
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'name="flights[]" value="'.$flight->id.'"'),
            'A flight with several fares must still have a single bulk-selection checkbox.'
        );
    }

    public function test_admin_can_save_bbr_fares_and_load_ranges(): void
    {
        $admin = $this->createAdminUser();

        $this->actingAs($admin, 'web')->post('/admin/promethee/tarifs-bbr', [
            'enabled' => 1,
            'blue' => 55,
            'white' => 80,
            'blue_min' => 50,
            'blue_max' => 68,
            'white_min' => 70,
            'white_max' => 86,
            'red_min' => 88,
            'red_max' => 99,
        ])->assertRedirect('/admin/promethee/tarifs-bbr');

        $this->assertSame('55', DB::table('promethee_settings')->where('key', 'pricing.bands.blue')->value('value'));
        $this->assertSame('80', DB::table('promethee_settings')->where('key', 'pricing.bands.white')->value('value'));
        $this->assertSame('88', DB::table('promethee_settings')->where('key', 'pricing.demand.red_min')->value('value'));
        $this->assertSame('99', DB::table('promethee_settings')->where('key', 'pricing.demand.red_max')->value('value'));
    }

    public function test_bbr_band_change_uses_inherited_subfleet_fare_and_restores_red_reference(): void
    {
        $admin = $this->createAdminUser();
        $fleet = $this->createSubfleetWithAircraft(1);
        $subfleet = $fleet['subfleet'];

        $fare = Fare::factory()->create([
            'code' => 'YBREST',
            'name' => 'Air Inter Brest',
            'type' => FareType::PASSENGER,
            'price' => 218,
            'capacity' => 100,
            'active' => true,
        ]);
        $subfleet->fares()->syncWithoutDetaching([
            $fare->id => ['price' => '177', 'cost' => null, 'capacity' => 100],
        ]);

        $flight = Flight::factory()->create([
            'airline_id' => $subfleet->airline_id,
            'active' => true,
        ]);
        $flight->subfleets()->syncWithoutDetaching([$subfleet->id]);

        foreach ([
            'pricing.bands.enabled' => '1',
            'pricing.bands.blue' => '50',
            'pricing.bands.white' => '80',
        ] as $key => $value) {
            DB::table('promethee_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->actingAs($admin, 'web')->post('/admin/promethee/economy/flight-prices', [
            'flight_ids' => [$flight->id],
            'mode' => 'band',
            'band' => 'blanc',
        ])->assertStatus(302);

        $whiteFare = DB::table('flight_fare')
            ->where('flight_id', $flight->id)
            ->where('fare_id', $fare->id)
            ->first();
        $whitePricing = DB::table('promethee_pricing')
            ->where('flight_id', $flight->id)
            ->where('fare_id', $fare->id)
            ->first();

        $this->assertSame(141.6, (float) $whiteFare->price);
        $this->assertSame(177.0, (float) $whitePricing->red_price);
        $this->assertSame('blanc', $whitePricing->band);
        $this->assertSame(0.8, (float) $whitePricing->multiplier);

        $this->actingAs($admin, 'web')->post('/admin/promethee/economy/flight-prices', [
            'flight_ids' => [$flight->id],
            'mode' => 'band',
            'band' => 'rouge',
        ])->assertStatus(302);

        $redFare = DB::table('flight_fare')
            ->where('flight_id', $flight->id)
            ->where('fare_id', $fare->id)
            ->first();
        $redPricing = DB::table('promethee_pricing')
            ->where('flight_id', $flight->id)
            ->where('fare_id', $fare->id)
            ->first();

        $this->assertSame(177.0, (float) $redFare->price);
        $this->assertSame(177.0, (float) $redPricing->red_price);
        $this->assertSame('rouge', $redPricing->band);
        $this->assertSame(1.0, (float) $redPricing->multiplier);
    }

    public function test_single_flight_price_editor_displays_inherited_subfleet_fare_instead_of_global_base(): void
    {
        $admin = $this->createAdminUser();
        $fleet = $this->createSubfleetWithAircraft(1);
        $subfleet = $fleet['subfleet'];

        $fare = Fare::factory()->create([
            'code' => 'YDISPLAY',
            'name' => 'Inherited Economy',
            'type' => FareType::PASSENGER,
            'price' => 218,
            'capacity' => 100,
            'active' => true,
        ]);
        $subfleet->fares()->syncWithoutDetaching([
            $fare->id => ['price' => '177', 'cost' => null, 'capacity' => 100],
        ]);

        $flight = Flight::factory()->create([
            'airline_id' => $subfleet->airline_id,
            'active' => true,
        ]);
        $flight->subfleets()->syncWithoutDetaching([$subfleet->id]);

        $response = $this->actingAs($admin, 'web')
            ->get('/admin/promethee/economy/flight-prices/'.$flight->id.'/edit');

        $response->assertOk();
        $response->assertSee('177,00', false);
        $response->assertSee('RÉFÉRENCE ROUGE', false);
    }

}
