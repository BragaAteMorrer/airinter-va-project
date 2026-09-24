<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\Aircraft;
use App\Models\Bid;
use App\Models\Enums\AircraftState;
use App\Models\Enums\AircraftStatus;
use App\Models\SimBrief;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** API contract used by the desktop ACARS; it deliberately exposes only the pilot's own operations. */
class AcarsOperationsController extends Controller
{
    private const SIMULATORS = ['fs2004', 'fsx', 'p3d', 'msfs2020', 'msfs2024', 'xplane'];

    public function __construct(private readonly UserService $userSvc) {}

    public function index(Request $request)
    {
        $data = $request->validate(['simulator' => 'nullable|in:fs2004,fsx,p3d,msfs2020,msfs2024,xplane']);
        $simulator = $data['simulator'] ?? 'msfs2020';
        $bids = Bid::with(['flight.airline', 'flight.subfleets', 'aircraft.subfleet'])
            ->where('user_id', $request->user()->id)->latest()->get();

        return response()->json(['data' => [
            'simulator' => $simulator,
            'simulators' => self::SIMULATORS,
            'operations' => $bids->map(fn (Bid $bid) => $this->operation($bid, $simulator))->values(),
        ]]);
    }

    public function ofp(string $bidId, Request $request)
    {
        $bid = Bid::with(['flight', 'aircraft'])->where('user_id', $request->user()->id)->findOrFail($bidId);
        $ofp = SimBrief::where('user_id', $request->user()->id)
            ->where('flight_id', $bid->flight_id)->latest('updated_at')->firstOrFail();

        return response($ofp->acars_xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="promethee-'.$bid->flight->ident.'-'.$ofp->id.'.xml"',
        ]);
    }

    /**
     * Aircraft selection belongs to Prométhée, not to the desktop client.
     * It is deliberately scoped to the pilot's own reservation and intersects
     * the line's subfleets with the pilot's grade/type-rating permissions.
     */
    public function aircraft(string $bidId, Request $request)
    {
        $bid = Bid::with('flight.subfleets')
            ->where('user_id', $request->user()->id)->findOrFail($bidId);
        $flight = $bid->flight;
        if ($flight === null) abort(404);

        $allowedSubfleets = $this->userSvc->getAllowableSubfleets($request->user())->pluck('id')->all();
        $flightSubfleets = $flight->subfleets->pluck('id')->all();
        $subfleetIds = $flightSubfleets ? array_values(array_intersect($allowedSubfleets, $flightSubfleets)) : $allowedSubfleets;

        $aircraft = Aircraft::query()->with(['subfleet:id,name'])
            ->withCount(['bid', 'simbriefs' => fn ($query) => $query->whereNull('pirep_id')])
            ->where('state', AircraftState::PARKED)
            ->where('status', AircraftStatus::ACTIVE)
            ->when(setting('pireps.only_aircraft_at_dpt_airport'), fn ($query) => $query->where('airport_id', $flight->dpt_airport_id))
            ->when(setting('simbrief.block_aircraft'), fn ($query) => $query->having('simbriefs_count', 0))
            ->when(setting('bids.block_aircraft'), fn ($query) => $query->having('bid_count', 0))
            ->whereIn('subfleet_id', $subfleetIds)
            ->orderBy('icao')->orderBy('registration')->get()
            ->map(fn (Aircraft $plane) => [
                'id' => $plane->id,
                'registration' => $plane->registration,
                'name' => $plane->name,
                'icao' => $plane->icao,
                'subfleet' => $plane->subfleet?->name,
            ])->values();

        return response()->json(['data' => $aircraft]);
    }

    private function operation(Bid $bid, string $simulator): array
    {
        $flight = $bid->flight;
        $aircraft = $bid->aircraft;
        $subfleet = $aircraft?->subfleet ?? $flight?->subfleets->first();
        $name = (string) ($subfleet?->name ?? $aircraft?->name ?? '');
        $key = Str::of($name)->lower()->replace(['_', '-'], ' ')->squish()->toString();
        $fallback = config('acars.substitutions.'.$key.'.'.$simulator, []);
        // A documented simulator substitution has priority: the OFP must be
        // calculated for the aircraft the pilot will actually fly.
        $simbriefType = $fallback['simbrief_type'] ?? ($aircraft?->simbrief_type ?: ($subfleet?->simbrief_type ?: $aircraft?->icao));
        $airline = Str::lower((string) ($flight?->airline?->name ?? ''));
        $loadFactor = str_contains($airline, 'charter') ? config('acars.load_factors.air_charter_international')
            : (str_contains($airline, 'cargo') ? config('acars.load_factors.inter_cargo_service') : config('acars.load_factors.air_inter'));
        $ofp = SimBrief::where('user_id', $bid->user_id)->where('flight_id', $bid->flight_id)->latest('updated_at')->first();

        return [
            'bid_id' => $bid->id,
            'flight' => [
                'id' => $flight?->id, 'ident' => $flight?->ident, 'airline_id' => $flight?->airline_id,
                'flight_number' => $flight?->flight_number, 'departure' => $flight?->dpt_airport_id,
                'arrival' => $flight?->arr_airport_id, 'alternate' => $flight?->alt_airport_id,
                'route' => $flight?->route, 'level' => $flight?->level,
            ],
            'aircraft' => ['id' => $aircraft?->id, 'registration' => $aircraft?->registration, 'subfleet' => $name],
            'simbrief' => [
                'type' => $simbriefType,
                'addon' => $fallback['addon'] ?? null,
                'ofp_id' => $ofp?->id,
                'available' => $ofp !== null,
            ],
            'operating_rules' => [
                'passenger_weight_kg' => config('acars.passenger_weight_kg'),
                'checked_baggage_kg' => config('acars.checked_baggage_kg'),
                'load_factor_percent' => $loadFactor,
                'fuel_policy' => 'trip + 5% + alternate + expected holding + 45 minutes reserve',
            ],
        ];
    }
}
