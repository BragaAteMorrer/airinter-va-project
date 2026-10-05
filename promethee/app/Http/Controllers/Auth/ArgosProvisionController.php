<?php

namespace App\Http\Controllers\Auth;

use App\Contracts\Controller;
use App\Models\Airline;
use App\Models\Enums\UserState;
use App\Models\User;
use App\Services\UserService;
use App\Support\Utils;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ArgosProvisionController extends Controller
{
    public function __invoke(Request $request, UserService $users): JsonResponse
    {
        $expected = (string) config('services.airinter_id.provisioning_token');
        $provided = (string) $request->header('X-Argos-Provisioning-Token');

        abort_if($expected === '' || !hash_equals($expected, $provided), 403, 'Invalid Argos provisioning credential.');

        if (setting('general.disable_registrations', false)) {
            return response()->json(['message' => 'Les inscriptions sont actuellement désactivées.'], 403);
        }

        if (setting('general.invite_only_registrations', false)) {
            return response()->json([
                'message' => 'Les inscriptions sur invitation doivent être traitées par le staff avant création Argos.',
            ], 403);
        }

        $data = $request->validate([
            'subject' => ['required', 'uuid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'vatsim_id' => ['nullable', 'string', 'max:32'],
            'ivao_id' => ['nullable', 'string', 'max:32'],
            'opt_in' => ['nullable', 'boolean'],
        ]);

        $existing = User::query()->where('argos_subject', $data['subject'])->first();
        if ($existing) {
            return response()->json($this->payload($existing));
        }

        if (User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($data['email'])])->exists()) {
            return response()->json([
                'message' => 'Un dossier pilote Prométhée existe déjà avec cette adresse e-mail. Utilisez la récupération de compte Argos.',
            ], 409);
        }

        $airline = Airline::query()
            ->where('icao', 'ITF')
            ->where('active', true)
            ->first();

        if (!$airline) {
            return response()->json(['message' => 'Air Inter (ITF) n’est pas configurée comme compagnie active.'], 503);
        }

        $user = User::create([
            'argos_subject' => $data['subject'],
            'name' => trim($data['name']),
            'email' => mb_strtolower(trim($data['email'])),
            // Argos owns credentials. The local hash is intentionally unknown
            // and cannot be used as a second login path.
            'password' => Hash::make(Str::random(96)),
            'api_key' => Utils::generateApiKey(),
            'airline_id' => $airline->id,
            'timezone' => $data['timezone'],
            'vatsim_id' => $data['vatsim_id'] ?: null,
            'ivao_id' => $data['ivao_id'] ?: null,
            'toc_accepted' => true,
            'opt_in' => (bool) ($data['opt_in'] ?? false),
            'state' => UserState::PENDING,
            'status' => 0,
        ]);

        $users->calculatePilotRank($user);
        $user->refresh();

        return response()->json($this->payload($user), 201);
    }

    private function payload(User $user): array
    {
        return [
            'id' => $user->id,
            'pilot_id' => $user->pilot_id,
            'ident' => $user->ident,
            'state' => (int) $user->state,
            'airline_id' => (int) $user->airline_id,
        ];
    }
}
