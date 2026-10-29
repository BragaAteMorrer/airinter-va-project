<?php

namespace Modules\Promethee\Services;

use App\Models\Aircraft;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\FlightType;
use App\Models\Flight;
use App\Models\Pirep;
use App\Models\Enums\PirepState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * A one-flight, non-commercial recovery permit. The aircraft is NEVER marked
 * ACTIVE during ferry preparation or transit. Only a private positioning
 * flight and an assigned technical base can bypass the normal maintenance hold.
 */
class AirframeEmergencyFerryService
{
    public function technicalBases(): array
    {
        if (!Schema::hasTable('promethee_operational_bases')
            || !Schema::hasColumn('promethee_operational_bases', 'check_b')
            || !Schema::hasColumn('promethee_operational_bases', 'check_c')) return [];

        return DB::table('promethee_operational_bases')
            ->where('active', true)->where('check_b', true)->where('check_c', true)
            ->orderBy('airport_id')->pluck('airport_id')->all();
    }

    public function state(int $aircraftId): ?object
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')
            || !Schema::hasColumn('promethee_airframe_maintenance', 'ferry_status')) return null;

        return DB::table('promethee_airframe_maintenance')->where('aircraft_id', $aircraftId)->first();
    }

    public function privateFlightState(string $flightId): ?object
    {
        if ($flightId === '' || !Schema::hasTable('promethee_airframe_maintenance')
            || !Schema::hasColumn('promethee_airframe_maintenance', 'ferry_flight_id')) return null;

        return DB::table('promethee_airframe_maintenance')->where('ferry_flight_id', $flightId)->first();
    }

    public function isAuthorized(int $aircraftId, string $flightId): bool
    {
        $state = $this->state($aircraftId);
        if (!$state || $state->ferry_status !== 'authorized'
            || (string) $state->ferry_flight_id !== $flightId
            || !$state->ferry_ready_at || now()->lt(\Carbon\Carbon::parse($state->ferry_ready_at))) return false;

        $aircraft = Aircraft::find($aircraftId);
        return $aircraft && $aircraft->status === AircraftStatus::MAINTENANCE
            && $aircraft->state === AircraftState::PARKED
            && strtoupper((string) $aircraft->airport_id) === strtoupper((string) $state->ferry_origin_airport_id);
    }

    public function authorize(
        int $aircraftId,
        string $destination,
        int $repairHours,
        ?int $userId,
        string $notes
    ): array {
        if (!$this->state($aircraftId) || !Schema::hasTable('promethee_missions')) {
            throw new RuntimeException('Migration de convoyage technique non appliquée.');
        }

        $destination = strtoupper(trim($destination));
        $notes = trim($notes);
        if ($notes === '' || mb_strlen($notes) < 12) {
            throw new RuntimeException('Un compte-rendu d’inspection explicite est obligatoire (12 caractères minimum).');
        }
        if ($repairHours < 1 || $repairHours > 48) {
            throw new RuntimeException('La préparation d’urgence doit durer entre 1 et 48 heures.');
        }
        if (!in_array($destination, $this->technicalBases(), true)) {
            throw new RuntimeException('La destination doit être une base technique active avec checks B et C.');
        }

        return DB::transaction(function () use ($aircraftId, $destination, $repairHours, $userId, $notes) {
            $aircraft = Aircraft::with('subfleet')->lockForUpdate()->findOrFail($aircraftId);
            $state = DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $aircraftId)->lockForUpdate()->first();

            if ($state->ferry_status ?? null) {
                throw new RuntimeException('Un convoyage technique est déjà ouvert pour cet appareil.');
            }
            if ($aircraft->status !== AircraftStatus::MAINTENANCE
                || $aircraft->state !== AircraftState::PARKED) {
                throw new RuntimeException('L’appareil doit être immobilisé et au parking.');
            }
            $legacy = Schema::hasTable('disposable_maintenance')
                ? DB::table('disposable_maintenance')->where('aircraft_id', $aircraftId)->first()
                : null;
            $legacyIncident = str_contains(strtolower((string) ($legacy->act_note ?? '')), 'hard landing');
            if (!$state->safety_hold_at && !$legacyIncident) {
                throw new RuntimeException('Aucun incident FDM ou hard landing historique ne justifie ce convoyage.');
            }
            if ($state->active_check
                || ($legacy?->act_note && !$legacyIncident)) {
                throw new RuntimeException('Un check technique normal est en cours : convoyage d’urgence interdit.');
            }
            if (DB::table('bids')->where('aircraft_id', $aircraftId)->exists()) {
                throw new RuntimeException('Annulez les réservations existantes de cet appareil avant le convoyage.');
            }

            // Legacy Hard Landing Checks can otherwise expire while a ferry is
            // authorized, leaving no FDM hold to clear after technical repair.
            // Preserve a genuine hold until the final inspection or a documented
            // cancellation followed by an inspection on the original site.
            if (!$state->safety_hold_at && $legacyIncident) {
                DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                    'safety_hold_reason' => 'Hard Landing Check historique : inspection technique obligatoire',
                    'safety_hold_at' => now(),
                    'safety_hold_pirep_id' => null,
                    'safety_hold_previous_status' => AircraftStatus::ACTIVE,
                    'updated_at' => now(),
                ]);
            }

            $origin = strtoupper((string) $aircraft->airport_id);
            if ($origin === '' || $origin === $destination) {
                throw new RuntimeException('La base technique doit être différente de l’escale actuelle.');
            }
            if (in_array($origin, $this->technicalBases(), true)) {
                throw new RuntimeException('Cet aéroport dispose déjà des capacités nécessaires : effectuez la réparation sur place.');
            }
            $airlineId = (int) ($aircraft->subfleet?->airline_id ?? 0);
            if (!$airlineId) throw new RuntimeException('Compagnie de cet appareil introuvable.');

            $flight = Flight::create([
                'airline_id' => $airlineId,
                'flight_number' => 900000 + $aircraftId,
                'route_code' => 'TECH',
                'dpt_airport_id' => $origin,
                'arr_airport_id' => $destination,
                'flight_type' => FlightType::POSITIONING,
                'notes' => 'CONVOYAGE TECHNIQUE PRIVÉ — sans passagers ni fret, '.($aircraft->registration ?: $aircraftId),
                'active' => true,
                'visible' => false,
            ]);
            $flight->subfleets()->attach($aircraft->subfleet_id);
            $ready = now()->addHours($repairHours);

            $missionId = DB::table('promethee_missions')->insertGetId([
                'created_by' => $userId,
                'title' => 'CONVOYAGE TECHNIQUE · '.$aircraft->registration.' · '.$origin.' → '.$destination,
                'description' => 'Inspection provisoire autorisée. Vol unique sans passagers ni fret. Réparation définitive obligatoire à destination. '.$notes,
                'mission_type' => 'repatriation',
                'dpt_airport_id' => $origin,
                'arr_airport_id' => $destination,
                'flight_id' => $flight->id,
                'aircraft_id' => $aircraftId,
                'reward_multiplier' => 1,
                'auto_generated' => false,
                'starts_on' => today('Europe/Paris')->toDateString(),
                'ends_on' => null,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'ferry_status' => 'authorized',
                'ferry_origin_airport_id' => $origin,
                'ferry_destination_airport_id' => $destination,
                'ferry_flight_id' => $flight->id,
                'ferry_mission_id' => $missionId,
                'ferry_authorized_at' => now(),
                'ferry_ready_at' => $ready,
                'ferry_arrived_at' => null,
                'ferry_notes' => $notes,
                'updated_at' => now(),
            ]);
            $this->logEvent($aircraftId, $origin, 'ferry_authorized',
                'Dépannage d’urgence : '.$repairHours.' h. Convoyage unique vers '.$destination.'. '.$notes, $userId);
            return ['flight_id' => $flight->id, 'mission_id' => $missionId, 'ready_at' => $ready];
        }, 3);
    }

    /**
     * Cancel a provisional permit BEFORE any bid or PIREP exists.
     * Never release the safety hold, the aircraft, or a completed ferry.
     * The flight and the mission remain in the database for audit history.
     */
    public function cancel(int $aircraftId, ?int $userId, string $notes): void
    {
        $notes = trim($notes);
        if (mb_strlen($notes) < 12) {
            throw new RuntimeException('Le motif d’annulation doit contenir au moins 12 caractères.');
        }

        DB::transaction(function () use ($aircraftId, $userId, $notes) {
            $state = DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $aircraftId)->lockForUpdate()->first();
            if (!$state || ($state->ferry_status ?? null) !== 'authorized') {
                throw new RuntimeException('Seul un convoyage autorisé et non terminé peut être annulé.');
            }
            $aircraft = Aircraft::query()->lockForUpdate()->findOrFail($aircraftId);
            if ($aircraft->status !== AircraftStatus::MAINTENANCE
                || $aircraft->state !== AircraftState::PARKED
                || strtoupper((string) $aircraft->airport_id) !== strtoupper((string) $state->ferry_origin_airport_id)) {
                throw new RuntimeException('La position ou l’état de l’appareil empêche l’annulation de ce convoyage.');
            }

            $flightId = (string) $state->ferry_flight_id;
            $missionId = (int) $state->ferry_mission_id;
            if (DB::table('bids')->where('flight_id', $flightId)->exists()
                || DB::table('promethee_mission_bookings')->where('mission_id', $missionId)
                    ->where('status', 'reserved')->exists()) {
                throw new RuntimeException('La mission est réservée : le pilote doit d’abord abandonner sa réservation.');
            }
            if (DB::table('pireps')->where('flight_id', $flightId)->exists()) {
                throw new RuntimeException('Un PIREP existe déjà pour ce vol : annulation manuelle interdite, inspection nécessaire.');
            }

            DB::table('promethee_missions')->where('id', $missionId)->update([
                'active' => false, 'updated_at' => now(),
            ]);
            DB::table('flights')->where('id', $flightId)->update([
                'active' => false, 'updated_at' => now(),
            ]);
            // The safety hold is intentionally retained, even for a legacy
            // incident. A new permit may be issued after a fresh inspection.
            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'ferry_status' => null,
                'ferry_origin_airport_id' => null,
                'ferry_destination_airport_id' => null,
                'ferry_flight_id' => null,
                'ferry_mission_id' => null,
                'ferry_authorized_at' => null,
                'ferry_ready_at' => null,
                'ferry_arrived_at' => null,
                'ferry_notes' => null,
                'updated_at' => now(),
            ]);
            $this->logEvent($aircraftId, (string) $aircraft->airport_id,
                'ferry_cancelled',
                'Convoyage technique annulé (mission '.$missionId.', vol '.$flightId.'). '.$notes,
                $userId);
        }, 3);
    }

    public function missionIsProtected(int $missionId): bool
    {
        if (!Schema::hasTable('promethee_airframe_maintenance')
            || !Schema::hasColumn('promethee_airframe_maintenance', 'ferry_mission_id')) {
            return false;
        }

        return DB::table('promethee_airframe_maintenance')
            ->where('ferry_mission_id', $missionId)
            ->whereIn('ferry_status', ['authorized', 'arrived'])->exists();
    }

    public function isFerryPirep(Pirep $pirep): bool
    {
        $state = $pirep->aircraft_id ? $this->state((int) $pirep->aircraft_id) : null;
        return $state && $state->ferry_status === 'authorized'
            && (string) $state->ferry_flight_id === (string) $pirep->flight_id;
    }

    public function complete(Pirep $pirep): bool
    {
        if (!$this->isFerryPirep($pirep)) return false;

        return DB::transaction(function () use ($pirep) {
            $state = DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $pirep->aircraft_id)->lockForUpdate()->first();
            if (!$state || $state->ferry_status !== 'authorized'
                || (string) $state->ferry_flight_id !== (string) $pirep->flight_id
                || (int) $pirep->state !== PirepState::ACCEPTED
                || strtoupper((string) $pirep->dpt_airport_id) !== strtoupper((string) $state->ferry_origin_airport_id)
                || strtoupper((string) $pirep->arr_airport_id) !== strtoupper((string) $state->ferry_destination_airport_id)) {
                throw new RuntimeException('PIREP de convoyage incohérent : autorisation conservée et avion immobilisé.');
            }

            // A scheduled route alone does not prove that a designated pilot
            // actually completed the technical movement. Require the active
            // booking and an explicitly accepted PIREP for this very flight.
            $reserved = DB::table('promethee_mission_bookings')
                ->where('mission_id', $state->ferry_mission_id)
                ->where('user_id', $pirep->user_id)
                ->where('status', 'reserved')
                ->lockForUpdate()->exists();
            if (!$reserved) {
                throw new RuntimeException('Convoyage non validé : réservation pilote introuvable ou déjà clôturée.');
            }

            $aircraft = Aircraft::query()->lockForUpdate()->findOrFail($pirep->aircraft_id);
            $aircraft->update([
                'status' => AircraftStatus::MAINTENANCE,
                'airport_id' => $state->ferry_destination_airport_id,
                'state' => AircraftState::PARKED,
            ]);
            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'ferry_status' => 'arrived',
                'ferry_arrived_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table('promethee_missions')->where('id', $state->ferry_mission_id)
                ->update(['active' => false, 'updated_at' => now()]);
            DB::table('flights')->where('id', $state->ferry_flight_id)
                ->update(['active' => false, 'updated_at' => now()]);
            DB::table('promethee_mission_bookings')
                ->where('mission_id', $state->ferry_mission_id)->where('user_id', $pirep->user_id)
                ->where('status', 'reserved')->update([
                    'status' => 'completed', 'completed_at' => now(), 'updated_at' => now(),
                ]);
            $this->logEvent((int) $pirep->aircraft_id, $state->ferry_destination_airport_id,
                'ferry_arrived', 'PIREP '.$pirep->id.' validé. Réparation définitive encore obligatoire.', null);
            return true;
        }, 3);
    }

    /**
     * Final release requires the aircraft to have actually reached its
     * technical destination and its remaining local checks to be complete.
     * An emergency ferry never clears an incident just because it arrived.
     */
    public function finalize(int $aircraftId, ?int $userId, string $notes): void
    {
        $notes = trim($notes);
        if (mb_strlen($notes) < 12) {
            throw new RuntimeException('La réparation définitive exige un compte-rendu (12 caractères minimum).');
        }

        DB::transaction(function () use ($aircraftId, $userId, $notes) {
            $state = DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $aircraftId)->lockForUpdate()->first();
            $aircraft = Aircraft::query()->lockForUpdate()->findOrFail($aircraftId);
            if (!$state || ($state->ferry_status ?? null) !== 'arrived'
                || strtoupper((string) $aircraft->airport_id) !== strtoupper((string) $state->ferry_destination_airport_id)) {
                throw new RuntimeException('La réparation définitive exige le convoyage terminé sur la base désignée.');
            }

            $legacyActive = Schema::hasTable('disposable_maintenance')
                && DB::table('disposable_maintenance')->where('aircraft_id', $aircraftId)
                    ->whereNotNull('act_note')->exists();
            if ($state->active_check || $legacyActive) {
                throw new RuntimeException('Attendez la fin des checks cellule en cours avant de remettre cet avion en service.');
            }

            // Acceptance can later be revoked (PIREP rejected/deleted). A
            // matching *current* accepted flight report is mandatory even if
            // the historical ferry status says it reached its technical base.
            $accepted = Pirep::query()
                ->where('aircraft_id', $aircraftId)
                ->where('flight_id', $state->ferry_flight_id)
                ->where('dpt_airport_id', $state->ferry_origin_airport_id)
                ->where('arr_airport_id', $state->ferry_destination_airport_id)
                ->where('state', PirepState::ACCEPTED)
                ->exists();
            if (!$accepted) {
                throw new RuntimeException('Le PIREP de convoyage est absent, rejeté ou annulé : remise en service interdite.');
            }

            DB::table('promethee_airframe_maintenance')->where('id', $state->id)->update([
                'ferry_status' => null,
                'ferry_origin_airport_id' => null,
                'ferry_destination_airport_id' => null,
                'ferry_flight_id' => null,
                'ferry_mission_id' => null,
                'ferry_authorized_at' => null,
                'ferry_ready_at' => null,
                'ferry_arrived_at' => null,
                'ferry_notes' => null,
                'updated_at' => now(),
            ]);

            if ($state->safety_hold_at) {
                if (!app(AirframeMaintenanceService::class)->releaseSafetyHold($aircraftId, $userId, $notes)) {
                    throw new RuntimeException('Échec de la levée de l’immobilisation FDM.');
                }
            } else {
                $aircraft->update(['status' => AircraftStatus::ACTIVE]);
            }
            $this->logEvent($aircraftId, (string) $aircraft->airport_id, 'ferry_released',
                'Inspection et réparation définitives terminées. '.$notes, $userId);
        }, 3);
    }

    private function logEvent(int $aircraftId, string $airport, string $type, string $notes, ?int $userId): void
    {
        if (!Schema::hasTable('promethee_airframe_maintenance_events')) return;

        DB::table('promethee_airframe_maintenance_events')->insert([
            'aircraft_id' => $aircraftId,
            'check_type' => 'f',
            'event_type' => $type,
            'airport_id' => $airport,
            'minutes_before' => null,
            'cycles_before' => null,
            'created_by' => $userId,
            'notes' => $notes,
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
