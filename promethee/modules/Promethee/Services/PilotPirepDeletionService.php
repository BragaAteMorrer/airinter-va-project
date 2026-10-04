<?php

namespace Modules\Promethee\Services;

use App\Models\Enums\PirepState;
use App\Models\Pirep;
use App\Models\User;
use App\Services\PirepService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Self-service PIREP abandonment for pilots.
 *
 * This deliberately excludes accepted/rejected/cancelled reports: once a
 * report has entered a terminal accounting state, only the administrator
 * emergency workflow may remove it.
 */
class PilotPirepDeletionService
{
    private const SELF_DELETABLE_STATES = [
        PirepState::IN_PROGRESS,
        PirepState::PENDING,
        PirepState::DRAFT,
        PirepState::PAUSED,
    ];

    public function __construct(private readonly PirepService $pireps) {}

    public function canDelete(Pirep $pirep, User $user): bool
    {
        return (string) $pirep->user_id === (string) $user->id
            && in_array((int) $pirep->state, self::SELF_DELETABLE_STATES, true);
    }

    public function deleteOwn(Pirep $pirep, User $user, string $origin = 'promethee'): array
    {
        abort_unless(
            (string) $pirep->user_id === (string) $user->id,
            403,
            'Vous ne pouvez supprimer que vos propres PIREP.'
        );

        abort_unless(
            in_array((int) $pirep->state, self::SELF_DELETABLE_STATES, true),
            409,
            'Ce PIREP est déjà validé, rejeté ou annulé et ne peut plus être supprimé par le pilote. Contactez un administrateur si une remise à zéro est nécessaire.'
        );

        $pirep->loadMissing(['user', 'aircraft', 'flight']);
        $operationId = $this->operationId($pirep);
        $snapshot = [
            'id' => (string) $pirep->id,
            'user_id' => (string) $pirep->user_id,
            'pilot_id' => $pirep->user?->pilot_id,
            'flight_id' => $pirep->flight_id,
            'ident' => $pirep->ident,
            'aircraft_id' => $pirep->aircraft_id,
            'aircraft_registration' => $pirep->aircraft?->registration,
            'state' => (int) $pirep->state,
            'status' => (string) $pirep->status,
            'source_name' => (string) $pirep->source_name,
            'operation_id' => $operationId,
            'origin' => $origin,
            'submitted_at' => optional($pirep->submitted_at)?->toIso8601String(),
        ];

        DB::transaction(function () use ($pirep, $user, $snapshot, $operationId) {
            // Promethee-owned rows are not all covered by phpVMS' core
            // PirepService, so remove them explicitly before the PIREP.
            if (Schema::hasTable('promethee_telemetry')) {
                DB::table('promethee_telemetry')->where('pirep_id', $pirep->id)->delete();
            }

            if (Schema::hasTable('promethee_pirep_aircraft_profiles')) {
                DB::table('promethee_pirep_aircraft_profiles')->where('pirep_id', $pirep->id)->delete();
            }

            if ($operationId && Schema::hasTable('promethee_datalink_stores')) {
                DB::table('promethee_datalink_stores')->where('operation_id', $operationId)->delete();
            }

            $this->pireps->delete($pirep);

            if (Schema::hasTable('promethee_audit_logs')) {
                DB::table('promethee_audit_logs')->insert([
                    'actor_id' => $user->id,
                    'action' => 'pirep.self_delete',
                    'subject_type' => 'pirep',
                    'subject_id' => $snapshot['id'],
                    'context' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return $snapshot;
    }

    private function operationId(Pirep $pirep): ?string
    {
        if (preg_match('/Hermes ACARS \\[(op_[^\\]]+)\\]/', (string) $pirep->source_name, $match)) {
            return $match[1];
        }

        return null;
    }
}
