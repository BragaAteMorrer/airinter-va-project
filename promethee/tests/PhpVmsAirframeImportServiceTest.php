<?php

namespace Tests;

use App\Models\Fare;
use App\Models\Enums\FareType;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Models\AircraftConfigurationAssignment;
use Modules\Promethee\Models\AircraftHistoricalVariant;
use Modules\Promethee\Models\AircraftTypeProfile;
use Modules\Promethee\Services\PhpVmsAirframeImportService;

final class PhpVmsAirframeImportServiceTest extends TestCase
{
    public function test_it_imports_fare_capacity_common_weights_and_tail_overrides(): void
    {
        $fleet = $this->createSubfleetWithAircraft(3, 'LFPO');
        $subfleet = $fleet['subfleet'];
        $subfleet->airline()->update(['icao' => 'ITF']);
        $subfleet->update([
            'type' => 'A320-211',
            'name' => 'Airbus A320-211',
            'simbrief_type' => 'A320',
        ]);

        $fare = Fare::factory()->create([
            'code' => 'Y',
            'name' => 'Air Inter Economy',
            'type' => FareType::PASSENGER,
            'capacity' => 218,
        ]);
        $subfleet->fares()->attach($fare->id, [
            'capacity' => '164',
            'price' => null,
            'cost' => null,
        ]);

        $planes = $fleet['aircraft']->sortBy('id')->values();
        foreach ($planes as $index => $plane) {
            DB::table('aircraft')->where('id', $plane->id)->update([
                'registration' => 'F-TST'.($index + 1),
                'icao' => 'A320',
                // phpVMS stores mass internally in pounds. Two aircraft share
                // the normal value; the third has a registration-specific MTOW.
                'dow' => 94000,
                'zfw' => 137789.14,
                'mtow' => $index < 2 ? 162040.58 : 165346.70,
                'mlw' => 145505.09,
                'updated_at' => now(),
            ]);
        }

        /** @var PhpVmsAirframeImportService $service */
        $service = app(PhpVmsAirframeImportService::class);
        $result = $service->import();

        $this->assertSame(1, $result['passenger_capacities']);
        $this->assertSame(3, $result['aircraft']);
        $this->assertSame(3, $result['assignments_created']);
        $this->assertSame(1, $result['tail_overrides']);

        $type = AircraftTypeProfile::query()->where('type_key', 'ITFA320211')->firstOrFail();
        $this->assertSame(164, $type->data['max_pax']);
        $this->assertSame('Y164', $type->data['seat_configuration']);
        $this->assertSame('A320-211', $type->data['legacy_phpvms']['type']);
        $this->assertSame('Y', $type->data['legacy_phpvms']['fares'][0]['code']);
        $this->assertEquals(164.0, $type->data['legacy_phpvms']['fares'][0]['capacity']);

        $variant = AircraftHistoricalVariant::query()
            ->where('type_key', 'ITFA320211')
            ->where('code', 'A320-211')
            ->firstOrFail();

        $this->assertSame('kg', $variant->data['weight_unit']);
        $this->assertEqualsWithDelta(73500, (float) $variant->data['mtow'], 1.0);

        $outlier = $planes[2]->fresh();
        $assignment = AircraftConfigurationAssignment::query()
            ->where('aircraft_id', $outlier->id)
            ->firstOrFail();

        $this->assertEqualsWithDelta(75000, (float) $assignment->overrides['mtow'], 1.0);
        $this->assertSame('F-TST3', $assignment->overrides['legacy_phpvms']['registration']);
    }

    public function test_it_uses_cargo_fares_and_preserves_manual_assignments(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $subfleet = $fleet['subfleet'];
        $subfleet->airline()->update(['icao' => 'ICS']);
        $subfleet->update([
            'type' => 'B737-2series',
            'name' => 'Boeing 737-2series',
        ]);

        $fare = Fare::factory()->create([
            'code' => 'CGO',
            'name' => 'Cargo',
            'type' => FareType::CARGO,
            'capacity' => 1000,
        ]);
        $subfleet->fares()->attach($fare->id, [
            'capacity' => '14360',
            'price' => null,
            'cost' => null,
        ]);

        $plane = $fleet['aircraft']->first();
        $plane->refresh();
        $plane->registration = 'F-CARGO';
        $plane->icao = 'B732';
        $plane->save();

        $manualVariant = AircraftHistoricalVariant::query()->create([
            'type_key' => 'B732',
            'code' => 'B732_MANUAL',
            'name' => 'Boeing 737-200 manuel',
            'data' => ['max_pax' => 0],
            'historical_confidence' => 'confirmed',
            'active' => true,
            'source' => 'Documentation Air Inter',
        ]);

        $manual = AircraftConfigurationAssignment::query()->create([
            'aircraft_id' => $plane->id,
            'variant_id' => $manualVariant->id,
            'active' => true,
            'overrides' => ['fuel_factor' => 2.5],
            'historical_confidence' => 'confirmed',
            'source' => 'Documentation Air Inter',
        ]);

        /** @var PhpVmsAirframeImportService $service */
        $service = app(PhpVmsAirframeImportService::class);
        $result = $service->import();

        $this->assertSame(1, $result['cargo_capacities']);
        $this->assertSame(1, $result['manual_assignments_preserved']);

        $type = AircraftTypeProfile::query()->where('type_key', 'ICSB7372SERIES')->firstOrFail();
        $this->assertEquals(14360.0, $type->data['max_cargo']);
        $this->assertSame('kg', $type->data['weight_unit']);

        $manual->refresh();
        $this->assertSame($manualVariant->id, $manual->variant_id);
        $this->assertSame('Documentation Air Inter', $manual->source);
        $this->assertEquals(2.5, $manual->overrides['fuel_factor']);
        $this->assertEquals(14360.0, $manual->overrides['max_cargo']);
        $this->assertSame('kg', $manual->overrides['weight_unit']);
        $this->assertSame('F-CARGO', $manual->overrides['legacy_phpvms']['registration']);
    }

    public function test_import_is_idempotent_for_its_own_assignments(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
        $subfleet = $fleet['subfleet'];
        $subfleet->airline()->update(['icao' => 'ITF']);
        $subfleet->update([
            'type' => 'Nord-262',
            'name' => 'Nord N262B Frégate',
        ]);

        $fare = Fare::factory()->create([
            'code' => 'Y',
            'name' => 'Economy',
            'type' => FareType::PASSENGER,
            'capacity' => 28,
        ]);
        $subfleet->fares()->attach($fare->id, ['capacity' => '28']);

        /** @var PhpVmsAirframeImportService $service */
        $service = app(PhpVmsAirframeImportService::class);
        $first = $service->import();
        $second = $service->import();

        $plane = $fleet['aircraft']->first();

        $this->assertSame(1, $first['assignments_created']);
        $this->assertSame(0, $second['assignments_created']);
        $this->assertSame(1, $second['assignments_updated']);
        $this->assertSame(
            1,
            AircraftConfigurationAssignment::query()->where('aircraft_id', $plane->id)->count()
        );
    }
}
