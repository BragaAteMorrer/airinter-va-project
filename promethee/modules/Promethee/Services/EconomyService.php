<?php
namespace Modules\Promethee\Services;

use App\Models\Airport;
use App\Models\Fare;
use App\Models\Flight;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EconomyService
{
    private function error(string $message): never { throw ValidationException::withMessages(['value'=>$message]); }

    public function preview(array $input): array
    {
        if ($input['target']==='ticket' && DB::table('promethee_settings')->where('key','pricing.bands.enabled')->value('value')!=='0') {
            $input['blue_percent']=(float)(DB::table('promethee_settings')->where('key','pricing.bands.blue')->value('value') ?: $input['blue_percent']);
            $input['white_percent']=(float)(DB::table('promethee_settings')->where('key','pricing.bands.white')->value('value') ?: $input['white_percent']);
        }
        $rows=[];
        if ($input['target']==='fuel') {
            $query=Airport::query();
            if (!empty($input['airport_id'])) $query->where('id',$input['airport_id']);
            foreach (['location','region','country'] as $field) if (!empty($input[$field])) $query->where($field,$input[$field]);
            if (!empty($input['airline_id'])) $query->whereHas('departures',fn($q)=>$q->where('airline_id',$input['airline_id']));
            if ($query->count()>500) $this->error('Plus de 500 aéroports : préciser le périmètre.');
            $column=$input['fuel_type'];
            foreach ($query->orderBy('id')->get() as $airport) {
                $before=$airport->getRawOriginal($column);
                $default=['fuel_jeta_cost'=>'airports.default_jet_a_fuel_cost','fuel_100ll_cost'=>'airports.default_100ll_fuel_cost','fuel_mogas_cost'=>'airports.default_mogas_fuel_cost'][$column];
                $effective=$before ? (float)$before : (float)setting($default,0);
                try { $after=PriceMath::adjust($effective,$input['mode'],(float)$input['value']); } catch (\InvalidArgumentException $e) { $this->error($e->getMessage()); }
                if ($after<=0) $this->error('Le carburant doit rester strictement positif (0 signifie tarif par défaut dans phpVMS).');
                $rows[]=['id'=>$airport->id,'label'=>$airport->icao.' — '.$airport->name,'column'=>$column,'before'=>$before,'effective'=>$effective,'after'=>$after];
            }
        } else {
            $fare=Fare::findOrFail($input['fare_id']);
            $query=Flight::query()->with(['fares','subfleets.fares']);
            if (empty($input['include_inactive'])) $query->where('active',true);
            if (!empty($input['airline_id'])) $query->where('airline_id',$input['airline_id']);
            if (!empty($input['subfleet_id'])) $query->whereHas('subfleets',fn($q)=>$q->where('subfleets.id',$input['subfleet_id']));
            if (!empty($input['distance_min'])) $query->where('distance','>=',$input['distance_min']);
            if (!empty($input['distance_max'])) $query->where('distance','<=',$input['distance_max']);
            $query->whereHas('dpt_airport',function($q) use ($input) {
                if (!empty($input['airport_id'])) $q->where('id',$input['airport_id']);
                foreach (['location','region','country'] as $field) if (!empty($input[$field])) $q->where($field,$input[$field]);
            });
            if (!empty($input['arrival_country'])) $query->whereHas('arr_airport',fn($q)=>$q->where('country',$input['arrival_country']));
            $query->whereHas('subfleets.fares',fn($q)=>$q->where('fares.id',$fare->id));
            if ($query->count()>500) $this->error('Plus de 500 lignes : préciser le périmètre.');
            foreach ($query->orderBy('id')->get() as $flight) {
                $pivot=DB::table('flight_fare')->where(['flight_id'=>$flight->id,'fare_id'=>$fare->id])->first();
                $pricing=DB::table('promethee_pricing')->where(['flight_id'=>$flight->id,'fare_id'=>$fare->id])->first();
                $effective=[];
                foreach ($flight->subfleets as $subfleet) {
                    $sfFare=$subfleet->fares->firstWhere('id',$fare->id);
                    if ($sfFare) $effective[]=$this->resolve($this->resolve((float)$fare->price,$sfFare->pivot->price),$pivot->price ?? null);
                }
                $effective=array_values(array_unique($effective));
                if (count($effective)>1 && $input['mode']!=='set' && !$pricing) $this->error('La ligne '.$flight->ident.' a plusieurs prix selon l’appareil. Utiliser « fixer » pour unifier le tarif.');
                $base=$pricing ? (float)$pricing->red_price : ($effective[0] ?? (float)$fare->price);
                try { $red=PriceMath::adjust($base,$input['mode'],(float)$input['value']); } catch (\InvalidArgumentException $e) { $this->error($e->getMessage()); }
                $band=$input['band']==='keep' ? ($pricing->band ?? 'rouge') : $input['band'];
                $multiplier=['bleu'=>(float)$input['blue_percent']/100,'blanc'=>(float)$input['white_percent']/100,'rouge'=>1][$band];
                $after=round($red*$multiplier,2);
                if (strlen((string)$after)>10) $this->error('Prix trop grand pour le champ phpVMS.');
                $rows[]=['id'=>$flight->id,'fare_id'=>$fare->id,'label'=>$flight->ident.' · '.$flight->dpt_airport_id.' → '.$flight->arr_airport_id,
                    'before'=>$pivot ? (array)$pivot : null,'pricing_before'=>$pricing ? (array)$pricing : null,
                    'effective'=>$effective[0] ?? (float)$fare->price,'after'=>$after,'red_price'=>$red,'band'=>$band,'multiplier'=>$multiplier,
                    'capacity'=>(int)($pivot->capacity ?? $fare->capacity ?? 0)];
            }
        }
        if (!$rows) $this->error('Aucun élément ne correspond à ce périmètre et à cette cabine.');
        return ['input'=>$input,'rows'=>$rows,'impact'=>$this->impact($input,$rows)];
    }

    private function impact(array $input,array $rows): array
    {
        if ($input['target']==='fuel') return ['kind'=>'fuel','count'=>count($rows),'per_thousand'=>array_sum(array_map(fn($row)=>($row['after']-$row['effective'])*1000,$rows)),'message'=>'Écart estimé pour un avitaillement de 1 000 unités internes par aéroport.'];
        $load=(float)(DB::table('promethee_settings')->where('key','pricing.simulation.load_factor')->value('value') ?: 70)/100;
        $passengers=0; $revenue=0;
        foreach ($rows as $row) { $occupied=(int)round($row['capacity']*$load); $passengers+=$occupied; $revenue+=($row['after']-$row['effective'])*$occupied; }
        return ['kind'=>'ticket','count'=>count($rows),'load_factor'=>round($load*100,1),'passengers'=>$passengers,'revenue_delta'=>$revenue,'message'=>'Écart de recette par rotation estimé à partir du taux de remplissage simulé. Les coûts opérationnels restent inchangés.'];
    }

    private function bandMultipliers(): array
    {
        $blue = (float) (DB::table('promethee_settings')->where('key', 'pricing.bands.blue')->value('value') ?: 50);
        $white = (float) (DB::table('promethee_settings')->where('key', 'pricing.bands.white')->value('value') ?: 75);

        return ['bleu' => $blue / 100, 'blanc' => $white / 100, 'rouge' => 1.0];
    }

    private function resolve(float $base,mixed $override): float
    {
        if ($override===null || $override==='') return $base;
        return str_ends_with((string)$override,'%') ? $base*(float)$override/100 : (float)$override;
    }

    public function apply(array $preview,int $userId): int
    {
        return DB::transaction(function() use ($preview,$userId) {
            foreach ($preview['rows'] as $row) {
                $target=$preview['input']['target']==='import' ? $row['target'] : $preview['input']['target'];
                if ($target==='fuel') {
                    $current=DB::table('airports')->where('id',$row['id'])->lockForUpdate()->first();
                    if (!$current || (string)$current->{$row['column']}!==(string)$row['before']) $this->error('Un tarif a changé depuis l’aperçu. Refaire la simulation.');
                    DB::table('airports')->where('id',$row['id'])->update([$row['column']=>$row['after']]);
                } else {
                    DB::table('flights')->where('id',$row['id'])->lockForUpdate()->first();
                    $key=['flight_id'=>$row['id'],'fare_id'=>$row['fare_id']]; $current=DB::table('flight_fare')->where($key)->first(); $pricing=DB::table('promethee_pricing')->where($key)->first();
                    if (($current ? (array)$current : null)!=$row['before'] || ($pricing ? (array)$pricing : null)!=$row['pricing_before']) $this->error('Une ligne a changé depuis l’aperçu. Refaire la simulation.');
                    DB::table('flight_fare')->updateOrInsert($key,['price'=>(string)$row['after'],'updated_at'=>now()]);
                    DB::table('promethee_pricing')->updateOrInsert($key,['red_price'=>$row['red_price'],'band'=>$row['band'],'multiplier'=>$row['multiplier'],'updated_at'=>now(),'created_at'=>now()]);
                }
            }
            $changeId=DB::table('promethee_changes')->insertGetId(['user_id'=>$userId,'target'=>$preview['input']['target'],'filters'=>json_encode($preview['input']),'changes'=>json_encode($preview['rows']),'created_at'=>now(),'updated_at'=>now()]);
            foreach ($preview['rows'] as $row) {
                $target=$preview['input']['target']==='import' ? $row['target'] : $preview['input']['target'];
                DB::table('promethee_price_history')->insert(['change_id'=>$changeId,'target'=>$target,'subject_id'=>$row['id'],'field'=>$target==='fuel' ? $row['column'] : 'fare:'.$row['fare_id'],'before_price'=>$row['effective'],'after_price'=>$row['after'],'context'=>json_encode(['label'=>$row['label'],'band'=>$row['band'] ?? null]),'created_at'=>now(),'updated_at'=>now()]);
            }
            return $changeId;
        });
    }

    public function revert(int $id): void
    {
        DB::transaction(function() use ($id) {
            $change=DB::table('promethee_changes')->where('id',$id)->lockForUpdate()->first();
            if (!$change || $change->reverted_at) $this->error('Modification absente ou déjà annulée.');
            foreach (json_decode($change->changes,true) as $row) {
                $target=$change->target==='import' ? $row['target'] : $change->target;
                if ($target==='fuel') {
                    $current=DB::table('airports')->where('id',$row['id'])->lockForUpdate()->first();
                    if (!$current || (float)$current->{$row['column']}!==(float)$row['after']) $this->error('Un tarif a été modifié depuis : annulation refusée pour préserver la modification plus récente.');
                    DB::table('airports')->where('id',$row['id'])->update([$row['column']=>$row['before']]);
                } else {
                    DB::table('flights')->where('id',$row['id'])->lockForUpdate()->first(); $key=['flight_id'=>$row['id'],'fare_id'=>$row['fare_id']]; $current=DB::table('flight_fare')->where($key)->first(); $pricing=DB::table('promethee_pricing')->where($key)->first();
                    if (!$current || (float)$current->price!==(float)$row['after'] || !$pricing || $pricing->band!==$row['band'] || (float)$pricing->red_price!==(float)$row['red_price']) $this->error('Une ligne a été modifiée depuis : annulation refusée.');
                    if ($row['before']) { $restore=$row['before']; unset($restore['flight_id'],$restore['fare_id']); DB::table('flight_fare')->where($key)->update($restore); } else DB::table('flight_fare')->where($key)->delete();
                    DB::table('promethee_pricing')->where($key)->delete(); if ($row['pricing_before']) DB::table('promethee_pricing')->insert($row['pricing_before']);
                }
            }
            DB::table('promethee_changes')->where('id',$id)->update(['reverted_at'=>now(),'updated_at'=>now()]);
        });
    }

    public function importPreview(array $items): array
    {
        $rows=[];
        foreach ($items as $line=>$item) {
            if ($item['target']==='fuel') {
                $airport=Airport::find($item['id']); if (!$airport) $this->error("Ligne {$line} : aéroport introuvable.");
                $column=$item['field']; $before=$airport->getRawOriginal($column); $defaults=['fuel_jeta_cost'=>'airports.default_jet_a_fuel_cost','fuel_100ll_cost'=>'airports.default_100ll_fuel_cost','fuel_mogas_cost'=>'airports.default_mogas_fuel_cost']; $effective=$before ? (float)$before : (float)setting($defaults[$column],0); $after=(float)$item['price'];
                if ($after<=0) $this->error("Ligne {$line} : prix carburant strictement positif requis.");
                $rows[]=['target'=>'fuel','id'=>$airport->id,'label'=>$airport->icao.' — '.$airport->name,'column'=>$column,'before'=>$before,'effective'=>$effective,'after'=>$after]; continue;
            }
            $flight=Flight::with('subfleets.fares')->find($item['id']); $fare=Fare::find($item['fare_id']); if (!$flight || !$fare) $this->error("Ligne {$line} : ligne ou cabine introuvable.");
            if (!$flight->subfleets()->whereHas('fares',fn($q)=>$q->where('fares.id',$fare->id))->exists()) $this->error("Ligne {$line} : cabine non disponible sur cette ligne.");
            $pivot=DB::table('flight_fare')->where(['flight_id'=>$flight->id,'fare_id'=>$fare->id])->first(); $pricing=DB::table('promethee_pricing')->where(['flight_id'=>$flight->id,'fare_id'=>$fare->id])->first(); $effective=$pivot ? (float)$pivot->price : (float)$fare->price; $band=$item['band'] ?? ($pricing->band ?? 'rouge'); $multiplier=$this->bandMultipliers()[$band]; $red=(float)$item['price'];
            $rows[]=['target'=>'ticket','id'=>$flight->id,'fare_id'=>$fare->id,'label'=>$flight->ident.' · '.$flight->dpt_airport_id.' → '.$flight->arr_airport_id,'before'=>$pivot ? (array)$pivot : null,'pricing_before'=>$pricing ? (array)$pricing : null,'effective'=>$effective,'after'=>round($red*$multiplier,2),'red_price'=>$red,'band'=>$band,'multiplier'=>$multiplier,'capacity'=>(int)($pivot->capacity ?? $fare->capacity ?? 0)];
        }
        if (!$rows) $this->error('Le fichier ne contient aucune ligne exploitable.');
        return ['input'=>['target'=>'import','source'=>'csv'],'rows'=>$rows,'impact'=>['kind'=>'import','count'=>count($rows),'message'=>'Import préparé : aucune donnée n’est modifiée avant confirmation.']];
    }
}
