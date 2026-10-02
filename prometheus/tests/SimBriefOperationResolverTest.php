<?php

namespace Tests;

use App\Models\Airport;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\UserState;
use App\Models\Enums\AirframeSource;
use App\Models\SimBriefAirframe;
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
        $this->assertSame($resolved['demand']['passengers'], $resolved['parameters']['pax']);

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

    public function test_n262_proxy_uses_sh33_for_calculation_but_keeps_n262_identity(): void
    {
        $origin = Airport::factory()->create(['icao' => 'ZZN1', 'name' => 'Nord Origin']);
        $destination = Airport::factory()->create(['icao' => 'ZZN2', 'name' => 'Nord Destination']);

        $fleet = $this->createSubfleetWithAircraft(1, $origin->id);
        $subfleet = $fleet['subfleet'];
        $subfleet->name = 'Nord 262';
        $subfleet->type = 'N262';
        $subfleet->simbrief_type = null;
        $subfleet->save();

        $aircraft = $fleet['aircraft']->first();
        $aircraft->registration = 'F-BNAA';
        $aircraft->name = 'Nord 262';
        $aircraft->icao = 'N262';
        $aircraft->simbrief_type = null;
        $aircraft->status = AircraftState::PARKED ? AircraftStatus::ACTIVE : AircraftStatus::ACTIVE;
        $aircraft->state = AircraftState::PARKED;
        $aircraft->airport_id = $origin->id;
        $aircraft->save();

        SimBriefAirframe::query()->updateOrCreate([
            'icao' => 'N262',
            'source' => AirframeSource::INTERNAL,
        ], [
            'name' => 'NORD 262',
            'airframe_id' => null,
            'options' => json_encode([
                'simbrief' => [
                    'strategy' => 'proxy',
                    'proxy_type' => 'SH33',
                    'engines' => 'BASTAN VIC',
                    'maxpax' => 29,
                    'weights_kg' => ['mtow' => 10300],
                    'performance' => ['fuelfactor' => 'P00'],
                ],
            ]),
        ]);

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
            'load_factor' => 80,
            'load_factor_variance' => 0,
        ], $subfleet->id);

        $bid = Bid::query()->create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
        ]);

        $this->updateSetting('pireps.only_aircraft_at_dpt_airport', '0');
        $this->updateSetting('bids.block_aircraft', '0');
        $this->updateSetting('simbrief.block_aircraft', '0');

        $resolved = app(SimBriefOperationResolver::class)->resolveOperation('op_'.$bid->id, $user);

        $this->assertSame('N262', $resolved['aircraft']['icao']);
        $this->assertSame('N262', $resolved['aircraft']['simbrief_display_type']);
        $this->assertSame('proxy', $resolved['aircraft']['simbrief_strategy']);
        $this->assertSame('SH33', $resolved['parameters']['type']);
        $this->assertSame(29, $resolved['demand']['capacity']);
        $this->assertLessThanOrEqual(29, $resolved['parameters']['pax']);

        $acdata = json_decode($resolved['parameters']['acdata'], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('N262', $acdata['icao']);
        $this->assertSame('NORD 262', $acdata['name']);
        $this->assertSame('BASTAN VIC', $acdata['engines']);
        $this->assertSame(29, $acdata['maxpax']);
        $this->assertSame(22.708, $acdata['mtow']);
    }

    public function test_custom_sb_airframe_uses_internal_id_as_simbrief_type(): void
    {
        $origin = Airport::factory()->create(['icao' => 'ZZC1']);
        $destination = Airport::factory()->create(['icao' => 'ZZC2']);
        $fleet = $this->createSubfleetWithAircraft(1, $origin->id);
        $subfleet = $fleet['subfleet'];
        $subfleet->name = 'Airbus A319';
        $subfleet->type = 'A319';
        $subfleet->save();

        $aircraft = $fleet['aircraft']->first();
        $aircraft->registration = 'F-GPMA';
        $aircraft->icao = 'A319';
        $aircraft->status = AircraftStatus::ACTIVE;
        $aircraft->state = AircraftState::PARKED;
        $aircraft->airport_id = $origin->id;
        $aircraft->save();

        SimBriefAirframe::query()->updateOrCreate([
            'icao' => 'A319',
            'name' => 'Fenix A319 test',
            'source' => AirframeSource::INTERNAL,
        ], [
            'airframe_id' => '123456_1582090020',
            'options' => json_encode(['simbrief' => ['strategy' => 'custom_airframe']]),
        ]);

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
        $bid = Bid::query()->create([
            'user_id' => $user->id,
            'flight_id' => $flight->id,
            'aircraft_id' => $aircraft->id,
        ]);

        $this->updateSetting('pireps.only_aircraft_at_dpt_airport', '0');
        $this->updateSetting('bids.block_aircraft', '0');
        $this->updateSetting('simbrief.block_aircraft', '0');

        $resolved = app(SimBriefOperationResolver::class)->resolveOperation('op_'.$bid->id, $user);

        $this->assertSame('custom_airframe', $resolved['aircraft']['simbrief_strategy']);
        $this->assertSame('123456_1582090020', $resolved['parameters']['type']);
        $this->assertArrayNotHasKey('acdata', $resolved['parameters']);
        $this->assertSame('A319', $resolved['aircraft']['icao']);
    }

}
