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

        $blocked = $this->blockedAircraft();
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
                /** @var Aircraft|null $plane */
                $plane = $planes->get($id);
                if (!$plane) {
                    throw ValidationException::withMessages([
                        'aircraft_ids' => 'Un appareil sélectionné n’existe plus.',
                    ]);
                }

                $blockingReason = $this->blockingReason($plane);
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

    private function blockedAircraft()
    {
        return Aircraft::query()
            ->get(['id', 'status', 'state'])
            ->mapWithKeys(fn (Aircraft $plane) => [(int) $plane->id => $this->blockingReason($plane)])
            ->filter(fn ($reason) => $reason !== null);
    }

    private function blockingReason(Aircraft $plane): ?string
    {
        if ($plane->status !== AircraftStatus::ACTIVE) {
            return 'statut appareil non actif';
        }

        if ((int) $plane->state !== AircraftState::PARKED) {
            return (int) $plane->state === AircraftState::IN_AIR
                ? 'appareil actuellement en vol'
                : 'appareil actuellement utilisé';
        }

        if (DB::table('bids')->where('aircraft_id', $plane->id)->exists()) {
            return 'réservation active';
        }

        if (DB::table('pireps')
            ->where('aircraft_id', $plane->id)
            ->whereIn('state', self::BUSY_PIREP_STATES)
            ->exists()) {
            return 'PIREP actif ou en attente';
        }

        if (Schema::hasTable('promethee_missions')
            && DB::table('promethee_missions')
                ->where('aircraft_id', $plane->id)
                ->where('active', true)
                ->exists()) {
            return 'mission active';
        }

        if (Schema::hasTable('promethee_airframe_maintenance')
            && DB::table('promethee_airframe_maintenance')
                ->where('aircraft_id', $plane->id)
                ->whereNotNull('active_check')
                ->exists()) {
            return 'maintenance cellule en cours';
        }

        if (Schema::hasTable('disposable_maintenance')
            && DB::table('disposable_maintenance')
                ->where('aircraft_id', $plane->id)
                ->whereNotNull('act_note')
                ->exists()) {
            return 'maintenance active';
        }

        return null;
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
