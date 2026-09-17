<?php
namespace Modules\Promethee\Services;
use App\Models\Pirep;
use App\Models\Enums\PirepState;
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
                $samples = DB::table('promethee_telemetry')->where('pirep_id',$p->id)
                    ->orderBy('recorded_at')->get()->map(function ($row) {
                        return array_merge(json_decode($row->payload,true), ['recorded_at'=>$row->recorded_at]);
                    })->all();
                yield ['landing_rate'=>$p->getRawOriginal('landing_rate') === null ? null : (float)$p->landing_rate, 'samples'=>$samples];
            }
        })();
        return (new SafetyAnalyzer())->aggregate($flights, $month);
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
