<?php

namespace Tests;

use App\Models\User;
use App\Services\FinanceService;
use Illuminate\Support\Facades\DB;
use Modules\Promethee\Services\RegionalOperationsService;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class PrometheeMissionReservationTest extends TestCase
{
    public function test_quote_does_not_charge_jumpseat_when_pilot_already_at_departure(): void
    {
        $user = new User();
        $user->curr_airport_id = 'LFPO';
        $mission = (object) ['dpt_airport_id' => 'LFPO'];

        $quote = app(RegionalOperationsService::class)->quoteRepatriationMission($mission, $user);

        $this->assertSame('LFPO', $quote['origin_airport_id']);
        $this->assertSame('LFPO', $quote['destination_airport_id']);
        $this->assertFalse($quote['needs_jumpseat']);
        $this->assertSame(0, (int) $quote['jumpseat_amount']->getAmount());
    }

    public function test_pilot_cannot_reserve_two_active_repatriations(): void
    {
        $user = $this->createUser(['curr_airport_id' => 'LFPO']);
        $fleet = $this->createSubfleetWithAircraft(2, 'LFPO');
        $aircraft = $fleet['aircraft'];

        $missionIds = [];
        foreach ($aircraft as $plane) {
            $missionIds[] = DB::table('promethee_missions')->insertGetId([
                'title' => 'Test rapatriement '.$plane->registration,
                'description' => 'Mission de test',
                'mission_type' => 'repatriation',
                'aircraft_id' => $plane->id,
                'dpt_airport_id' => 'LFPO',
                'arr_airport_id' => 'LFPG',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('promethee_mission_bookings')->insert([
            'mission_id' => $missionIds[0],
            'user_id' => $user->id,
            'status' => 'reserved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            app(RegionalOperationsService::class)->reserveRepatriationMission(
                (int) $missionIds[1],
                $user,
                app(FinanceService::class),
                ['origin_airport_id' => 'LFPO', 'jumpseat_amount_minor' => 0]
            );
            $this->fail('Second active repatriation booking must be refused.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $this->assertSame(1, DB::table('promethee_mission_bookings')->where('user_id', $user->id)->count());
    }
}
