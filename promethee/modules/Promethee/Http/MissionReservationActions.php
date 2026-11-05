<?php
namespace Modules\Promethee\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FinanceService;
use Modules\Promethee\Services\RegionalOperationsService;
use Symfony\Component\HttpKernel\Exception\HttpException;

/** Reservation confirmation and booking actions shared by the mission controller. */
trait MissionReservationActions
{
    public function confirmMissionReservation(int $id, Request $r, RegionalOperationsService $regionalOperations) {
            $mission = DB::table('promethee_missions')
                ->where('id', $id)->where('active', true)->where('mission_type', 'repatriation')->first();
            abort_unless($mission, 404);
            $quote = $regionalOperations->quoteRepatriationMission($mission, $r->user());
    
            return $this->page('mission-confirm', compact('mission', 'quote'));
        }
    
    public function reserveMission(int $id, Request $r, FinanceService $finance, RegionalOperationsService $regionalOperations) {
            $mission = DB::table('promethee_missions')->where('id', $id)->where('active', true)->first();
            abort_unless($mission, 404);
            abort_unless($mission->mission_type === 'repatriation', 422, 'Cette mission ne nécessite pas de réservation.');
            $confirmed = $r->validate([
                'confirmed' => 'required|accepted',
                'origin_airport_id' => 'required|string|max:8',
                'jumpseat_amount_minor' => 'required|integer|min:0',
            ]);
            try {
                $regionalOperations->reserveRepatriationMission($id, $r->user(), $finance, $confirmed);
            } catch (HttpException $exception) {
                // A conflicting booking is a valid business rejection, not a
                // server error page. Keep the booking constraint, but take the
                // pilot straight to the list where it can be cancelled.
                if ($exception->getStatusCode() !== 409
                    || !str_contains($exception->getMessage(), 'est encore réservée')) {
                    throw $exception;
                }
                return redirect()->to(route('promethee.missions').'#my-missions')
                    ->withErrors([$exception->getMessage()]);
            }
            return back()->with('success', 'Mission de rapatriement réservée. Votre position pilote a été ajustée si un jumpseat était nécessaire.');
        }
}
