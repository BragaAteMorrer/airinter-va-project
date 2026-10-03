@extends('promethee::layout')
@section('title','Santé Prométhée')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">ADMINISTRATION</span><h1>État des services.</h1><p>Contrôle des données métier et journal des actions administratives.</p></div></div>
<section class="control-strip">@foreach($tables as $name=>$count)<article><span>{{ $name }}</span><strong>{{ number_format($count,0,',',' ') }}</strong><small>enregistrements</small></article>@endforeach</section>
<section class="panel"><div class="panel-heading"><div><span class="eyebrow">ACARS</span><h2>Dernier échantillon</h2></div></div><p>{{ $latestTelemetry ? \Carbon\Carbon::parse($latestTelemetry->recorded_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i:s').' · PIREP '.$latestTelemetry->pirep_id : 'Aucune télémétrie enregistrée.' }}</p></section>
<section class="panel table-wrap"><div class="panel-heading"><div><span class="eyebrow">AUDIT</span><h2>Journal récent</h2></div></div><table><thead><tr><th>Date</th><th>Action</th><th>Cible</th><th>Acteur</th></tr></thead><tbody>@forelse($audit as $entry)<tr><td>{{ \Carbon\Carbon::parse($entry->created_at)->setTimezone('Europe/Paris')->format('d/m H:i') }}</td><td>{{ $entry->action }}</td><td>{{ $entry->subject_type }} {{ $entry->subject_id }}</td><td>{{ $entry->actor_id ?: 'système' }}</td></tr>@empty<tr><td colspan="4">Aucune action journalisée.</td></tr>@endforelse</tbody></table></section>
@endsection
