<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HermesBridgeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->tokenCan('hermes:operate')) {
            return response()->json([
                'message' => 'Ce jeton Argos n’autorise pas l’utilisation d’Hermès.',
            ], 403);
        }

        if (method_exists($user, 'canUseSso') && !$user->canUseSso()) {
            return response()->json([
                'message' => 'Ce compte Argos ne peut pas utiliser le SSO actuellement.',
            ], 403);
        }

        $identity = $user->identities()
            ->where('provider', 'promethee')
            ->first();

        if (!$identity) {
            return response()->json([
                'message' => 'Ce compte Argos n’est pas lié à Prométhée.',
            ], 403);
        }

        return response()->json([
            'sub' => $user->subject,
            'state' => $user->state,
            'promethee' => [
                'id' => $identity->external_user_id,
                'ident' => $identity->external_ident,
            ],
        ]);
    }
}
