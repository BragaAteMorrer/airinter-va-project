<?php
namespace Modules\Promethee\Services;

use App\Models\Enums\AcarsType;
use App\Models\Enums\PirepState;
use App\Models\Pirep;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BulletinService
{
    public function build(string $month): array
    {
        $start = CarbonImmutable::createFromFormat('!Y-m', $month, 'Europe/Paris');
        $query = Pirep::where('state', PirepState::ACCEPTED)
            ->where('submitted_at','>=',$start->utc())
            ->where('submitted_at','<',$start->addMonth()->utc());

        $flights = (function () use ($query) {
            foreach ($query->lazyById(100) as $p) {
                $samples = $this->samplesForPirep($p->id);

                yield [
                    'landing_rate' => $p->getRawOriginal('landing_rate') === null ? null : (float) $p->landing_rate,
                    'samples' => $samples,
                ];
            }
        })();

        return (new SafetyAnalyzer())->aggregate($flights, $month);
    }

    /**
     * Prefer the high-fidelity Hermès archive. Historical vmsACARS/phpVMS
     * positions are used only when no Prométhée telemetry exists for the PIREP.
     * Missing legacy values stay missing; they are never silently treated as
     * compliant.
     */
    private function samplesForPirep(string $pirepId): array
    {
        $samples = DB::table('promethee_telemetry')
            ->where('pirep_id', $pirepId)
            ->orderBy('recorded_at')
            ->get()
            ->map(function ($row) {
                $payload = json_decode($row->payload, true);

                return array_merge(
                    is_array($payload) ? $payload : [],
                    ['recorded_at' => $row->recorded_at, 'telemetry_source' => 'hermes']
                );
            })
            ->all();

        if ($samples !== []) {
            return $samples;
        }

        return $this->legacyAcarsSamples($pirepId);
    }

    /**
     * Normalize only the fields that phpVMS/vmsACARS actually persisted.
     * Ground state may be derived from AGL when it is available:
     *  - > 50 ft AGL: airborne
     *  - <= 10 ft AGL with <= 80 kt GS: ground
     * Everything in-between remains unknown.
     */
    private function legacyAcarsSamples(string $pirepId): array
    {
        return DB::table('acars')
            ->where('pirep_id', $pirepId)
            ->where('type', AcarsType::FLIGHT_PATH)
            ->orderByRaw('COALESCE(sim_time, created_at) asc')
            ->get()
            ->map(function ($row) {
                $sample = ['telemetry_source' => 'legacy_acars'];

                $recordedAt = $row->sim_time ?? $row->created_at ?? null;
                if ($recordedAt !== null) {
                    $sample['recorded_at'] = $recordedAt;
                }

                foreach ([
                    'lat' => 'lat',
                    'lon' => 'lon',
                    'altitude_msl' => 'altitude_msl',
                    'altitude_agl' => 'agl',
                    'ias' => 'ias',
                    'gs' => 'gs',
                    'vs' => 'vs',
                    'heading' => 'heading',
                    'fuel' => 'fuel',
                ] as $column => $target) {
                    if (property_exists($row, $column) && $row->{$column} !== null) {
                        $sample[$target] = is_numeric($row->{$column})
                            ? (float) $row->{$column}
                            : $row->{$column};
                    }
                }

                $agl = $sample['agl'] ?? null;
                $gs = $sample['gs'] ?? null;
                if ($agl !== null) {
                    if ((float) $agl > 50) {
                        $sample['on_ground'] = false;
                    } elseif ((float) $agl <= 10 && ($gs === null || abs((float) $gs) <= 80)) {
                        $sample['on_ground'] = true;
                    }
                }

                return $sample;
            })
            ->filter(fn (array $sample) => isset($sample['recorded_at']))
            ->values()
            ->all();
    }

    public function save(string $month): array
    {
        $report = $this->build($month);
        DB::table('promethee_bulletins')->updateOrInsert(['month'=>$month], [
            'report'=>json_encode($report), 'updated_at'=>now(), 'created_at'=>now(),
        ]);

        return $report;
    }
}
