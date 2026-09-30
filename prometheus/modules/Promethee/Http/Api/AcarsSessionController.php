<?php

namespace Modules\Promethee\Http\Api;

use App\Contracts\Controller;
use App\Exceptions\PilotIdNotFound;
use App\Models\Enums\UserState;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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

        $user = $this->findUser(trim($credentials['login']), $userSvc);
        if ($user === null || !Hash::check($credentials['password'], $user->password)
            || !in_array($user->state, [UserState::ACTIVE, UserState::ON_LEAVE], true)) {
            return response()->json([
                'error' => ['code' => '401', 'message' => 'Identifiants invalides ou compte non autorisé.'],
            ], 401);
        }

        return $this->issueSession($user);
    }

    public function storeFromArgos(Request $request): JsonResponse
    {
        $data = $request->validate([
            'access_token' => ['required', 'string', 'max:8192'],
        ]);

        $issuer = rtrim((string) env('ARGOS_ISSUER', 'https://argos.airinter-va.org'), '/');

        try {
            $response = Http::acceptJson()
                ->withToken($data['access_token'])
                ->timeout(10)
                ->get($issuer.'/oauth/hermes/userinfo');
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'error' => ['code' => '502', 'message' => 'Prométhée ne parvient pas à joindre Argos.'],
            ], 502);
        }

        if ($response->status() === 401) {
            return response()->json([
                'error' => ['code' => '401', 'message' => 'La session Argos est invalide ou expirée.'],
            ], 401);
        }

        if ($response->status() === 403) {
            return response()->json([
                'error' => ['code' => '403', 'message' => 'Cette session Argos ne possède pas le droit hermes:operate.'],
            ], 403);
        }

        if (!$response->successful()) {
            return response()->json([
                'error' => ['code' => '502', 'message' => 'Argos a refusé la vérification de la session.'],
            ], 502);
        }

        $email = mb_strtolower(trim((string) $response->json('email')));
        if ($email === '') {
            return response()->json([
                'error' => ['code' => '422', 'message' => 'Argos n’a pas renvoyé l’adresse e-mail du pilote.'],
            ], 422);
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($user === null || !in_array($user->state, [UserState::ACTIVE, UserState::ON_LEAVE], true)) {
            return response()->json([
                'error' => ['code' => '403', 'message' => 'Aucun compte pilote Prométhée actif ne correspond à cette identité Argos.'],
            ], 403);
        }

        return $this->issueSession($user);
    }

    private function issueSession(User $user): JsonResponse
    {
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
            // Some installations use a case-sensitive database collation while
            // e-mail addresses must be accepted case-insensitively by ACARS.
            return User::whereRaw('LOWER(email) = ?', [mb_strtolower($login)])->first();
        }

        try {
            return $userSvc->findUserByPilotId($login);
        } catch (PilotIdNotFound) {
            return null;
        }
    }
}
