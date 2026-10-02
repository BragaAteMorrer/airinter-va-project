<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Controller;
use App\Http\Requests\CreateAirframeRequest;
use App\Http\Requests\UpdateAirframeRequest;
use App\Models\Aircraft;
use App\Models\Enums\AirframeSource;
use App\Models\SimBriefAirframe;
use App\Repositories\AirframeRepository;
use App\Services\SimBriefService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laracasts\Flash\Flash;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

class AirframeController extends Controller
{
    public function __construct(
        private readonly AirframeRepository $airframeRepo
    ) {}

    /**
     * @throws RepositoryException
     */
    public function index(Request $request): View
    {
        $this->airframeRepo->pushCriteria(new RequestCriteria($request));
        $airframes = $this->airframeRepo->where('source', AirframeSource::INTERNAL)->orderby('icao', 'asc')->orderby('name', 'asc')->get();

        return view('admin.airframes.index', [
            'airframes' => $airframes,
        ]);
    }

    public function create(): View
    {
        return view('admin.airframes.create', [
            'icao_codes' => Aircraft::whereNotNull('icao')->groupBy('icao')->pluck('icao')->toArray(),
            'simbrief_profile' => [],
        ]);
    }

    /**
     * @throws ValidatorException
     */
    public function store(CreateAirframeRequest $request): RedirectResponse
    {
        $input = $this->inputWithSimbriefProfile($request);

        $model = $this->airframeRepo->create($input);
        Flash::success('Airframe saved successfully.');

        return redirect(route('admin.airframes.index'));
    }

    public function show(int $id): RedirectResponse|View
    {
        $airframe = $this->airframeRepo->findWithoutFail($id);

        if (empty($airframe)) {
            Flash::error('SimBrief Airframe not found');

            return redirect(route('admin.airframes.index'));
        }

        return view('admin.airframes.show', [
            'airframe' => $airframe,
            'simbrief_profile' => $airframe->simbriefProfile(),
        ]);
    }

    public function edit(int $id): RedirectResponse|View
    {
        $airframe = $this->airframeRepo->findWithoutFail($id);

        if (empty($airframe)) {
            Flash::error('SimBrief Airframe not found');

            return redirect(route('admin.airframes.index'));
        }

        return view('admin.airframes.edit', [
            'airframe' => $airframe,
            'icao_codes' => Aircraft::whereNotNull('icao')->groupBy('icao')->pluck('icao')->toArray(),
            'simbrief_profile' => $airframe->simbriefProfile(),
        ]);
    }

    /**
     * @throws ValidatorException
     */
    public function update(int $id, UpdateAirframeRequest $request): RedirectResponse
    {
        $airframe = $this->airframeRepo->findWithoutFail($id);

        if (empty($airframe)) {
            Flash::error('SimBrief Airframe not found');

            return redirect(route('admin.airframes.index'));
        }

        $airframe = $this->airframeRepo->update($this->inputWithSimbriefProfile($request, $airframe), $id);
        Flash::success('SimBrief Airport updated successfully.');

        return redirect(route('admin.airframes.index'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $airframe = $this->airframeRepo->findWithoutFail($id);

        if (empty($airframe)) {
            Flash::error('SimBrief Airframe not found');

            return redirect(route('admin.airframes.index'));
        }

        $this->airframeRepo->delete($id);

        Flash::success('SimBrief Airframe deleted successfully.');

        return redirect(route('admin.airframes.index'));
    }

    private function inputWithSimbriefProfile(Request $request, ?SimBriefAirframe $airframe = null): array
    {
        $input = $request->all();
        $options = $airframe?->decodedOptions() ?? [];
        $clean = static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value;
        $nullable = static fn (mixed $value): mixed => ($value === null || $value === '') ? null : $value;

        $profile = [
            'strategy' => $request->input('simbrief_strategy', filled($input['airframe_id'] ?? null) ? 'custom_airframe' : 'native'),
            'proxy_type' => $nullable($clean($request->input('simbrief_proxy_type'))),
            'engines' => $nullable($clean($request->input('simbrief_engines'))),
            'cat' => $nullable($clean($request->input('simbrief_cat'))),
            'equip' => $nullable($clean($request->input('simbrief_equip'))),
            'transponder' => $nullable($clean($request->input('simbrief_transponder'))),
            'pbn' => $nullable($clean($request->input('simbrief_pbn'))),
            'extrarmk' => $nullable($clean($request->input('simbrief_extrarmk'))),
            'maxpax' => $nullable($request->input('simbrief_maxpax')),
            'hexcode' => $nullable($clean($request->input('simbrief_hexcode'))),
            'per' => $nullable($clean($request->input('simbrief_per'))),
            'paxwgt' => $nullable($request->input('simbrief_paxwgt')),
            'bagwgt' => $nullable($request->input('simbrief_bagwgt')),
            'ceiling' => $nullable($request->input('simbrief_ceiling')),
            'cruiseoffset' => $nullable($clean($request->input('simbrief_cruiseoffset'))),
            'weights_kg' => [
                'oew' => $nullable($request->input('simbrief_oew_kg')),
                'mzfw' => $nullable($request->input('simbrief_mzfw_kg')),
                'mtow' => $nullable($request->input('simbrief_mtow_kg')),
                'mlw' => $nullable($request->input('simbrief_mlw_kg')),
                'maxfuel' => $nullable($request->input('simbrief_maxfuel_kg')),
                'maxcargo' => $nullable($request->input('simbrief_maxcargo_kg')),
            ],
            'performance' => [
                'fuelfactor' => $nullable($clean($request->input('simbrief_fuelfactor'))),
                'climb' => $nullable($clean($request->input('simbrief_climb'))),
                'cruise' => $nullable($clean($request->input('simbrief_cruise'))),
                'descent' => $nullable($clean($request->input('simbrief_descent'))),
            ],
        ];

        $options['simbrief'] = $profile;
        $input['icao'] = strtoupper(trim((string) ($input['icao'] ?? '')));
        $input['airframe_id'] = $nullable($clean($input['airframe_id'] ?? null));
        $input['options'] = json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        foreach (array_keys(SimBriefAirframe::$rules) as $key) {
            if (str_starts_with($key, 'simbrief_')) unset($input[$key]);
        }

        return $input;
    }

    // Manually trigger update of SimBrief Airframe and Layouts
    public function updateSimbriefData()
    {
        Log::debug('Manually Updating SimBrief Support Data');
        $SimBriefSVC = app(SimBriefService::class);
        $SimBriefSVC->getAircraftAndAirframes();
        $SimBriefSVC->GetBriefingLayouts();

        Flash::success('SimBrief Airframe and Layouts updated successfully.');

        return redirect(route('admin.airframes.index'));
    }
}
