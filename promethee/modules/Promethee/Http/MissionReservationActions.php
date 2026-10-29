<?php
namespace Modules\Promethee\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\FinanceService;
use Modules\Promethee\Services\RegionalOperationsService;

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
            $regionalOperations->reserveRepatriationMission($id, $r->user(), $finance, $confirmed);
            return back()->with('success', 'Mission de rapatriement réservée. Votre position pilote a été ajustée si un jumpseat était nécessaire.');
        }
}
