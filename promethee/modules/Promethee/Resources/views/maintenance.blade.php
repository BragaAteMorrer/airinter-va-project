@extends('promethee::layout')
@section('title', 'Maintenance')

@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">AIR INTER · TECHNIQUE</span>
        <h1>Maintenance de la flotte.</h1>
        <p>Appareils en intervention ou dont une échéance de maintenance approche.</p>
    </div>
    <span class="tag">{{ number_format($maintenance->total()) }} à surveiller</span>
</div>

<section class="control-strip">
    <article>
        <span>Suivi technique</span>
        <strong>{{ number_format($maintenance->total()) }}</strong>
        <small>appareil(s) dans la file maintenance</small>
    </article>
    <article>
        <span>Visites proches</span>
        <strong>{{ count($upcomingMaintenance) }}</strong>
        <small>dans {{ $warningHours }} h ou {{ $warningCycles }} cycle(s)</small>
    </article>
    <article>
        <span>Potentiel moteurs</span>
        <strong>{{ count($engineWarnings) }}</strong>
        <small>échéance(s) TBO à planifier</small>
    </article>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">CELLULE · SUIVI ACTIF</span>
            <h2>Appareils en maintenance</h2>
        </div>
        <span class="tag">{{ number_format($maintenance->total()) }} ligne(s)</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Appareil</th><th>Compagnie</th><th>État</th><th>Intervention</th><th>Site technique</th>
                    <th>Temps A restant</th><th>Temps B restant</th><th>Temps C restant</th><th>Cycles A</th><th>Cycles B</th><th>Cycles C</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenance as $item)
                    <tr>
                        <td><a href="{{ route('promethee.aircraft.show', $item->registration) }}"><strong>{{ $item->registration }}</strong></a> · {{ $item->icao }}</td>
                        <td>{{ $item->airline_icao ?: '—' }}@if($item->airline_name) · {{ $item->airline_name }}@endif</td>
                        <td><span class="tag">{{ number_format($item->curr_state, 0) }} %</span></td>
                        <td>{{ $item->act_note ?: 'À planifier' }}</td>
                        <td>
                            <strong>{{ $item->airport_id ?: '—' }}</strong><br>
                            @if($item->heavy_maintenance)<span class="tag">PETITE + GROSSE</span>
                            @elseif($item->small_maintenance)<span class="tag">PETITE</span>
                            @else<span class="tag">TRANSFERT TECHNIQUE REQUIS</span>@endif
                        </td>
                        <td>{{ is_numeric($item->rem_ta) ? number_format($item->rem_ta / 60, 1, ',', ' ') . ' h' : '—' }}</td><td>{{ is_numeric($item->rem_tb) ? number_format($item->rem_tb / 60, 1, ',', ' ') . ' h' : '—' }}</td><td>{{ is_numeric($item->rem_tc) ? number_format($item->rem_tc / 60, 1, ',', ' ') . ' h' : '—' }}</td>
                        <td>{{ $item->rem_ca ?? '—' }}</td><td>{{ $item->rem_cb ?? '—' }}</td><td>{{ $item->rem_cc ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="11">Aucune opération de maintenance ni échéance proche.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

{{ $maintenance->links('pagination::bootstrap-4') }}

<section class="panel">
    <div class="panel-heading">
        <div><span class="eyebrow">PROCHAINES ÉCHÉANCES</span><h2>Appareils approchant une visite</h2></div>
        <p>Seuil : {{ $warningHours }} h ou {{ $warningCycles }} cycle(s) restant(s).</p>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Appareil</th><th>Compagnie</th><th>Position / capacité</th><th>Heures A/B/C restantes</th><th>Cycles A/B/C restants</th></tr></thead>
            <tbody>
                @forelse($upcomingMaintenance as $item)
                    <tr>
                        <td><a href="{{ route('promethee.aircraft.show', $item->registration) }}"><strong>{{ $item->registration }}</strong></a> · {{ $item->icao }}</td>
                        <td>{{ $item->airline_icao ?: '—' }}</td>
                        <td><strong>{{ $item->airport_id ?: '—' }}</strong> · @if($item->heavy_maintenance) petite + grosse maintenance @elseif($item->small_maintenance) petite maintenance @else aucune capacité technique @endif</td>
                        <td>{{ is_numeric($item->rem_ta) ? number_format($item->rem_ta / 60, 1, ',', ' ') : '—' }} / {{ is_numeric($item->rem_tb) ? number_format($item->rem_tb / 60, 1, ',', ' ') : '—' }} / {{ is_numeric($item->rem_tc) ? number_format($item->rem_tc / 60, 1, ',', ' ') : '—' }} h</td>
                        <td>{{ $item->rem_ca ?? '—' }} / {{ $item->rem_cb ?? '—' }} / {{ $item->rem_cc ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aucun appareil dans la fenêtre d’alerte configurée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <div><span class="eyebrow">MOTEURS</span><h2>Échéances TBO</h2></div>
        <p>Suivi indépendant des checks cellule A/B/C.</p>
    </div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Appareil</th><th>Moteur</th><th>Position</th><th>Depuis révision</th><th>Restant</th><th>État</th></tr></thead>
            <tbody>
                @forelse($engineWarnings as $engine)
                    <tr>
                        <td><a href="{{ route('promethee.aircraft.show', $engine->registration) }}"><strong>{{ $engine->registration }}</strong></a> · {{ $engine->airline_icao ?: '—' }}</td>
                        <td>{{ $engine->engine_type }}<br><small>{{ $engine->serial_number }}</small></td>
                        <td>M{{ $engine->position }} · {{ $engine->airport_id ?: '—' }}</td>
                        <td>{{ number_format($engine->hours_since_overhaul, 1, ',', ' ') }} h / {{ number_format($engine->cycles_since_overhaul) }} cycles</td>
                        <td>{{ $engine->remaining_hours !== null ? number_format($engine->remaining_hours, 1, ',', ' ') . ' h' : '—' }} / {{ $engine->remaining_cycles !== null ? number_format($engine->remaining_cycles) . ' cycles' : '—' }}</td>
                        <td><span class="tag">{{ $engine->status === 'due' ? 'TBO ATTEINT' : 'À PLANIFIER' }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucune échéance moteur dans la fenêtre d’alerte.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
