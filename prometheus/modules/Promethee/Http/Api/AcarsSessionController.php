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

class AcarsSessionController extends Controller
{
    public function store(Request $request, UserService $userSvc): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $user = $this->findUser(trim($credentials['login']), $userSvc);
        if ($user === null || !Hash::check($credentials['password'], $user->password)
            || !in_array($user->state, [UserState::ACTIVE, UserState::ON_LEAVE], true)) {
            return response()->json([
                'error' => ['code' => '401', 'message' => 'Identifiants invalides ou compte non autorisé.'],
            ], 401);
        }

        return $this->issueToken($user);
    }

    public function argos(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'access_token' => ['required', 'string', 'max:8192'],
        ]);

        $argosUrl = rtrim((string) env('AIRINTER_ID_URL', 'https://argos.airinter-va.org'), '/');

        try {
            $response = Http::acceptJson()
                ->withToken($payload['access_token'])
                ->timeout(10)
                ->get($argosUrl.'/api/v1/hermes/bridge');
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'error' => ['code' => '502', 'message' => 'Argos est temporairement indisponible.'],
            ], 502);
        }

        if (!$response->successful()) {
            return response()->json([
                'error' => [
                    'code' => (string) $response->status(),
                    'message' => (string) ($response->json('message') ?: 'Argos a refusé cette session Hermès.'),
                ],
            ], $response->status() === 403 ? 403 : 401);
        }

        $prometheeUserId = $response->json('promethee.id');
        if (!is_scalar($prometheeUserId) || (string) $prometheeUserId === '') {
            return response()->json([
                'error' => ['code' => '403', 'message' => 'Le compte Argos n’est pas lié à Prométhée.'],
            ], 403);
        }

        $user = User::query()->find($prometheeUserId);
        if (!$user || !in_array($user->state, [UserState::ACTIVE, UserState::ON_LEAVE], true)) {
            return response()->json([
                'error' => ['code' => '403', 'message' => 'Le compte pilote Prométhée n’est pas autorisé à utiliser Hermès.'],
            ], 403);
        }

        return $this->issueToken($user);
    }

    public function identityConfiguration(): JsonResponse
    {
        $clientId = trim((string) env('AIRINTER_ID_HERMES_CLIENT_ID', ''));

        return response()->json(['data' => [
            'issuer' => rtrim((string) env('AIRINTER_ID_URL', 'https://argos.airinter-va.org'), '/'),
            'client_id' => $clientId,
            'redirect_uri' => (string) env('AIRINTER_ID_HERMES_REDIRECT_URI', 'http://127.0.0.1:47821/callback'),
            'scope' => 'openid profile email hermes:operate',
            'pkce_method' => 'S256',
            'configured' => $clientId !== '',
        ]]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $token = trim((string) preg_replace('/^Bearer\s+/i', '', (string) $request->header('Authorization')));
        if ($token !== '') {
            DB::table('acars_access_tokens')->where('token_hash', hash('sha256', $token))->update([
                'revoked_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['data' => ['revoked' => true]]);
    }

    private function issueToken(User $user): JsonResponse
    {
        $expiresAt = now()->addHours(12);
        $plainToken = bin2hex(random_bytes(32));

        DB::table('acars_access_tokens')
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->where('expires_at', '<=', now())
            ->delete();

        DB::table('acars_access_tokens')->insert([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
            'last_used_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['data' => [
            'access_token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
        ]]);
    }

    private function findUser(string $login, UserService $userSvc): ?User
    {
        if (str_contains($login, '@')) {
            return User::whereRaw('LOWER(email) = ?', [mb_strtolower($login)])->first();
        }

        try {
            return $userSvc->findUserByPilotId($login);
        } catch (PilotIdNotFound) {
            return null;
        }
    }
}
