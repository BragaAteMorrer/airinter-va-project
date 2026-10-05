<?php

namespace Modules\Promethee\Http;

use App\Contracts\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Promethee\Models\RealSimulatorCertification;
use Modules\Promethee\Services\BrandingService;

class RealSimulatorCertificationController extends Controller
{
    private const LEVELS = [
        'FFS_A' => 'FFS niveau A',
        'FFS_B' => 'FFS niveau B',
        'FFS_C' => 'FFS niveau C',
        'FFS_D' => 'FFS niveau D',
        'FTD' => 'FTD',
        'FNPT_I' => 'FNPT I',
        'FNPT_II' => 'FNPT II',
        'FNPT_II_MCC' => 'FNPT II MCC',
        'OTHER' => 'Autre dispositif réel',
    ];

    public function __construct(private readonly BrandingService $branding) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:100',
            'status' => 'nullable|in:verified,revoked',
        ]);

        $query = RealSimulatorCertification::query()
            ->with(['pilot:id,name,pilot_id', 'verifier:id,name,pilot_id'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $filters['status']))
            ->when($request->filled('q'), function ($q) use ($filters) {
                $term = '%'.trim($filters['q']).'%';
                $q->where(function ($certifications) use ($term) {
                    $certifications
                        ->where('certificate_name', 'like', $term)
                        ->orWhere('aircraft_type', 'like', $term)
                        ->orWhere('device_name', 'like', $term)
                        ->orWhere('organisation', 'like', $term)
                        ->orWhere('reference', 'like', $term)
                        ->orWhereHas('pilot', fn ($pilots) => $pilots
                            ->where('name', 'like', $term)
                            ->orWhere('pilot_id', 'like', $term));
                });
            })
            ->orderByDesc('completed_on')
            ->orderByDesc('id');

        return view('promethee::admin.real-simulator-certifications', [
            'certifications' => $query->paginate(40)->withQueryString(),
            'pilots' => User::query()->orderBy('pilot_id')->get(['id', 'name', 'pilot_id']),
            'levels' => self::LEVELS,
            'branding' => $this->branding->active(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data = $this->normalise($data);
        $data['verified_by'] = $request->user()->id;
        $data['verified_at'] = now();

        RealSimulatorCertification::query()->create($data);

        return back()->with('success', 'Certification sur simulateur réel enregistrée et vérifiée.');
    }

    public function update(RealSimulatorCertification $certification, Request $request)
    {
        $data = $this->normalise($this->validated($request));
        $data['verified_by'] = $request->user()->id;
        $data['verified_at'] = now();

        $certification->fill($data)->save();

        return back()->with('success', 'Certification mise à jour.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'certificate_name' => 'required|string|max:160',
            'aircraft_type' => 'nullable|string|max:32',
            'simulator_level' => ['required', Rule::in(array_keys(self::LEVELS))],
            'device_name' => 'required|string|max:160',
            'organisation' => 'required|string|max:160',
            'location' => 'nullable|string|max:160',
            'completed_on' => 'required|date',
            'valid_until' => 'nullable|date|after_or_equal:completed_on',
            'reference' => 'nullable|string|max:120',
            'evidence_url' => 'nullable|url|max:1000',
            'status' => 'required|in:verified,revoked',
            'notes' => 'nullable|string|max:4000',
        ]);
    }

    private function normalise(array $data): array
    {
        $data['certificate_name'] = trim($data['certificate_name']);
        $data['aircraft_type'] = filled($data['aircraft_type'] ?? null)
            ? strtoupper(trim($data['aircraft_type']))
            : null;
        $data['device_name'] = trim($data['device_name']);
        $data['organisation'] = trim($data['organisation']);
        $data['location'] = filled($data['location'] ?? null) ? trim($data['location']) : null;
        $data['reference'] = filled($data['reference'] ?? null) ? trim($data['reference']) : null;
        $data['notes'] = filled($data['notes'] ?? null) ? trim($data['notes']) : null;

        return $data;
    }
}
