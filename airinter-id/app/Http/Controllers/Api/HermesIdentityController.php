<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HermesIdentityController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !$user->tokenCan('hermes:operate')) {
            return response()->json([
                'error' => 'insufficient_scope',
                'error_description' => 'The hermes:operate scope is required.',
            ], 403, ['Cache-Control' => 'no-store']);
        }

        $user->load('identities');

        return response()->json([
            'sub' => $user->subject,
            'name' => $user->display_name,
            'email' => $user->email,
            'state' => $user->state,
            'identities' => $user->identities->map(fn ($identity) => [
                'provider' => $identity->provider,
                'id' => $identity->external_user_id,
                'ident' => $identity->external_ident,
            ])->values(),
        ])->header('Cache-Control', 'no-store');
    }
}
