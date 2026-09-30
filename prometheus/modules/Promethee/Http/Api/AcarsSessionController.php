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
            // Prefer the dedicated API endpoint. Keep the historical OAuth
            // userinfo route as a compatibility fallback while Argos deployments
            // are upgraded. Retry 429/5xx briefly: Hermès metadata and token
            // exchanges can otherwise hit the same short burst limiter.
            $response = $this->verifyArgosSession($issuer, $data['access_token']);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json([
                'error' => ['code' => '502', 'message' => 'Prométhée ne parvient pas à joindre Argos. Réessayez dans quelques secondes.'],
            ], 502);
        }

        if ($response->status() === 401) {
            return response()->json([
                'error' => ['code' => '401', 'message' => 'La session Argos est invalide ou expirée. Reconnectez-vous avec Argos.'],
            ], 401);
        }

        if ($response->status() === 403) {
            return response()->json([
                'error' => ['code' => '403', 'message' => 'Cette session Argos ne possède pas le droit hermes:operate.'],
            ], 403);
        }

        if ($response->status() === 429) {
            return response()->json([
                'error' => ['code' => '429', 'message' => 'Argos limite temporairement les vérifications. Patientez quelques secondes puis réessayez.'],
            ], 429);
        }

        if (!$response->successful()) {
            $argosMessage = trim((string) ($response->json('error_description') ?? $response->json('message') ?? ''));
            return response()->json([
                'error' => [
                    'code' => (string) $response->status(),
                    'message' => $argosMessage !== ''
                        ? 'Argos a refusé la vérification : '.$argosMessage
                        : 'Argos a refusé la vérification de la session (HTTP '.$response->status().').',
                ],
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

    private function verifyArgosSession(string $issuer, string $accessToken): \Illuminate\Http\Client\Response
    {
        $paths = [
            '/api/v1/hermes/session',
            '/oauth/hermes/userinfo',
        ];

        $lastResponse = null;

        foreach ($paths as $path) {
            for ($attempt = 0; $attempt < 3; $attempt++) {
                $response = Http::acceptJson()
                    ->withToken($accessToken)
                    ->timeout(10)
                    ->get($issuer.$path);

                $lastResponse = $response;

                if ($response->successful() || in_array($response->status(), [401, 403], true)) {
                    return $response;
                }

                // A 404/405 means this Argos deployment does not expose that
                // compatibility endpoint; immediately try the next path.
                if (in_array($response->status(), [404, 405], true)) {
                    break;
                }

                if ($response->status() !== 429 && $response->status() < 500) {
                    return $response;
                }

                if ($attempt < 2) {
                    usleep((500 * (2 ** $attempt)) * 1000);
                }
            }
        }

        return $lastResponse ?? throw new \RuntimeException('Argos session verification returned no response.');
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
