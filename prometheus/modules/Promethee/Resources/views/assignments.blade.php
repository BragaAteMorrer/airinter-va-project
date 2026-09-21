@extends('promethee::layout')
@section('title','Mes affectations')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">PROGRAMME PILOTE</span><h1>Mes affectations.</h1><p>Objectifs de {{ \Carbon\Carbon::createFromFormat('Y-m',$month)->locale('fr')->translatedFormat('F Y') }}. Un PIREP accepté sur la ligne valide automatiquement l’objectif.</p></div><span class="tag">{{ $assignments->where('completed',true)->count() }}/{{ $assignments->count() }} réalisées</span></div>
<section class="panel table-wrap"><table><thead><tr><th>État</th><th>Vol</th><th>Ligne</th><th>Note</th></tr></thead><tbody>@forelse($assignments as $assignment)<tr><td><span class="tag">{{ $assignment->completed ? 'Réalisée' : 'À effectuer' }}</span></td><td><strong>{{ $assignment->route_code ?: $assignment->flight_number }}</strong></td><td>{{ $assignment->dpt_airport_id }} → {{ $assignment->arr_airport_id }}</td><td>{{ $assignment->notes ?: '—' }}</td></tr>@empty<tr><td colspan="4">Aucune affectation pour ce mois.</td></tr>@endforelse</tbody></table></section>
@endsection
