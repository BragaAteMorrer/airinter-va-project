@extends('promethee::layout')
@section('title','Transferts de flotte')

@push('styles')
<style>
.fleet-transfer-console{display:grid;gap:1.1rem}
.fleet-transfer-console .ops-header{margin-bottom:0}
.fleet-transfer-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}
.fleet-transfer-stat{padding:1rem;border:1px solid var(--border-color,#d7dce2);border-radius:12px;background:var(--panel-bg,#fff)}
.fleet-transfer-stat strong{display:block;font-size:1.55rem;line-height:1}
.fleet-transfer-stat span{display:block;margin-top:.35rem;font-size:.72rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;opacity:.65}
.fleet-transfer-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:1rem;align-items:start}
.fleet-transfer-main{display:grid;gap:1rem;min-width:0}
.fleet-transfer-sidebar{position:sticky;top:1rem;display:grid;gap:.8rem}
.fleet-transfer-toolbar{display:grid;grid-template-columns:minmax(220px,1fr) auto auto;gap:.55rem;align-items:end}
.fleet-transfer-toolbar label{min-width:0}
.fleet-transfer-toolbar input{width:100%}
.fleet-transfer-table-wrap{overflow:auto;max-height:660px}
.fleet-transfer-table{margin:0;min-width:980px}
.fleet-transfer-table thead{position:sticky;top:0;z-index:2}
.fleet-transfer-row[hidden]{display:none}
.fleet-transfer-row.is-blocked{opacity:.62}
.fleet-transfer-row.is-selected td:first-child{box-shadow:inset 3px 0 0 var(--accent,#0d5dcc)}
.fleet-transfer-registration strong,.fleet-transfer-registration small{display:block}
.fleet-transfer-registration small{margin-top:.15rem;opacity:.65}
.fleet-transfer-status{display:inline-flex;align-items:center;gap:.35rem;padding:.25rem .5rem;border:1px solid var(--border-color,#d7dce2);border-radius:999px;font-size:.72rem;font-weight:800}
.fleet-transfer-status.ok{border-color:#18864b;color:#12683b}
.fleet-transfer-status.blocked{border-color:#b42318;color:#8f1d14}
.fleet-transfer-position strong,.fleet-transfer-position small{display:block}
.fleet-transfer-position small{margin-top:.12rem;opacity:.62}
.fleet-transfer-form{display:grid;gap:.8rem}
.fleet-transfer-form label{display:grid;gap:.35rem}
.fleet-transfer-form select,.fleet-transfer-form textarea{width:100%}
.fleet-transfer-form textarea{min-height:86px;resize:vertical}
.fleet-transfer-selection{padding:.75rem;border-radius:10px;background:color-mix(in srgb,var(--panel-bg,#fff) 92%,currentColor 3%);border:1px dashed var(--border-color,#d7dce2)}
.fleet-transfer-selection strong{font-size:1.35rem}
.fleet-transfer-selection span{font-size:.8rem;opacity:.68}
.fleet-transfer-warning{padding:.75rem;border-radius:10px;border:1px solid #d0a21b;background:rgba(208,162,27,.08);font-size:.82rem;line-height:1.45}
.fleet-transfer-help{margin:0;opacity:.72;line-height:1.5}
.fleet-transfer-history{display:grid;gap:.55rem}
.fleet-transfer-history-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:.8rem;padding:.75rem .85rem;border:1px solid var(--border-color,#d7dce2);border-radius:10px}
.fleet-transfer-history-item strong,.fleet-transfer-history-item span,.fleet-transfer-history-item small{display:block}
.fleet-transfer-history-item span{margin-top:.15rem}
.fleet-transfer-history-item small{margin-top:.2rem;opacity:.62}
.fleet-transfer-empty{padding:1.2rem;text-align:center;border:1px dashed var(--border-color,#d7dce2);border-radius:10px;opacity:.65}
@media(max-width:1050px){
  .fleet-transfer-layout{grid-template-columns:1fr}
  .fleet-transfer-sidebar{position:static}
}
@media(max-width:760px){
  .fleet-transfer-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
  .fleet-transfer-toolbar{grid-template-columns:1fr 1fr}
  .fleet-transfer-toolbar label{grid-column:1/-1}
}
@media(max-width:520px){
  .fleet-transfer-stats{grid-template-columns:1fr}
  .fleet-transfer-toolbar{grid-template-columns:1fr}
  .fleet-transfer-toolbar label{grid-column:auto}
}
</style>
@endpush

@section('content')
<div class="fleet-transfer-console">
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">ADMIN · FLOTTE</span>
      <h1>Repositionnement des appareils.</h1>
      <p>Déplacez administrativement une ou plusieurs immatriculations vers un autre aéroport. La base de rattachement ne change jamais depuis cet écran.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.dashboard') }}">← Administration</a>
  </div>

  @if($errors->any())
    <section class="panel">
      <strong>Transfert refusé.</strong>
      <ul>
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </section>
  @endif

  <div class="fleet-transfer-stats" aria-label="État des transferts de flotte">
    <div class="fleet-transfer-stat"><strong>{{ count($rows) }}</strong><span>Appareils</span></div>
    <div class="fleet-transfer-stat"><strong>{{ $eligibleCount }}</strong><span>Transférables maintenant</span></div>
    <div class="fleet-transfer-stat"><strong>{{ $blockedCount }}</strong><span>Protégés / occupés</span></div>
    <div class="fleet-transfer-stat"><strong>{{ count($airports) }}</strong><span>Aéroports disponibles</span></div>
  </div>

  <form method="post"
        action="{{ route('admin.promethee.fleet-transfers.store') }}"
        id="fleetTransferForm"
        onsubmit="return confirmFleetTransfer();">
    @csrf

    <div class="fleet-transfer-layout">
      <div class="fleet-transfer-main">
        <section class="panel">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">01 · APPAREILS</span>
              <h2>Choisir les immatriculations</h2>
              <p class="fleet-transfer-help">Les appareils en vol, utilisés, réservés, engagés dans un PIREP, une mission ou une maintenance sont volontairement bloqués.</p>
            </div>
          </div>

          <div class="fleet-transfer-toolbar">
            <label>Rechercher
              <input type="search" id="fleetTransferSearch" placeholder="F-GPXC, F100, Air Inter, LFPO…" autocomplete="off">
            </label>
            <button class="button outline" type="button" id="fleetTransferSelectVisible">Sélectionner les visibles</button>
            <button class="button outline" type="button" id="fleetTransferClear">Tout effacer</button>
          </div>

          <p class="fleet-transfer-help" id="fleetTransferListStatus" aria-live="polite"></p>

          <div class="table-wrap fleet-transfer-table-wrap">
            <table class="fleet-transfer-table">
              <thead>
                <tr>
                  <th aria-label="Sélection"></th>
                  <th>Immatriculation</th>
                  <th>Compagnie / type</th>
                  <th>Position actuelle</th>
                  <th>Base (inchangée)</th>
                  <th>État</th>
                  <th>Disponibilité</th>
                </tr>
              </thead>
              <tbody>
                @foreach($rows as $row)
                  @php($plane=$row['aircraft'])
                  @php($search=mb_strtolower(implode(' ', array_filter([
                    $plane->registration,
                    $plane->icao,
                    $plane->name,
                    $plane->subfleet?->name,
                    $plane->subfleet?->airline?->name,
                    $plane->subfleet?->airline?->icao,
                    $plane->airport_id,
                    $plane->airport?->name,
                    $plane->hub_id,
                    $plane->home?->name,
                    $row['reason'],
                  ]))))
                  <tr class="fleet-transfer-row {{ $row['eligible'] ? '' : 'is-blocked' }}"
                      data-transfer-row
                      data-search="{{ $search }}"
                      data-eligible="{{ $row['eligible'] ? '1' : '0' }}">
                    <td>
                      <input type="checkbox"
                             name="aircraft_ids[]"
                             value="{{ $plane->id }}"
                             data-transfer-checkbox
                             aria-label="Sélectionner {{ $plane->registration }}"
                             {{ $row['eligible'] ? '' : 'disabled' }}>
                    </td>
                    <td class="fleet-transfer-registration">
                      <strong>{{ $plane->registration }}</strong>
                      <small>#{{ $plane->id }} · {{ $plane->icao ?: 'ICAO —' }}</small>
                    </td>
                    <td>
                      <strong>{{ $plane->subfleet?->airline?->icao ?: '—' }}</strong>
                      · {{ $plane->subfleet?->name ?: $plane->name }}
                    </td>
                    <td class="fleet-transfer-position">
                      <strong>{{ $plane->airport_id ?: '—' }}</strong>
                      <small>{{ $plane->airport?->name ?: 'Position non renseignée' }}</small>
                    </td>
                    <td class="fleet-transfer-position">
                      <strong>{{ $plane->hub_id ?: '—' }}</strong>
                      <small>{{ $plane->home?->name ?: 'Base non renseignée' }}</small>
                    </td>
                    <td>
                      {{ $row['state_label'] }}
                      <br><small>{{ $row['status_label'] }}</small>
                    </td>
                    <td>
                      @if($row['eligible'])
                        <span class="fleet-transfer-status ok">DISPONIBLE</span>
                      @else
                        <span class="fleet-transfer-status blocked">BLOQUÉ</span>
                        <br><small>{{ $row['reason'] }}</small>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="fleet-transfer-empty" id="fleetTransferEmpty" hidden>Aucun appareil ne correspond à cette recherche.</div>
        </section>
      </div>

      <aside class="fleet-transfer-sidebar">
        <section class="panel">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">02 · DESTINATION</span>
              <h2>Exécuter le transfert</h2>
            </div>
          </div>

          <div class="fleet-transfer-form">
            <div class="fleet-transfer-selection">
              <strong id="fleetTransferSelectedCount">0</strong>
              <span> appareil sélectionné</span>
            </div>

            <label>Aéroport de destination
              <select name="destination_airport_id" id="fleetTransferDestination" required>
                <option value="">Choisir un aéroport…</option>
                @foreach($airports as $airport)
                  <option value="{{ $airport->id }}">
                    {{ $airport->icao }}@if($airport->iata) / {{ $airport->iata }}@endif
                    · {{ $airport->name }}
                  </option>
                @endforeach
              </select>
            </label>

            <label>Motif / note
              <textarea name="reason" maxlength="500" placeholder="Ex. repositionnement administratif, correction de position, transfert technique…">{{ old('reason') }}</textarea>
            </label>

            <div class="fleet-transfer-warning">
              <strong>Important :</strong> seul l’aéroport actuel de l’avion est modifié. Sa base reste inchangée ; aucun pilote n’est déplacé et aucun PIREP n’est créé.
            </div>

            <button class="button" type="submit" id="fleetTransferSubmit" disabled>Transférer la sélection</button>
          </div>
        </section>
      </aside>
    </div>
  </form>

  <section class="panel">
    <div class="panel-heading">
      <div>
        <span class="eyebrow">JOURNAL</span>
        <h2>Derniers transferts administratifs</h2>
        <p class="fleet-transfer-help">Historique issu du journal d’audit Prométhée.</p>
      </div>
    </div>

    <div class="fleet-transfer-history">
      @forelse($history as $entry)
        @php($transfer=$entry->transfer ?? [])
        <div class="fleet-transfer-history-item">
          <div>
            <strong>{{ $transfer['registration'] ?? ('Appareil #'.$entry->subject_id) }}</strong>
            <span>
              {{ $transfer['from_airport'] ?? '—' }} → {{ $transfer['to_airport'] ?? '—' }}
            </span>
            @if(!empty($transfer['reason']))<small>{{ $transfer['reason'] }}</small>@endif
          </div>
          <small>{{ $entry->created_at }}</small>
        </div>
      @empty
        <div class="fleet-transfer-empty">Aucun transfert administratif enregistré pour le moment.</div>
      @endforelse
    </div>
  </section>
</div>

@push('scripts')
<script>
(() => {
  const search = document.getElementById('fleetTransferSearch');
  const rows = Array.from(document.querySelectorAll('[data-transfer-row]'));
  const checkboxes = Array.from(document.querySelectorAll('[data-transfer-checkbox]'));
  const selectVisible = document.getElementById('fleetTransferSelectVisible');
  const clear = document.getElementById('fleetTransferClear');
  const count = document.getElementById('fleetTransferSelectedCount');
  const status = document.getElementById('fleetTransferListStatus');
  const empty = document.getElementById('fleetTransferEmpty');
  const destination = document.getElementById('fleetTransferDestination');
  const submit = document.getElementById('fleetTransferSubmit');

  function selectedCount() {
    return checkboxes.filter(input => input.checked).length;
  }

  function refreshSelection() {
    const total = selectedCount();
    count.textContent = String(total);
    checkboxes.forEach(input => input.closest('tr')?.classList.toggle('is-selected', input.checked));
    submit.disabled = total === 0 || !destination.value;
  }

  function refreshFilter() {
    const term = (search.value || '').trim().toLocaleLowerCase('fr');
    let visible = 0;
    let eligibleVisible = 0;

    rows.forEach(row => {
      const matches = term === '' || (row.dataset.search || '').includes(term);
      row.hidden = !matches;
      if (matches) {
        visible++;
        if (row.dataset.eligible === '1') eligibleVisible++;
      }
    });

    empty.hidden = visible !== 0;
    status.textContent = visible + ' appareil' + (visible > 1 ? 's' : '')
      + ' affiché' + (visible > 1 ? 's' : '')
      + ' · ' + eligibleVisible + ' transférable' + (eligibleVisible > 1 ? 's' : '');
  }

  search.addEventListener('input', refreshFilter);
  destination.addEventListener('change', refreshSelection);
  checkboxes.forEach(input => input.addEventListener('change', refreshSelection));

  selectVisible.addEventListener('click', () => {
    rows.forEach(row => {
      if (!row.hidden && row.dataset.eligible === '1') {
        const checkbox = row.querySelector('[data-transfer-checkbox]');
        if (checkbox) checkbox.checked = true;
      }
    });
    refreshSelection();
  });

  clear.addEventListener('click', () => {
    checkboxes.forEach(input => input.checked = false);
    refreshSelection();
  });

  window.confirmFleetTransfer = function () {
    const total = selectedCount();
    const airport = destination.value;
    if (!total || !airport) return false;
    return confirm(
      'Repositionner ' + total + ' appareil' + (total > 1 ? 's' : '')
      + ' vers ' + airport + ' ?'
      + '\n\nLa base de rattachement restera inchangée. Les appareils devenus occupés entre-temps seront protégés côté serveur.'
    );
  };

  refreshFilter();
  refreshSelection();
})();
</script>
@endpush
@endsection
