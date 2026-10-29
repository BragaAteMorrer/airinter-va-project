<?php

namespace Modules\Promethee\Listeners;

use App\Events\PirepAccepted;
use App\Models\Aircraft;
use App\Models\User;
use App\Services\Finance\PirepFinanceService;
use App\Services\FinanceService;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Throwable;

class RegionalOperationsEventListener
{
    public function onPirepAccepted(PirepAccepted $event): void
    {
        if (!Schema::hasTable('promethee_mission_bookings') || !Schema::hasTable('promethee_missions')) {
            return;
        }

        try {
            $pirep = $event->pirep->loadMissing(['user.journal', 'airline.journal', 'aircraft.subfleet', 'flight']);
            $ferry = app(\Modules\Promethee\Services\AirframeEmergencyFerryService::class);
            if ($ferry->isFerryPirep($pirep)) {
                $ferry->complete($pirep);
                return; // No repatriation bonus or silent hub reassignment for a technical ferry.
            }

            DB::transaction(function () use ($pirep) {
                $booking = DB::table('promethee_mission_bookings as booking')
                    ->join('promethee_missions as mission', 'mission.id', '=', 'booking.mission_id')
                    ->where('booking.user_id', $pirep->user_id)
                    ->where('booking.status', 'reserved')
                    ->whereNull('booking.bonus_paid_at')
                    ->where('mission.mission_type', 'repatriation')
                    ->where('mission.aircraft_id', $pirep->aircraft_id)
                    ->where('mission.dpt_airport_id', $pirep->dpt_airport_id)
                    ->where('mission.arr_airport_id', $pirep->arr_airport_id)
                    ->where(function ($query) use ($pirep) {
                        $query->whereNull('mission.flight_id')
                            ->orWhere('mission.flight_id', $pirep->flight_id);
                    })
                    ->select([
                        'booking.id as booking_id',
                        'booking.mission_id',
                        'mission.reward_multiplier',
                        'mission.arr_airport_id',
                    ])
                    ->lockForUpdate()
                    ->first();

                if (!$booking) {
                    return;
                }

                $multiplier = max(1, (float) $booking->reward_multiplier);
                $bonusFactor = $multiplier - 1;

                $user = User::with('journal')->findOrFail($pirep->user_id);
                $airline = $pirep->airline;
                $userJournal = $user->journal ?: $user->initJournal();
                $airlineJournal = $airline->journal ?: $airline->initJournal(setting('units.currency', 'USD'));

                $normalPay = app(PirepFinanceService::class)->getPilotPay($pirep);
                $bonusAmount = (int) round((int) $normalPay->getAmount() * $bonusFactor);

                if ($bonusAmount > 0) {
                    $bonus = new Money($bonusAmount);
                    $memo = 'Prime mission rapatriement ×'.number_format($multiplier, 1, '.', '').' · PIREP '.$pirep->id;

                    app(FinanceService::class)->debitFromJournal(
                        $airlineJournal,
                        $bonus,
                        $user,
                        $memo,
                        'Mission Bonus',
                        'repatriation_bonus',
                        $pirep->submitted_at
                    );

                    app(FinanceService::class)->creditToJournal(
                        $userJournal,
                        $bonus,
                        $user,
                        $memo,
                        'Mission Bonus',
                        'repatriation_bonus',
                        $pirep->submitted_at
                    );
                }

                DB::table('promethee_mission_bookings')->where('id', $booking->booking_id)->update([
                    'status' => 'completed',
                    'bonus_amount' => $bonusAmount,
                    'bonus_paid_at' => now(),
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('promethee_missions')->where('id', $booking->mission_id)->update([
                    'active' => false,
                    'updated_at' => now(),
                ]);

                DB::table('promethee_aircraft_bases')->where('aircraft_id', $pirep->aircraft_id)->update([
                    'away_since' => null,
                    'repatriation_mission_id' => null,
                    'updated_at' => now(),
                ]);

                Aircraft::where('id', $pirep->aircraft_id)->update([
                    'airport_id' => $booking->arr_airport_id,
                    'hub_id' => $booking->arr_airport_id,
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('Repatriation bonus payment failed', [
                'pirep_id' => $event->pirep->id,
                'error' => $exception->getMessage(),
            ]);
            report($exception);
        }
    }
}
