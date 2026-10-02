<?php

namespace Modules\Promethee\Services;

use App\Models\Bid;
use App\Models\Enums\AcarsType;
use App\Models\Enums\PirepState;
use App\Models\Enums\PirepStatus;
use App\Models\Pirep;
use App\Models\SimBrief;
use App\Services\PirepService;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Single source of truth for the Hermès PIREP lifecycle.
 *
 * The important distinction is deliberately explicit:
 * - a PIREP can exist without being filed;
 * - ARRIVED can mean "awaiting final filing";
 * - only a server-side phpVMS filing transition may complete an operation.
 */
final class HermesPirepLifecycleService
{
    public function __construct(private readonly PirepService $pirepSvc) {}

    public function isCancelled(Pirep $pirep): bool
    {
        return (int) $pirep->state === PirepState::CANCELLED
            || $pirep->status === PirepStatus::CANCELLED;
    }

    public function isActiveDraft(Pirep $pirep): bool
    {
        return (int) $pirep->state === PirepState::IN_PROGRESS
            && $pirep->submitted_at === null
            && $pirep->status !== PirepStatus::ARRIVED
            && $pirep->status !== PirepStatus::CANCELLED;
    }

    /**
     * A Hermès operation is completed only when phpVMS has actually filed the
     * report. PirepService::file() writes submitted_at and moves the state to
     * PENDING before submit() can accept/reject it, so requiring both gives us
     * a stable server-side proof of final filing.
     */
    public function isFiled(Pirep $pirep): bool
    {
        return $pirep->submitted_at !== null
            && in_array(
                (int) $pirep->state,
                [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED],
                true
            );
    }

    public function isAwaitingFiling(Pirep $pirep): bool
    {
        return !$this->isFiled($pirep)
            && !$this->isCancelled($pirep)
            && $pirep->status === PirepStatus::ARRIVED
            && $this->hasFlightEvidence($pirep);
    }

    public function hasFlightEvidence(Pirep $pirep): bool
    {
        if (DB::table('promethee_telemetry')->where('pirep_id', $pirep->id)->exists()) {
            return true;
        }

        // SimBrief creates ROUTE records during planning. They are not proof
        // that ACARS ever started; only non-route core ACARS rows count.
        return DB::table('acars')
            ->where('pirep_id', $pirep->id)
            ->where('type', '!=', AcarsType::ROUTE)
            ->exists();
    }

    /**
     * Detect legacy Hermès corruption without mutating it.
     *
     * Older clients could create a terminal-looking PIREP before the simulator
     * ever ran. We may identify that state for diagnostics/recovery, but the web
     * request path must never delete or reopen it automatically.
     */
    public function isLegacyGhost(Pirep $pirep): bool
    {
        if (!str_starts_with((string) $pirep->source_name, 'Hermes ACARS [op_')) {
            return false;
        }

        $looksTerminal = $pirep->submitted_at !== null
            || in_array(
                (int) $pirep->state,
                [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED],
                true
            )
            || $pirep->status === PirepStatus::ARRIVED;

        return $looksTerminal && !$this->hasFlightEvidence($pirep);
    }

    public function diagnose(Pirep $pirep): array
    {
        return [
            'pirep_id' => $pirep->id,
            'source_name' => $pirep->source_name,
            'state' => (int) $pirep->state,
            'status' => $this->statusValue($pirep),
            'submitted_at' => optional($pirep->submitted_at)?->toIso8601String(),
            'filed' => $this->isFiled($pirep),
            'active_draft' => $this->isActiveDraft($pirep),
            'awaiting_filing' => $this->isAwaitingFiling($pirep),
            'flight_evidence' => $this->hasFlightEvidence($pirep),
            'legacy_ghost' => $this->isLegacyGhost($pirep),
        ];
    }

    /**
     * Explicit/manual repair path for a legacy zero-flight ghost.
     *
     * This method is intentionally never called by the HTTP prefile workflow.
     * The CLI command invokes it only with --apply after a dry-run diagnosis.
     */
    public function repairLegacyGhost(Pirep $pirep, Bid $bid): array
    {
        if (!$this->isLegacyGhost($pirep)) {
            throw new LogicException('Refusing repair: this PIREP is not a proven zero-flight Hermès ghost.');
        }

        $ghostId = $pirep->id;

        DB::transaction(function () use ($pirep, $bid) {
            $pirep->loadMissing(['user', 'aircraft']);
            $wasAccepted = (int) $pirep->state === PirepState::ACCEPTED;
            $aircraft = $pirep->aircraft;
            $user = $pirep->user;
            $departure = $pirep->dpt_airport_id;
            $arrival = $pirep->arr_airport_id;
            $createdAt = $pirep->created_at;

            // Reverse phpVMS stats first if a broken build managed to auto-accept
            // the ghost. This keeps existing service-side accounting semantics.
            if ($wasAccepted) {
                $pirep = $this->pirepSvc->reject($pirep);
            }

            DB::table('promethee_telemetry')->where('pirep_id', $pirep->id)->delete();

            // Keep the OFP for the same reservation; PirepService::delete()
            // would otherwise remove the attached SimBrief row.
            SimBrief::query()
                ->where('pirep_id', $pirep->id)
                ->where('user_id', $bid->user_id)
                ->where('flight_id', $bid->flight_id)
                ->where('aircraft_id', $bid->aircraft_id)
                ->update(['pirep_id' => null]);

            $this->pirepSvc->delete($pirep);

            // Roll positions back only when no newer report can legitimately own
            // the current aircraft/pilot location.
            if ($aircraft && $departure && (string) $aircraft->airport_id === (string) $arrival) {
                $hasNewerAircraftPirep = Pirep::query()
                    ->where('aircraft_id', $aircraft->id)
                    ->where('created_at', '>', $createdAt)
                    ->exists();

                if (!$hasNewerAircraftPirep) {
                    $aircraft->airport_id = $departure;
                    $aircraft->save();
                }
            }

            if ($user && (string) $user->curr_airport_id === (string) $arrival) {
                $lastAccepted = Pirep::query()
                    ->where('user_id', $user->id)
                    ->where('state', PirepState::ACCEPTED)
                    ->latest('submitted_at')
                    ->first();

                $user->last_pirep_id = $lastAccepted?->id;
                $user->curr_airport_id = $lastAccepted?->arr_airport_id ?: $user->home_airport_id;
                $user->save();
            }
        });

        return [
            'operation_id' => 'op_'.$bid->id,
            'pirep_id' => $ghostId,
            'repaired' => true,
        ];
    }

    private function statusValue(Pirep $pirep): mixed
    {
        $status = $pirep->status;
        return $status instanceof \BackedEnum ? $status->value : $status;
    }
}
