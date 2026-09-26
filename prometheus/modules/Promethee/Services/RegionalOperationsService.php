<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\User;
use App\Services\AirportService;
use App\Services\FinanceService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;

class RegionalOperationsService
{
    public function settings(): array
    {
        $values = DB::table('promethee_settings')
            ->whereIn('key', [
                'regional.repatriation_mission_after_days',
                'regional.auto_return_after_days',
                'regional.repatriation_reward_multiplier',
            ])->pluck('value', 'key');

        return [
            'mission_after_days' => max(1, (int) ($values['regional.repatriation_mission_after_days'] ?? 10)),
            'auto_return_after_days' => max(2, (int) ($values['regional.auto_return_after_days'] ?? 20)),
            'reward_multiplier' => max(1, (float) ($values['regional.repatriation_reward_multiplier'] ?? 2)),
        ];
    }

    public function sync(): array
    {
        $settings = $this->settings();
        $created = 0;
        $returned = 0;
        $cleared = 0;

        $assignments = DB::table('promethee_aircraft_bases')->get()->keyBy('aircraft_id');
        $aircraft = Aircraft::query()->get(['id', 'registration', 'airport_id', 'hub_id', 'landing_time']);

        foreach ($aircraft as $plane) {
            $assignment = $assignments->get($plane->id);
            if (!$assignment) {
                DB::table('promethee_aircraft_bases')->insert([
                    'aircraft_id' => $plane->id,
                    'base_airport_id' => 'LFPO',
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $assignment = DB::table('promethee_aircraft_bases')->where('aircraft_id', $plane->id)->first();
            }

            $base = strtoupper((string) $assignment->base_airport_id);
            $current = strtoupper((string) $plane->airport_id);

            if ($current === $base) {
                if ($assignment->away_since || $assignment->repatriation_mission_id) {
                    if ($assignment->repatriation_mission_id) {
                        DB::table('promethee_missions')
                            ->where('id', $assignment->repatriation_mission_id)
                            ->update(['active' => false, 'updated_at' => now()]);
                    }
                    DB::table('promethee_aircraft_bases')->where('aircraft_id', $plane->id)->update([
                        'away_since' => null,
                        'repatriation_mission_id' => null,
                        'updated_at' => now(),
                    ]);
                    $cleared++;
                }
                continue;
            }

            $awaySince = $assignment->away_since ? \Carbon\Carbon::parse($assignment->away_since) : null;
            if (!$awaySince) {
                DB::table('promethee_aircraft_bases')->where('aircraft_id', $plane->id)->update([
                    'away_since' => $plane->landing_time ?: now(),
                    'updated_at' => now(),
                ]);
                continue;
            }

            $daysAway = $awaySince->diffInDays(now());

            if ($daysAway >= $settings['mission_after_days'] && !$assignment->repatriation_mission_id && $current !== '') {
                $missionId = DB::table('promethee_missions')->insertGetId([
                    'created_by' => null,
                    'title' => 'Rapatriement '.$plane->registration.' vers '.$base,
                    'description' => 'Mission automatique de rapatriement flotte. Appareil '.$plane->registration.' immobilisé hors base depuis '.$daysAway.' jour(s).',
                    'mission_type' => 'repatriation',
                    'dpt_airport_id' => $current,
                    'arr_airport_id' => $base,
                    'flight_id' => null,
                    'aircraft_id' => $plane->id,
                    'reward_multiplier' => $settings['reward_multiplier'],
                    'auto_generated' => true,
                    'starts_on' => today('Europe/Paris')->toDateString(),
                    'ends_on' => null,
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('promethee_aircraft_bases')->where('aircraft_id', $plane->id)->update([
                    'repatriation_mission_id' => $missionId,
                    'updated_at' => now(),
                ]);
                $created++;
                $assignment->repatriation_mission_id = $missionId;
            }

            if ($daysAway >= $settings['auto_return_after_days'] && $assignment->repatriation_mission_id) {
                $reserved = DB::table('promethee_mission_bookings')
                    ->where('mission_id', $assignment->repatriation_mission_id)
                    ->where('status', 'reserved')
                    ->exists();

                if (!$reserved) {
                    $plane->update(['airport_id' => $base, 'hub_id' => $base]);
                    DB::table('promethee_missions')->where('id', $assignment->repatriation_mission_id)->update([
                        'active' => false,
                        'updated_at' => now(),
                    ]);
                    DB::table('promethee_aircraft_bases')->where('aircraft_id', $plane->id)->update([
                        'away_since' => null,
                        'repatriation_mission_id' => null,
                        'last_auto_return_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $returned++;
                }
            }
        }

        return compact('created', 'returned', 'cleared');
    }

    public function reserveRepatriationMission(int $missionId, User $user, FinanceService $finance): void
    {
        DB::transaction(function () use ($missionId, $user, $finance) {
            $mission = DB::table('promethee_missions')
                ->where('id', $missionId)
                ->where('mission_type', 'repatriation')
                ->where('active', true)
                ->lockForUpdate()
                ->first();

            abort_unless($mission, 404);

            $alreadyReserved = DB::table('promethee_mission_bookings')
                ->where('mission_id', $mission->id)
                ->where('status', 'reserved')
                ->exists();

            abort_if($alreadyReserved, 409, 'Cette mission de rapatriement est déjà réservée.');

            $freshUser = User::with(['journal', 'airline.journal'])->findOrFail($user->id);
            $journal = $freshUser->journal ?: $freshUser->initJournal();
            $currentAirportId = $freshUser->curr_airport_id ?: $freshUser->home_airport_id;
            $jumpseatAmount = new Money(0);

            if ($currentAirportId !== $mission->dpt_airport_id) {
                $origin = Airport::findOrFail($currentAirportId);
                $destination = Airport::findOrFail($mission->dpt_airport_id);
                $distance = app(AirportService::class)->calculateDistance($origin->id, $destination->id)->toUnit('nmi', 2);
                $basePrice = (float) (DB::table('promethee_settings')->where('key', 'jumpseat.base_price')->value('value') ?: 0.13);
                $discount = (float) setting('dbasic.jumpseat_discount', 0);
                $ratio = $discount > 0 && $discount < 100 ? 1 - ($discount / 100) : 1;
                $jumpseatAmount = Money::createFromAmount(round($basePrice * $distance * $ratio, 2));

                abort_if(
                    (int) $journal->getBalance()->getAmount() < (int) $jumpseatAmount->getAmount(),
                    422,
                    'Solde insuffisant pour rejoindre l’appareil.'
                );

                $finance->debitFromJournal(
                    $journal,
                    $jumpseatAmount,
                    $freshUser,
                    'Jumpseat mission rapatriement vers '.$destination->icao,
                    'jumpseat',
                    'repatriation'
                );

                if ($freshUser->airline?->journal) {
                    $finance->creditToJournal(
                        $freshUser->airline->journal,
                        $jumpseatAmount,
                        $freshUser,
                        'Jumpseat mission rapatriement de '.$freshUser->name,
                        'jumpseat',
                        'repatriation'
                    );
                }

                $freshUser->update(['curr_airport_id' => $destination->id]);
            }

            DB::table('promethee_mission_bookings')->updateOrInsert(
                [
                    'mission_id' => $mission->id,
                    'user_id' => $freshUser->id,
                ],
                [
                    'status' => 'reserved',
                    'jumpseat_amount' => (int) $jumpseatAmount->getAmount(),
                    'bonus_amount' => 0,
                    'bonus_paid_at' => null,
                    'reserved_at' => now(),
                    'completed_at' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });
    }
}
