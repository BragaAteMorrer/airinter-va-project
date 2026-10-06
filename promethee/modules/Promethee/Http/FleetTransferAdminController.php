<?php

namespace Modules\Promethee\Http;

use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\Enums\PirepState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class FleetTransferAdminController extends PrometheeWebController
{
    private const BUSY_PIREP_STATES = [
        PirepState::IN_PROGRESS,
        PirepState::PAUSED,
        PirepState::PENDING,
        PirepState::DRAFT,
    ];

    public function index()
    {
        $aircraft = Aircraft::query()
            ->with(['subfleet.airline', 'airport', 'home'])
            ->orderBy('registration')
            ->get();

        $blocked = $this->blockedAircraft($aircraft);
        $rows = $aircraft->map(function (Aircraft $plane) use ($blocked) {
            $reason = $blocked->get((int) $plane->id);

            return [
                'aircraft' => $plane,
                'eligible' => $reason === null,
                'reason' => $reason,
                'state_label' => AircraftState::$labels[$plane->state] ?? 'Unknown',
                'status_label' => AircraftStatus::$labels[$plane->status] ?? (string) $plane->status,
            ];
        });

        $airports = Airport::query()
            ->orderByDesc('hub')
            ->orderBy('icao')
            ->get(['id', 'icao', 'iata', 'name', 'location', 'hub']);

        $operationalBases = Schema::hasTable('promethee_operational_bases')
            ? DB::table('promethee_operational_bases')
                ->where('active', true)
                ->pluck('airport_id')
                ->map(fn ($id) => strtoupper((string) $id))
                ->flip()
            : collect();

        $history = collect();
        if (Schema::hasTable('promethee_audit_logs')) {
            $history = DB::table('promethee_audit_logs')
                ->where('action', 'aircraft.transfer')
                ->latest('created_at')
                ->limit(30)
                ->get()
                ->map(function ($row) {
                    $context = json_decode((string) ($row->context ?? ''), true);
                    $row->transfer = is_array($context) ? $context : [];
                    return $row;
                });
        }

        return $this->page('admin-fleet-transfers', [
            'rows' => $rows,
            'airports' => $airports,
            'operationalBases' => $operationalBases,
            'history' => $history,
            'eligibleCount' => $rows->where('eligible', true)->count(),
            'blockedCount' => $rows->where('eligible', false)->count(),
        ]);
    }

    public function transfer(Request $request)
    {
        $data = $request->validate([
            'aircraft_ids' => 'required|array|min:1|max:100',
            'aircraft_ids.*' => 'required|integer|distinct|exists:aircraft,id',
            'destination_airport_id' => 'required|string|max:8|exists:airports,id',
            'change_base' => 'nullable|boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        $destination = strtoupper((string) $data['destination_airport_id']);
        $changeBase = $request->boolean('change_base');
        $reason = trim((string) ($data['reason'] ?? ''));

        if ($changeBase) {
            if (!Schema::hasTable('promethee_operational_bases')
                || !DB::table('promethee_operational_bases')
                    ->where('airport_id', $destination)
                    ->where('active', true)
                    ->exists()) {
                throw ValidationException::withMessages([
                    'destination_airport_id' => 'Pour changer la base de rattachement, la destination doit être une base opérationnelle active de Prométhée.',
                ]);
            }
        }

        $ids = collect($data['aircraft_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $moved = 0;
        $unchanged = 0;

        DB::transaction(function () use ($ids, $destination, $changeBase, $reason, $request, &$moved, &$unchanged) {
            $planes = Aircraft::query()
                ->whereIn('id', $ids)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($ids as $id) {
                if (!$planes->has($id)) {
                    throw ValidationException::withMessages([
                        'aircraft_ids' => 'Un appareil sélectionné n’existe plus.',
                    ]);
                }
            }

            $blocked = $this->blockedAircraft($planes->values());
            foreach ($ids as $id) {
                /** @var Aircraft $plane */
                $plane = $planes->get($id);
                $blockingReason = $blocked->get((int) $plane->id);
                if ($blockingReason !== null) {
                    throw ValidationException::withMessages([
                        'aircraft_ids' => $plane->registration.' ne peut plus être transféré : '.$blockingReason.'. Actualisez la page puis réessayez.',
                    ]);
                }
            }

            foreach ($ids as $id) {
                /** @var Aircraft $plane */
                $plane = $planes->get($id);
                $fromAirport = strtoupper((string) $plane->airport_id);
                $fromBase = strtoupper((string) $plane->hub_id);

                if ($fromAirport === $destination && (!$changeBase || $fromBase === $destination)) {
                    $unchanged++;
                    continue;
                }

                $updates = ['airport_id' => $destination];
                if ($changeBase) {
                    $updates['hub_id'] = $destination;
                }
                $plane->update($updates);

                if ($changeBase) {
                    $this->updateBaseAssignment($plane, $destination);
                } else {
                    $this->syncAwayState($plane, $destination);
                }

                if (Schema::hasTable('promethee_audit_logs')) {
                    DB::table('promethee_audit_logs')->insert([
                        'actor_id' => $request->user()?->id,
                        'action' => 'aircraft.transfer',
                        'subject_type' => 'aircraft',
                        'subject_id' => (string) $plane->id,
                        'context' => json_encode([
                            'registration' => $plane->registration,
                            'from_airport' => $fromAirport ?: null,
                            'to_airport' => $destination,
                            'change_base' => $changeBase,
                            'from_base' => $fromBase ?: null,
                            'to_base' => $changeBase ? $destination : ($fromBase ?: null),
                            'reason' => $reason !== '' ? $reason : null,
                        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $moved++;
            }
        });

        $message = $moved.' appareil(s) transféré(s) vers '.$destination.'.';
        if ($changeBase && $moved > 0) {
            $message .= ' Base de rattachement mise à jour.';
        }
        if ($unchanged > 0) {
            $message .= ' '.$unchanged.' appareil(s) déjà à destination, sans modification.';
        }

        return back()->with('success', $message);
    }

    private function blockedAircraft($planes)
    {
        $planes = collect($planes);
        $ids = $planes->pluck('id')->map(fn ($id) => (int) $id)->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        $bidAircraft = DB::table('bids')
            ->whereIn('aircraft_id', $ids)
            ->pluck('aircraft_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $pirepAircraft = DB::table('pireps')
            ->whereIn('aircraft_id', $ids)
            ->whereIn('state', self::BUSY_PIREP_STATES)
            ->pluck('aircraft_id')
            ->map(fn ($id) => (int) $id)
            ->flip();

        $missionAircraft = collect();
        if (Schema::hasTable('promethee_missions')) {
            $activeMissions = DB::table('promethee_missions')
                ->whereIn('aircraft_id', $ids)
                ->where('active', true)
                ->get(['id', 'aircraft_id', 'mission_type']);

            $reservedMissionIds = Schema::hasTable('promethee_mission_bookings') && $activeMissions->isNotEmpty()
                ? DB::table('promethee_mission_bookings')
                    ->whereIn('mission_id', $activeMissions->pluck('id'))
                    ->where('status', 'reserved')
                    ->pluck('mission_id')
                    ->map(fn ($id) => (int) $id)
                    ->flip()
                : collect();

            // An unbooked auto-repatriation is administrative cleanup, not a
            // hard lock: a manual transfer may supersede it and will close it.
            // Any other active mission, or a repatriation already reserved by
            // a pilot, protects the airframe from being moved under their feet.
            $missionAircraft = $activeMissions
                ->filter(fn ($mission) => $mission->mission_type !== 'repatriation'
                    || $reservedMissionIds->has((int) $mission->id))
                ->pluck('aircraft_id')
                ->map(fn ($id) => (int) $id)
                ->flip();
        }

        $airframeMaintenanceAircraft = Schema::hasTable('promethee_airframe_maintenance')
            ? DB::table('promethee_airframe_maintenance')
                ->whereIn('aircraft_id', $ids)
                ->whereNotNull('active_check')
                ->pluck('aircraft_id')
                ->map(fn ($id) => (int) $id)
                ->flip()
            : collect();

        $legacyMaintenanceAircraft = Schema::hasTable('disposable_maintenance')
            ? DB::table('disposable_maintenance')
                ->whereIn('aircraft_id', $ids)
                ->whereNotNull('act_note')
                ->pluck('aircraft_id')
                ->map(fn ($id) => (int) $id)
                ->flip()
            : collect();

        return $planes->mapWithKeys(function (Aircraft $plane) use (
            $bidAircraft,
            $pirepAircraft,
            $missionAircraft,
            $airframeMaintenanceAircraft,
            $legacyMaintenanceAircraft
        ) {
            $reason = null;

            if ($plane->status !== AircraftStatus::ACTIVE) {
                $reason = 'statut appareil non actif';
            } elseif ((int) $plane->state !== AircraftState::PARKED) {
                $reason = (int) $plane->state === AircraftState::IN_AIR
                    ? 'appareil actuellement en vol'
                    : 'appareil actuellement utilisé';
            } elseif ($bidAircraft->has((int) $plane->id)) {
                $reason = 'réservation active';
            } elseif ($pirepAircraft->has((int) $plane->id)) {
                $reason = 'PIREP actif ou en attente';
            } elseif ($missionAircraft->has((int) $plane->id)) {
                $reason = 'mission active';
            } elseif ($airframeMaintenanceAircraft->has((int) $plane->id)) {
                $reason = 'maintenance cellule en cours';
            } elseif ($legacyMaintenanceAircraft->has((int) $plane->id)) {
                $reason = 'maintenance active';
            }

            return [(int) $plane->id => $reason];
        })->filter(fn ($reason) => $reason !== null);
    }

    private function syncAwayState(Aircraft $plane, string $destination): void
    {
        if (!Schema::hasTable('promethee_aircraft_bases')) {
            return;
        }

        $assignment = DB::table('promethee_aircraft_bases')
            ->where('aircraft_id', $plane->id)
            ->lockForUpdate()
            ->first();

        if (!$assignment) {
            $base = strtoupper((string) $plane->hub_id);
            if ($base === '') {
                return;
            }

            DB::table('promethee_aircraft_bases')->insert([
                'aircraft_id' => $plane->id,
                'base_airport_id' => $base,
                'assigned_at' => now(),
                'away_since' => $destination === $base ? null : now(),
                'repatriation_mission_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        if ($assignment->repatriation_mission_id && Schema::hasTable('promethee_missions')) {
            DB::table('promethee_missions')
                ->where('id', $assignment->repatriation_mission_id)
                ->update(['active' => false, 'updated_at' => now()]);
        }

        $base = strtoupper((string) $assignment->base_airport_id);
        DB::table('promethee_aircraft_bases')
            ->where('aircraft_id', $plane->id)
            ->update([
                'away_since' => $destination === $base ? null : now(),
                'repatriation_mission_id' => null,
                'updated_at' => now(),
            ]);
    }

    private function updateBaseAssignment(Aircraft $plane, string $destination): void
    {
        if (!Schema::hasTable('promethee_aircraft_bases')) {
            return;
        }

        $assignment = DB::table('promethee_aircraft_bases')
            ->where('aircraft_id', $plane->id)
            ->lockForUpdate()
            ->first();

        if ($assignment?->repatriation_mission_id && Schema::hasTable('promethee_missions')) {
            DB::table('promethee_missions')
                ->where('id', $assignment->repatriation_mission_id)
                ->update(['active' => false, 'updated_at' => now()]);
        }

        DB::table('promethee_aircraft_bases')->updateOrInsert(
            ['aircraft_id' => $plane->id],
            [
                'base_airport_id' => $destination,
                'assigned_at' => now(),
                'away_since' => null,
                'repatriation_mission_id' => null,
                'updated_at' => now(),
                'created_at' => $assignment?->created_at ?? now(),
            ]
        );
    }
}
