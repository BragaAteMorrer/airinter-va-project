<?php
namespace Modules\Promethee\Http;
use App\Contracts\Controller;
use App\Models\Pirep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Promethee\Services\OperationIdentityService;
class TelemetryController extends Controller
{
    public function __construct(private readonly OperationIdentityService $operationIdentity) {}

    public function storeOperation(string $operation, Request $request)
    {
        $pirep = $this->operationIdentity->resolvePirep($operation, (int) $request->user()->id);
        abort_if(!$pirep, 409, 'Préparez le PIREP de cette opération avant de démarrer la télémétrie.');

        return $this->storeForPirep($pirep, $request, $operation);
    }

    public function store(string $id, Request $request)
    {
        $pirep = Pirep::findOrFail($id);
        abort_unless($pirep->user_id === $request->user()->id,403);

        return $this->storeForPirep($pirep, $request);
    }

    private function storeForPirep(Pirep $pirep, Request $request, ?string $operationId = null)
    {
        $rules = ['samples'=>'required|array|min:1|max:100','samples.*.sample_id'=>'required|uuid',
            'samples.*.recorded_at'=>'required|date','samples.*.agl'=>'nullable|numeric|between:-2000,70000',
            'samples.*.lat'=>'nullable|numeric|between:-90,90','samples.*.lon'=>'nullable|numeric|between:-180,180',
            'samples.*.altitude_msl'=>'nullable|numeric|between:-2000,70000',
            'samples.*.ias'=>'nullable|numeric|between:0,2000','samples.*.vref'=>'nullable|numeric|between:1,500',
            'samples.*.max_ias'=>'nullable|numeric|between:1,2000','samples.*.vs'=>'nullable|numeric|between:-30000,30000',
            'samples.*.gs'=>'nullable|numeric|between:0,3000','samples.*.heading'=>'nullable|numeric|between:-360,360',
            'samples.*.fuel'=>'nullable|numeric|between:0,1000000',
            'samples.*.phase'=>'nullable|string|in:BOARDING,PUSHBACK,TAXI_OUT,TAKEOFF,CLIMB,CRUISE,ENROUTE,DESCENT,APPROACH,FINAL,LANDING,TAXI_IN,IN',
            'samples.*.bank'=>'nullable|numeric|between:-180,180',
            'samples.*.pitch'=>'nullable|numeric|between:-90,90',
            'samples.*.localizer_dots'=>'nullable|numeric|between:-100,100',
            'samples.*.glideslope_dots'=>'nullable|numeric|between:-100,100'];
        foreach (['on_ground','gear_down','landing_flaps','thrust_stable','checklist_complete'] as $field) $rules['samples.*.'.$field]='nullable|boolean';
        $data = $request->validate($rules);
        $inserted = 0;
        $id = $pirep->id;
        DB::transaction(function () use ($data,$id,&$inserted) {
            foreach ($data['samples'] as $s) {
                $date = Carbon::parse($s['recorded_at'])->utc();
                abort_if($date->isAfter(now()->addMinutes(5)),422,'Date de télémétrie future.');
                $sampleId=$s['sample_id'];unset($s['sample_id'],$s['recorded_at']);
                $inserted += DB::table('promethee_telemetry')->insertOrIgnore([
                    'pirep_id'=>$id,'sample_id'=>$sampleId,'recorded_at'=>$date,'payload'=>json_encode($s),'created_at'=>now(),'updated_at'=>now(),
                ]);
            }
        });
        return response()->json(['data'=>[
            'operation_id' => $operationId,
            'pirep_id' => $pirep->id,
            'inserted' => $inserted,
            'received' => count($data['samples']),
            'live' => true,
        ]]);
    }
}
