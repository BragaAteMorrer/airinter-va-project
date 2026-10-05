<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load('identities');

        return response()->json([
            'sub' => $user->subject,
            'name' => $user->display_name,
            'email' => $user->email,
            'email_verified' => $user->email_verified_at !== null,
            'locale' => $user->preferred_locale,
            'zoneinfo' => $user->timezone,
            'state' => $user->state,
            'pilot_profile' => [
                'country' => $user->country,
                'home_airport_id' => $user->home_airport_id,
                'vatsim_id' => $user->vatsim_id,
                'ivao_id' => $user->ivao_id,
                'avatar_url' => $user->avatarUrl(),
            ],
            'identities' => $user->identities->map(fn ($identity) => [
                'provider' => $identity->provider,
                'id' => $identity->external_user_id,
                'ident' => $identity->external_ident,
            ])->values(),
        ]);
    }
}
