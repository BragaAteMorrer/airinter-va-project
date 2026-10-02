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
            'samples.*.touchdown_rate'=>'nullable|numeric|between:-30000,30000',
            'samples.*.simulation_rate'=>'nullable|numeric|between:0.1,64',
            'samples.*.engines_running'=>'nullable|array|max:8',
            'samples.*.engines_running.*'=>'boolean',
            'samples.*.localizer_dots'=>'nullable|numeric|between:-100,100',
            'samples.*.glideslope_dots'=>'nullable|numeric|between:-100,100'];
        foreach (['on_ground','gear_down','landing_flaps','thrust_stable','checklist_complete','slew_active','beacon_light','landing_light'] as $field) $rules['samples.*.'.$field]='nullable|boolean';
        $data = $request->validate($rules);
        $inserted = 0;
        $id = $pirep->id;
        $lastTelemetryAt = DB::table('promethee_telemetry')
            ->where('pirep_id', $id)
            ->max('recorded_at');
        $hadTelemetry = $lastTelemetryAt !== null;
        $hadArrival = DB::table('promethee_telemetry')
            ->where('pirep_id', $id)
            ->where('payload', 'like', '%"phase":"IN"%')
            ->exists();

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

        $resolvedOperationId = $operationId;
        if (!$resolvedOperationId && preg_match('/Hermes ACARS \[(op_[^\]]+)\]/', (string) $pirep->source_name, $matches)) {
            $resolvedOperationId = $matches[1];
        }

        $transitionContext = [
            'operation_id' => $resolvedOperationId,
            'pirep_id' => $pirep->id,
            'pirep_state' => (int) $pirep->state,
            'pirep_status' => $pirep->status instanceof \BackedEnum ? $pirep->status->value : $pirep->status,
            'aircraft_id' => $pirep->aircraft_id,
        ];

        if (!$hadTelemetry && $inserted > 0) {
            logger()->info('hermes_operation_transition', ['transition' => 'START'] + $transitionContext);
        } elseif ($hadTelemetry && $inserted > 0 && $lastTelemetryAt) {
            $firstIncoming = collect($data['samples'])
                ->map(fn ($sample) => Carbon::parse($sample['recorded_at'])->utc())
                ->sort()
                ->first();

            if ($firstIncoming && $firstIncoming->diffInSeconds(Carbon::parse($lastTelemetryAt)->utc()) >= 120) {
                logger()->info('hermes_operation_transition', ['transition' => 'RESUME'] + $transitionContext);
            }
        }

        $arrivedInBatch = collect($data['samples'])->contains(
            fn ($sample) => strtoupper((string) ($sample['phase'] ?? '')) === 'IN'
        );
        if (!$hadArrival && $arrivedInBatch) {
            logger()->info('hermes_operation_transition', ['transition' => 'ARRIVAL'] + $transitionContext);
        }

        return response()->json(['data'=>[
            'operation_id' => $resolvedOperationId,
            'pirep_id' => $pirep->id,
            'inserted' => $inserted,
            'received' => count($data['samples']),
            'live' => true,
        ]]);
    }
}
