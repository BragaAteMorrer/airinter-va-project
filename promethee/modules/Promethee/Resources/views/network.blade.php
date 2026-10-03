@extends('promethee::layout')
@section('title','Planification réseau')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">PLANIFICATION</span><h1>Réseau et saison.</h1><p>Indicateurs issus des PIREP acceptés : un outil de décision, pas une donnée financière réelle.</p></div><a class="button" href="{{ route('admin.promethee.seasons') }}">Gérer les saisons</a></div>
<section class="control-strip"><article><span>Équipages Hermès</span><strong id="presenceCount">{{ $presence['online_count'] ?? 0 }}</strong><small>connectés actuellement</small></article><article><span>Saison active</span><strong>{{ $season?->name ?? '—' }}</strong><small>{{ $season ? $season->starts_on.' → '.$season->ends_on : 'Aucune saison définie' }}</small></article><article><span>Lignes étudiées</span><strong>{{ $flights->count() }}</strong><small>actives et visibles</small></article><article><span>Flottes</span><strong>{{ $subfleets->count() }}</strong><small>compatibilités publiées</small></article></section>
<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">AIR INTER NETWORK</span><h2>Équipages Hermès en ligne</h2><p>Un équipage disparaît automatiquement si Hermès cesse d’envoyer son heartbeat.</p></div><span id="presenceGenerated" class="muted">{{ $presence['generated_at'] ?? '' }}</span></div>
  <table>
    <thead><tr><th>Pilote</th><th>Vol</th><th>Appareil</th><th>Phase</th><th>Simulateur</th><th>Hermès</th><th>Dernier signal</th></tr></thead>
    <tbody id="presenceRows">
      @forelse(($presence['crews'] ?? []) as $crew)
      <tr>
        <td><strong>{{ $crew['pilot']['ident'] ?? '—' }}</strong><br><small>{{ $crew['pilot']['name'] ?? '' }}</small></td>
        <td><strong>{{ $crew['flight']['ident'] ?? '—' }}</strong><br><small>{{ ($crew['flight']['departure'] ?? '—').' → '.($crew['flight']['arrival'] ?? '—') }}</small></td>
        <td>{{ $crew['aircraft']['registration'] ?? '—' }}<br><small>{{ $crew['aircraft']['icao'] ?? '' }}</small></td>
        <td>{{ $crew['phase'] ?? 'STANDBY' }}</td>
        <td>{{ strtoupper($crew['simulator'] ?? 'unknown') }}</td>
        <td>{{ $crew['hermes_version'] ?? '—' }}</td>
        <td>{{ $crew['age_seconds'] ?? 0 }} s</td>
      </tr>
      @empty
      <tr><td colspan="7">Aucun équipage Hermès connecté actuellement.</td></tr>
      @endforelse
    </tbody>
  </table>
</section>
<script>
(() => {
  const endpoint = @json(route('admin.promethee.network.presence'));
  const rows = document.getElementById('presenceRows');
  const count = document.getElementById('presenceCount');
  const generated = document.getElementById('presenceGenerated');
  const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
  const render = payload => {
    const data = payload?.data ?? payload ?? {};
    const crews = Array.isArray(data.crews) ? data.crews : [];
    count.textContent = String(data.online_count ?? crews.length);
    generated.textContent = data.generated_at ?? '';
    rows.innerHTML = crews.length ? crews.map(crew => `
      <tr>
        <td><strong>${esc(crew.pilot?.ident || '—')}</strong><br><small>${esc(crew.pilot?.name || '')}</small></td>
        <td><strong>${esc(crew.flight?.ident || '—')}</strong><br><small>${esc(crew.flight?.departure || '—')} → ${esc(crew.flight?.arrival || '—')}</small></td>
        <td>${esc(crew.aircraft?.registration || '—')}<br><small>${esc(crew.aircraft?.icao || '')}</small></td>
        <td>${esc(crew.phase || 'STANDBY')}</td>
        <td>${esc(String(crew.simulator || 'unknown').toUpperCase())}</td>
        <td>${esc(crew.hermes_version || '—')}</td>
        <td>${esc(crew.age_seconds ?? 0)} s</td>
      </tr>`).join('') : '<tr><td colspan="7">Aucun équipage Hermès connecté actuellement.</td></tr>';
  };
  const refresh = async () => {
    try {
      const response = await fetch(endpoint, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
      if (response.ok) render(await response.json());
    } catch (_) {}
  };
  setInterval(refresh, 15000);
})();
</script>
<section class="panel table-wrap"><div class="panel-heading"><div><span class="eyebrow">DEMANDE SIMULÉE</span><h2>Priorités de réseau</h2></div></div><table><thead><tr><th>Ligne</th><th>Distance</th><th>PIREPs ce mois</th><th>Historique</th><th>Indice</th><th>Recommandation</th></tr></thead><tbody>@foreach($flights as $flight)@php($index=min(100,($flight->month_pireps*18)+min(40,$flight->total_pireps)))<tr><td><strong>{{ $flight->ident }}</strong><br><small>{{ $flight->dpt_airport_id }} → {{ $flight->arr_airport_id }}</small></td><td>{{ number_format($flight->distance->toUnit('nmi'),0,',',' ') }} NM</td><td>{{ $flight->month_pireps }}</td><td>{{ $flight->total_pireps }}</td><td>{{ $index }}/100</td><td>{{ $index >= 55 ? 'Consolider la rotation' : ($index >= 20 ? 'Suivre la demande' : 'Promouvoir ou réévaluer') }}</td></tr>@endforeach</tbody></table></section>
@endsection
