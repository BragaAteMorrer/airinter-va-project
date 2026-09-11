<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Controller;
use App\Exceptions\PilotIdNotFound;
use App\Models\Enums\UserState;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Issues a narrowly-scoped, short-lived credential for the desktop ACARS.
 * The password only crosses the HTTPS connection once and is never persisted.
 */
class AcarsSessionController extends Controller
{
    public function store(Request $request, UserService $userSvc): JsonResponse
    {
        $credentials = $request->validate([
            'login'    => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $user = $this->findUser($credentials['login'], $userSvc);
        if ($user === null || !Hash::check($credentials['password'], $user->password)
            || !in_array($user->state, [UserState::ACTIVE, UserState::ON_LEAVE], true)) {
            return response()->json([
                'error' => ['code' => '401', 'message' => 'Identifiants invalides ou compte non autorisé.'],
            ], 401);
        }

        // Keep sessions short and clean expired sessions for this pilot.
        $expiresAt = now()->addHours(12);
        $plainToken = bin2hex(random_bytes(32));
        DB::table('acars_access_tokens')->where('user_id', $user->id)->whereNull('revoked_at')
            ->where('expires_at', '<=', now())->delete();
        DB::table('acars_access_tokens')->insert([
            'user_id'      => $user->id,
            'token_hash'   => hash('sha256', $plainToken),
            'expires_at'   => $expiresAt,
            'last_used_at' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json(['data' => [
            'access_token' => $plainToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $expiresAt->toIso8601String(),
        ]]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = trim((string) preg_replace('/^Bearer\\s+/i', '', (string) $request->header('Authorization')));
        if ($token !== '') {
            DB::table('acars_access_tokens')->where('token_hash', hash('sha256', $token))->update([
                'revoked_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return response()->json(['data' => ['revoked' => true]]);
    }

    private function findUser(string $login, UserService $userSvc): ?User
    {
        if (str_contains($login, '@')) {
            return User::where('email', $login)->first();
        }

        try {
            return $userSvc->findUserByPilotId($login);
        } catch (PilotIdNotFound) {
            return null;
        }
    }
}
