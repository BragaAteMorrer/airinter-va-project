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
        $ofpId = trim((string) $request->query('ofp_id', ''));
        $validState = (bool) preg_match('/^[A-Za-z0-9]{64}$/', $state);
        $validOfp = $ofpId !== '' && (bool) preg_match('/^[A-Za-z0-9_-]{1,100}$/', $ofpId);

        if (!$validState || !$validOfp || !$this->sessions->complete($state, $ofpId)) {
            return response()->view('promethee::simbrief-callback', [
                'success' => false,
                'message' => 'La session SimBrief a expiré ou la réponse reçue est invalide.',
            ], 410);
        }

        return response()->view('promethee::simbrief-callback', [
            'success' => true,
            'message' => 'OFP reçu. Hermès peut maintenant l’importer dans Prométhée.',
        ]);
    }
}
