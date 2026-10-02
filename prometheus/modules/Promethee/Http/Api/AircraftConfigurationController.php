<?php

namespace Modules\Promethee\Http\Api;

use App\Contracts\Controller;
use Illuminate\Http\Request;
use Modules\Promethee\Services\AircraftConfigurationResolver;

class AircraftConfigurationController extends Controller
{
    public function __construct(private readonly AircraftConfigurationResolver $resolver) {}

    public function show(string $registration, Request $request)
    {
        $data = $request->validate([
            'date' => 'nullable|date',
        ]);

        return response()->json([
            'data' => $this->resolver->resolveByRegistration($registration, $data['date'] ?? null),
        ]);
    }
}
