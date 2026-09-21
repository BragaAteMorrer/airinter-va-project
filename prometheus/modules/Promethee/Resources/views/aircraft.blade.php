@extends('promethee::layout')
@section('title', $aircraft->registration)
@section('content')
<div class="ops-header compact">
  <div><span class="eyebrow">FICHE APPAREIL</span><h1>{{ $aircraft->registration }} @if($aircraft->subfleet) <small>« {{ $aircraft->subfleet->name }} »</small>@endif</h1><p>Dossier opérationnel, maintenance et historique de l'appareil.</p></div>
  @if($aircraft->subfleet?->airline?->promethee_logo)<img src="{{ $aircraft->subfleet->airline->promethee_logo }}" alt="{{ $aircraft->subfleet->airline->name }}" style="width:92px;max-height:56px;object-fit:contain">@endif
</div>

<section class="panel table-wrap"><table><tbody>
  <tr><th>ICAO / IATA Type</th><td>{{ $aircraft->icao ?: '—' }} / {{ $aircraft->subfleet?->type ?: '—' }}</td></tr>
  <tr><th>Compagnie / Flotte</th><td>{{ $aircraft->subfleet?->airline?->name ?: '—' }} / {{ $aircraft->subfleet?->name ?: '—' }}</td></tr>
  <tr><th>Base</th><td>{{ $aircraft->hub_id ?: '—' }}</td></tr>
  <tr><th>Statut / Situation</th><td><span class="tag">{{ $aircraft->status_label }}</span> <span class="tag">{{ $aircraft->state_label }}</span></td></tr>
  <tr><th>Localisation</th><td>{{ $aircraft->airport?->icao ?: $aircraft->airport_id ?: '—' }}</td></tr>
  <tr><th>Carburant à bord</th><td>{{ number_format($aircraft->fuel_onboard->local(0), 0, ',', ' ') }} {{ setting('units.fuel') }}</td></tr>
  <tr><th>Dernier atterrissage</th><td>{{ $aircraft->landing_time ? $aircraft->landingTime?->locale('fr')->diffForHumans() : '—' }}</td></tr>
</tbody></table></section>

<section class="panel"><div class="panel-heading"><div><span class="eyebrow">TECHNIQUE</span><h2>Maintenance</h2></div>@if($maintenance)<span class="tag">État actuel {{ number_format($maintenance->curr_state, 0) }} %</span>@endif</div>
@if($maintenance)<div class="three-columns">
  @foreach(['A' => ['rem_ta','rem_ca','last_a'], 'B' => ['rem_tb','rem_cb','last_b'], 'C' => ['rem_tc','rem_cc','last_c']] as $check => $fields)
  <div><h3>{{ $check }} Check</h3><p>Temps restant : <strong>{{ $maintenance->{$fields[0]} ?? '—' }} h</strong></p><p>Cycles restants : <strong>{{ $maintenance->{$fields[1]} ?? '—' }}</strong></p><p>Dernier contrôle : <strong>{{ $maintenance->{$fields[2]} ?? '—' }}</strong></p></div>
  @endforeach
</div><p>Dernière action : <strong>{{ $maintenance->act_note ?: 'Aucune' }}</strong></p>@else<p class="empty">Aucune donnée de maintenance disponible pour cet appareil.</p>@endif
</section>

<section class="panel table-wrap"><div class="panel-heading"><div><span class="eyebrow">HISTORIQUE</span><h2>Rapports de vols</h2></div></div><table><thead><tr><th>Numéro de vol</th><th>Départ / Arrivée</th><th>Temps de vol</th><th>Envoyé</th><th>Statut</th></tr></thead><tbody>
@forelse($pireps as $pirep)<tr><td><a href="{{ route('promethee.pirep', $pirep->id) }}">{{ $pirep->flight_number }}</a></td><td>{{ $pirep->dpt_airport_id }} / {{ $pirep->arr_airport_id }}</td><td>{{ intdiv((int) $pirep->flight_time, 60) }} h {{ (int) $pirep->flight_time % 60 }} min</td><td>{{ $pirep->submitted_at?->locale('fr')->diffForHumans() }}</td><td><span class="tag">Accepté</span></td></tr>@empty<tr><td colspan="5">Aucun rapport de vol accepté.</td></tr>@endforelse
</tbody></table></section>

<section class="panel table-wrap"><div class="panel-heading"><div><span class="eyebrow">BILAN</span><h2>Statistiques</h2></div></div><table><tbody>
 <tr><th>PIREPs acceptés</th><td>{{ number_format($stats->pireps) }}</td></tr><tr><th>Temps total porte à porte</th><td>{{ intdiv((int) $stats->flight_minutes, 60) }} h {{ (int) $stats->flight_minutes % 60 }} min</td></tr>
 <tr><th>Consommation totale</th><td>{{ number_format($stats->fuel_used->local(0), 0, ',', ' ') }} {{ setting('units.fuel') }}</td></tr><tr><th>Distance totale</th><td>{{ number_format($stats->distance->local(0), 0, ',', ' ') }} {{ setting('units.distance') }}</td></tr>
 <tr><th>Taux de touché moyen</th><td>{{ $stats->landing_rate ? number_format($stats->landing_rate, 0) . ' ft/min' : '—' }}</td></tr>
</tbody></table></section>

<section class="panel"><div class="panel-heading"><div><span class="eyebrow">RESSOURCES</span><h2>Téléchargements</h2></div></div><div class="route-list">@forelse($downloads as $file)<article><strong>{{ $file->name }}</strong>@if($file->description)<span>{{ $file->description }}</span>@endif<a class="button outline" href="{{ route('promethee.downloads.download', $file->id) }}">Télécharger</a></article>@empty<p class="empty">Aucun téléchargement lié à ce type d'appareil.</p>@endforelse</div></section>
@endsection
