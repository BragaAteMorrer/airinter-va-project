<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\FleetBaseQuotaService;
use Modules\Promethee\Services\RegionalOperationsService;

final class PrometheeFleetBaseQuotaTest extends TestCase
{
    public function test_excel_reference_contains_exact_223_aircraft_and_base_totals(): void
    {
        $this->assertSame(223, (int) DB::table('promethee_fleet_base_targets')->sum('target_count'));

        $totals = DB::table('promethee_fleet_base_targets')
            ->selectRaw('base_airport_id, SUM(target_count) as total')
            ->groupBy('base_airport_id')
            ->pluck('total', 'base_airport_id')
            ->map(fn ($value) => (int) $value);

        $this->assertSame(84, $totals['LFPO']);
        $this->assertSame(29, $totals['LFMN']);
        $this->assertSame(26, $totals['LFLL']);
        $this->assertSame(31, $totals['LFML']);
        $this->assertSame(27, $totals['LFBO']);
        $this->assertSame(26, $totals['LFBD']);
    }

    public function test_forced_sync_still_ignores_aircraft_away_for_five_days(): void
    {
        $fleet = $this->createSubfleetWithAircraft(1, 'LFMN');
        $aircraft = $fleet['aircraft']->first();

        $aircraft->update([
            'hub_id' => 'LFPO',
            'airport_id' => 'LFMN',
            'landing_time' => now()->subDays(4),
        ]);

        DB::table('promethee_aircraft_bases')->updateOrInsert(
            ['aircraft_id' => $aircraft->id],
            [
                'base_airport_id' => 'LFPO',
                'assigned_at' => now()->subDays(30),
                'away_since' => now()->subDays(4),
                'repatriation_mission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        /** @var RegionalOperationsService $operations */
        $operations = app(RegionalOperationsService::class);
        $result = $operations->sync(true);

        $this->assertSame(0, DB::table('promethee_missions')
            ->where('mission_type', 'repatriation')
            ->where('aircraft_id', $aircraft->id)
            ->where('active', true)
            ->count());
        $this->assertSame(0, (int) $result['created']);

        DB::table('promethee_aircraft_bases')->where('aircraft_id', $aircraft->id)->update([
            'away_since' => now()->subDays(6),
            'updated_at' => now(),
        ]);
        $aircraft->update(['landing_time' => now()->subDays(6)]);

        $result = $operations->sync(true);

        $this->assertSame(1, DB::table('promethee_missions')
            ->where('mission_type', 'repatriation')
            ->where('aircraft_id', $aircraft->id)
            ->where('active', true)
            ->count());
        $this->assertSame(1, (int) $result['created']);
    }

    public function test_manual_assignment_cannot_break_a_compliant_quota(): void
    {
        $fleet = $this->createSubfleetWithAircraft(2, 'LFPO');
        $subfleet = $fleet['subfleet'];
        $planes = $fleet['aircraft']->values();

        $subfleet->update(['type' => 'QUOTA-TEST']);
        DB::table('airlines')->where('id', $subfleet->airline_id)->update([
            'icao' => 'ZZQ',
            'updated_at' => now(),
        ]);

        DB::table('promethee_fleet_base_targets')->insert([
            [
                'airline_icao' => 'ZZQ',
                'subfleet_type' => 'QUOTA-TEST',
                'base_airport_id' => 'LFPO',
                'target_count' => 1,
                'source' => 'test',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'airline_icao' => 'ZZQ',
                'subfleet_type' => 'QUOTA-TEST',
                'base_airport_id' => 'LFMN',
                'target_count' => 1,
                'source' => 'test',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $planes[0]->update(['airport_id' => 'LFPO', 'hub_id' => 'LFPO']);
        $planes[1]->update(['airport_id' => 'LFMN', 'hub_id' => 'LFMN']);

        foreach ([[$planes[0], 'LFPO'], [$planes[1], 'LFMN']] as [$plane, $base]) {
            DB::table('promethee_aircraft_bases')->updateOrInsert(
                ['aircraft_id' => $plane->id],
                [
                    'base_airport_id' => $base,
                    'assigned_at' => now(),
                    'away_since' => null,
                    'repatriation_mission_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        /** @var FleetBaseQuotaService $quota */
        $quota = app(FleetBaseQuotaService::class);
        $decision = $quota->assignmentDecision($planes[0]->fresh(), 'LFMN');

        $this->assertFalse($decision['allowed']);

        // Create a real drift (2 at LFPO, 0 at LFMN): the correction back to
        // LFMN must now be accepted because it reduces the Excel delta.
        DB::table('promethee_aircraft_bases')->where('aircraft_id', $planes[1]->id)->update([
            'base_airport_id' => 'LFPO',
            'updated_at' => now(),
        ]);
        $planes[1]->update(['hub_id' => 'LFPO', 'airport_id' => 'LFPO']);

        $decision = $quota->assignmentDecision($planes[1]->fresh(), 'LFMN');
        $this->assertTrue($decision['allowed']);
    }
}
