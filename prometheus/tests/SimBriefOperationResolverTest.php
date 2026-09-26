<?php

namespace Tests;

use App\Models\Airport;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\UserState;
use App\Models\User;
use Modules\Promethee\Services\SimBriefOperationResolver;

final class SimBriefOperationResolverTest extends TestCase
{
    public function test_operation_is_resolved_from_existing_database_relations(): void
    {
        $origin = Airport::factory()->create(['icao' => 'ZZZ1', 'name' => 'Resolver Origin']);
        $destination = Airport::factory()->create(['icao' => 'ZZZ2', 'name' => 'Resolver Destination']);

        $fleet = $this->createSubfleetWithAircraft(1, $origin->id);
        $subfleet = $fleet['subfleet'];
        $subfleet->simbrief_type = 'A320';
        $subfleet->save();

        $aircraft = $fleet['aircraft']->first();
        $aircraft->registration = 'F-GPMB';
        $aircraft->icao = 'A320';
        $aircraft->simbrief_type = 'A320-214';
        $aircraft->status = AircraftStatus::ACTIVE;
        $aircraft->state = AircraftState::PARKED;
        $aircraft->airport_id = $origin->id;
        $aircraft->save();

        $rank = $this->createRank(2, [$subfleet->id]);
        $user = User::factory()->create([
            'airline_id' => $subfleet->airline_id,
            'rank_id' => $rank->id,
            'flight_time' => 1000,
            'state' => UserState::ACTIVE,
        ]);

        $flight = $this->addFlight($user, [
            'flight_number' => 143,
            'dpt_airport_id' => $origin->id,
            'arr_airport_id' => $destination->id,
            'route' => 'DCT RESOLVER',
            'level' => 350,
            'active' => true,
            'visible' => true,
        ], $subfleet->id);

        $bid = Bid::query()->create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
        ]);

        $this->updateSetting('pireps.only_aircraft_at_dpt_airport', '1');
        $this->updateSetting('bids.block_aircraft', '0');
        $this->updateSetting('simbrief.block_aircraft', '0');

        /** @var SimBriefOperationResolver $resolver */
        $resolver = app(SimBriefOperationResolver::class);
        $resolved = $resolver->resolveOperation('op_'.$bid->id, $user);

        $this->assertSame('op_'.$bid->id, $resolved['operation_id']);
        $this->assertSame('ZZZ1', $resolved['origin']['icao']);
        $this->assertSame('ZZZ2', $resolved['destination']['icao']);
        $this->assertSame('F-GPMB', $resolved['aircraft']['registration']);
        $this->assertSame('A320-214', $resolved['aircraft']['simbrief_type']);
        $this->assertSame('aircraft.simbrief_type', $resolved['aircraft']['simbrief_type_source']);
        $this->assertSame('A320-214', $resolved['parameters']['type']);
        $this->assertSame('ZZZ1', $resolved['parameters']['orig']);
        $this->assertSame('ZZZ2', $resolved['parameters']['dest']);
        $this->assertSame('F-GPMB', $resolved['parameters']['reg']);
        $this->assertSame(350, $resolved['parameters']['fl']);
        $this->assertSame('DCT RESOLVER', $resolved['parameters']['route']);

        $public = $resolver->publicView($resolved);
        $this->assertSame('flights', $public['sources']['flight']);
        $this->assertSame('aircraft', $public['sources']['aircraft']);
        $this->assertSame('airports', $public['sources']['origin']);
        $this->assertTrue($public['ready_account']);

        $advanced = $resolver->resolveOperation('op_'.$bid->id, $user, [
            'simbrief_type' => '80_1709125568637',
            'callsign' => 'ITF143',
            'units' => 'LBS',
            'planformat' => 'AFR2017',
            'maps' => 'SIMPLE',
            'navlog' => '0',
            'tlr' => '1',
            'notams' => '1',
            'firnot' => '0',
            'stepclimbs' => '1',
            'etops' => '0',
            'find_sidstar' => 'R',
            'cruise' => 'CI',
            'civalue' => '15',
            'contpct' => '0.05',
            'resvrule' => '30',
            'selcal' => 'AB-CD',
            'deprwy' => '25',
            'arrrwy' => '33',
            'taxiout' => 20,
            'taxiin' => 8,
            'pax' => 138,
            'manualrmk' => 'Hermes dispatch test',
        ]);

        $this->assertSame('80_1709125568637', $advanced['parameters']['type']);
        $this->assertSame('planning_override', $advanced['aircraft']['simbrief_type_source']);
        $this->assertSame('ITF143', $advanced['parameters']['callsign']);
        $this->assertSame('LBS', $advanced['parameters']['units']);
        $this->assertSame('afr2017', $advanced['parameters']['planformat']);
        $this->assertSame('simple', $advanced['parameters']['maps']);
        $this->assertSame('0', $advanced['parameters']['navlog']);
        $this->assertSame('1', $advanced['parameters']['stepclimbs']);
        $this->assertSame('15', $advanced['parameters']['civalue']);
        $this->assertSame(20, $advanced['parameters']['taxiout']);
        $this->assertSame(8, $advanced['parameters']['taxiin']);
        $this->assertSame(138, $advanced['parameters']['pax']);
        $this->assertSame('Hermes dispatch test', $advanced['parameters']['manualrmk']);
    }

    public function test_aircraft_type_falls_back_to_subfleet_without_database_change(): void
    {
        $origin = Airport::factory()->create(['icao' => 'ZZY1']);
        $destination = Airport::factory()->create(['icao' => 'ZZY2']);

        $fleet = $this->createSubfleetWithAircraft(1, $origin->id);
        $subfleet = $fleet['subfleet'];
        $subfleet->simbrief_type = 'A320';
        $subfleet->save();

        $aircraft = $fleet['aircraft']->first();
        $aircraft->registration = 'F-GPMC';
        $aircraft->simbrief_type = null;
        $aircraft->icao = 'A320';
        $aircraft->status = AircraftStatus::ACTIVE;
        $aircraft->state = AircraftState::PARKED;
        $aircraft->save();

        $rank = $this->createRank(2, [$subfleet->id]);
        $user = User::factory()->create([
            'airline_id' => $subfleet->airline_id,
            'rank_id' => $rank->id,
            'flight_time' => 1000,
            'state' => UserState::ACTIVE,
        ]);
        $flight = $this->addFlight($user, [
            'dpt_airport_id' => $origin->id,
            'arr_airport_id' => $destination->id,
            'active' => true,
            'visible' => true,
        ], $subfleet->id);

        $this->updateSetting('pireps.only_aircraft_at_dpt_airport', '0');
        $this->updateSetting('bids.block_aircraft', '0');
        $this->updateSetting('simbrief.block_aircraft', '0');

        /** @var SimBriefOperationResolver $resolver */
        $resolver = app(SimBriefOperationResolver::class);
        $resolved = $resolver->resolveFlightAircraft(
            (string) $flight->id,
            (string) $aircraft->id,
            $user,
            'legacy-test'
        );

        $this->assertSame('A320', $resolved['aircraft']['simbrief_type']);
        $this->assertSame('subfleet.simbrief_type', $resolved['aircraft']['simbrief_type_source']);
    }
}
