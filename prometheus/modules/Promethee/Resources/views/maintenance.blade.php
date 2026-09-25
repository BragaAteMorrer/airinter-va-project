@extends('promethee::layout')
@section('title', 'Maintenance')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">AIR INTER · TECHNIQUE</span><h1>Maintenance de la flotte.</h1><p>Appareils en intervention ou dont une échéance de maintenance approche.</p></div><span class="tag">{{ number_format($maintenance->total()) }} à surveiller</span></div>
<section class="panel table-wrap"><table><thead><tr><th>Appareil</th><th>Compagnie</th><th>État</th><th>Intervention</th><th>Site technique</th><th>Temps A restant</th><th>Temps B restant</th><th>Temps C restant</th><th>Cycles A</th><th>Cycles B</th><th>Cycles C</th></tr></thead><tbody>@forelse($maintenance as $item)<tr><td><a href="{{ route('promethee.aircraft.show', $item->registration) }}"><strong>{{ $item->registration }}</strong></a> · {{ $item->icao }}</td><td>{{ $item->airline_icao ?: '—' }}@if($item->airline_name) · {{ $item->airline_name }}@endif</td><td><span class="tag">{{ number_format($item->curr_state, 0) }} %</span></td><td>{{ $item->act_note ?: 'À planifier' }}</td><td><strong>{{ $item->airport_id ?: '—' }}</strong><br>@if($item->heavy_maintenance)<span class="tag">PETITE + GROSSE</span>@elseif($item->small_maintenance)<span class="tag">PETITE</span>@else<span class="tag">TRANSFERT TECHNIQUE REQUIS</span>@endif</td><td>{{ $item->rem_ta ?? '—' }} h</td><td>{{ $item->rem_tb ?? '—' }} h</td><td>{{ $item->rem_tc ?? '—' }} h</td><td>{{ $item->rem_ca ?? '—' }}</td><td>{{ $item->rem_cb ?? '—' }}</td><td>{{ $item->rem_cc ?? '—' }}</td></tr>@empty<tr><td colspan="11">Aucune opération de maintenance ni échéance proche.</td></tr>@endforelse</tbody></table></section>{{ $maintenance->links('pagination::bootstrap-4') }}
<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">PROCHAINES ÉCHÉANCES</span><h2>Appareils approchant une visite</h2></div><p>Seuil beta : {{ $warningHours }} h ou {{ $warningCycles }} cycle(s) restant(s).</p></div>
  <table><thead><tr><th>Appareil</th><th>Compagnie</th><th>Position / capacité</th><th>Heures A/B/C restantes</th><th>Cycles A/B/C restants</th></tr></thead>
  <tbody>
  @forelse($upcomingMaintenance as $item)
    <tr><td><a href="{{ route('promethee.aircraft.show', $item->registration) }}"><strong>{{ $item->registration }}</strong></a> · {{ $item->icao }}</td><td>{{ $item->airline_icao ?: '—' }}</td><td><strong>{{ $item->airport_id ?: '—' }}</strong> · @if($item->heavy_maintenance)petite + grosse maintenance@elseif($item->small_maintenance)petite maintenance@elseaucune capacité technique@endif</td><td>{{ $item->rem_ta ?? '—' }} / {{ $item->rem_tb ?? '—' }} / {{ $item->rem_tc ?? '—' }} h</td><td>{{ $item->rem_ca ?? '—' }} / {{ $item->rem_cb ?? '—' }} / {{ $item->rem_cc ?? '—' }}</td></tr>
  @empty
    <tr><td colspan="5">Aucun appareil dans la fenêtre d’alerte configurée.</td></tr>
  @endforelse
  </tbody></table>
</section>
@endsection
