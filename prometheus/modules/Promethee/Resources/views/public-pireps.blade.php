@extends('promethee::layout')
@php($mine = $mine ?? false)
@section('title', $mine ? 'Mes PIREPs' : 'Tous les PIREPs')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">AIR INTER · RAPPORTS DE VOL</span><h1>{{ $mine ? 'Mes PIREPs.' : 'Tous les PIREPs.' }}</h1><p>{{ $mine ? 'Retrouvez uniquement vos rapports de vol validés.' : 'Consultez les rapports validés de la compagnie.' }}</p></div></div>

<section class="panel">
    <div class="panel-heading">
        <div><span class="eyebrow">RAPPORTS DE VOL</span><h2>Choisir une vue</h2></div>
        <div>
            <a @class(['button', 'outline' => $mine]) href="{{ route('promethee.public.pireps') }}">Tous les rapports</a>
            <a @class(['button', 'outline' => !$mine]) href="{{ route('promethee.public.pireps.mine') }}">Mes rapports</a>
        </div>
    </div>
</section>

<form class="panel filters" method="get">
    @unless($mine)<label>Pilote<input name="pilot" value="{{ request('pilot') }}" placeholder="Nom ou matricule"></label>@endunless
    <label>Vol<input name="flight" value="{{ request('flight') }}" placeholder="ITF802"></label>
    <label>Départ<input name="departure" value="{{ request('departure') }}" placeholder="LFPO"></label>
    <label>Arrivée<input name="arrival" value="{{ request('arrival') }}" placeholder="LFST"></label>
    <label>Appareil<input name="aircraft" value="{{ request('aircraft') }}" placeholder="Immat. ou ICAO"></label>
    <label>Du<input type="date" name="from" value="{{ request('from') }}"></label>
    <label>Au<input type="date" name="to" value="{{ request('to') }}"></label>
    <button type="submit">Filtrer</button>
    <a class="button outline" href="{{ route($mine ? 'promethee.public.pireps.mine' : 'promethee.public.pireps') }}">Réinitialiser</a>
</form>

<section class="panel table-wrap">
    <div class="panel-heading"><div><span class="eyebrow">OPÉRATIONS</span><h2>{{ $mine ? 'Mes vols réalisés' : 'Vols réalisés' }}</h2></div><span class="tag">{{ $pireps->total() }} résultat(s)</span></div>
    <table>
        <thead><tr><th>Vol</th><th>Pilote</th><th>Itinéraire</th><th>Appareil</th><th>Durée</th><th>Atterrissage</th><th>Soumis</th><th></th></tr></thead>
        <tbody>
        @forelse($pireps as $pirep)
            <tr>
                <td><strong>{{ $pirep->ident }}</strong></td>
                <td>{{ $pirep->user?->pilot_id }} · {{ $pirep->user?->name }}</td>
                <td>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }}</td>
                <td>{{ $pirep->aircraft?->registration ?: '—' }}</td>
                <td>{{ floor($pirep->flight_time / 60) }} h {{ $pirep->flight_time % 60 }} min</td>
                <td>{{ $pirep->landing_rate !== null ? $pirep->landing_rate.' ft/min' : '—' }}</td>
                <td>{{ optional($pirep->submitted_at)->diffForHumans() }}</td>
                <td><a class="button outline" href="{{ route('promethee.pireps.show', $pirep->id) }}">Consulter</a></td>
            </tr>
        @empty
            <tr><td colspan="8">{{ $mine ? 'Vous n’avez aucun rapport correspondant à ces filtres.' : 'Aucun rapport ne correspond à ces filtres.' }}</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
{{ $pireps->links('pagination::bootstrap-4') }}
@endsection
