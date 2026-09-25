<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use Illuminate\Http\Request;
use Modules\Promethee\Services\SimBriefApiSessionService;

class SimBriefCallbackController extends Controller
{
    public function __construct(private readonly SimBriefApiSessionService $sessions) {}

    public function __invoke(Request $request, string $state)
    {
        $validState = (bool) preg_match('/^[A-Za-z0-9]{64}$/', $state);
        $session = $validState ? $this->sessions->find($state) : null;
        $ofpId = trim((string) $request->query('ofp_id', $session['ofp_id'] ?? ''));
        $validOfp = $ofpId !== '' && (bool) preg_match('/^[A-Za-z0-9_-]{1,100}$/', $ofpId);

        if (!$validState || !$session || !$validOfp) {
            return response()->view('promethee::simbrief-callback', [
                'success' => false,
                'message' => 'La session SimBrief a expiré ou la réponse reçue est invalide.',
            ], 410);
        }

        // Some SimBrief API v1 flows do not append ofp_id to outputpage.
        // The identifier was already deterministically computed at session creation.
        $this->sessions->complete($state, $ofpId);

        return response()->view('promethee::simbrief-callback', [
            'success' => true,
            'message' => 'Génération terminée. Hermès importe maintenant l’OFP dans Prométhée.',
        ]);
    }
}
