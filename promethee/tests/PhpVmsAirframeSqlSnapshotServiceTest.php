<?php

namespace Tests;

use App\Models\Fare;
use App\Models\Enums\FareType;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\PhpVmsAirframeImportService;
use Modules\Promethee\Services\PhpVmsAirframeSqlSnapshotService;

final class PhpVmsAirframeSqlSnapshotServiceTest extends TestCase
{
    public function test_it_exports_legacy_source_and_materialised_airframes_to_reimportable_sql(): void
    {
        config(['database.connections.prometheus' => config('database.connections.testing')]);
        DB::purge('prometheus');

        $fleet = $this->createSubfleetWithAircraft(1, 'LFPO');
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
            'capacity' => 164,
        ]);
        $subfleet->fares()->attach($fare->id, ['capacity' => '164']);

        $plane = $fleet['aircraft']->first();
        DB::table('aircraft')->where('id', $plane->id)->update([
            'registration' => 'F-SQL1',
            'icao' => 'A320',
            'dow' => 94000,
            'zfw' => 137789.14,
            'mtow' => 162040.58,
            'mlw' => 145505.09,
            'updated_at' => now(),
        ]);

        app(PhpVmsAirframeImportService::class)->import();

        $path = tempnam(sys_get_temp_dir(), 'itva-airframes-');
        $result = app(PhpVmsAirframeSqlSnapshotService::class)->export($path);
        $sql = file_get_contents($path);
        @unlink($path);

        $this->assertNotFalse($sql);
        $this->assertStringContainsString('INSERT INTO `subfleets`', $sql);
        $this->assertStringContainsString('INSERT INTO `fares`', $sql);
        $this->assertStringContainsString('INSERT INTO `subfleet_fare`', $sql);
        $this->assertStringContainsString('INSERT INTO `aircraft`', $sql);
        $this->assertStringContainsString('INSERT INTO `promethee_aircraft_type_profiles`', $sql);
        $this->assertStringContainsString('INSERT INTO `promethee_aircraft_historical_variants`', $sql);
        $this->assertStringContainsString('INSERT INTO `promethee_aircraft_configuration_assignments`', $sql);
        $this->assertStringContainsString('F-SQL1', $sql);
        $this->assertStringContainsString('Y164', $sql);
        $this->assertStringContainsString('ON DUPLICATE KEY UPDATE', $sql);
        $this->assertGreaterThan(0, $result['counts']['aircraft']);
        $this->assertGreaterThan(0, $result['bytes']);
    }
}
