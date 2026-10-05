@extends('promethee::layout')
@section('title','Flotte technique')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
<style>
.airframe-console{display:grid;gap:1.25rem}
.airframe-console .ops-header{margin-bottom:0}
.airframe-console .airframe-intro{max-width:78ch}
.airframe-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}
.airframe-stat{border:1px solid var(--border-color,#d7dce2);border-radius:12px;padding:.9rem 1rem;background:var(--panel-bg,transparent)}
.airframe-stat strong{display:block;font-size:1.45rem;line-height:1.1}
.airframe-stat span{display:block;margin-top:.25rem;font-size:.78rem;opacity:.7;text-transform:uppercase;letter-spacing:.06em}
.airframe-tabs{display:flex;gap:.45rem;flex-wrap:wrap;padding:.35rem;border:1px solid var(--border-color,#d7dce2);border-radius:12px}
.airframe-tab{appearance:none;border:0;border-radius:9px;background:transparent;color:inherit;padding:.72rem 1rem;font:inherit;font-weight:700;cursor:pointer}
.airframe-tab[aria-selected="true"]{background:var(--panel-bg,#fff);box-shadow:0 1px 8px rgba(0,0,0,.08)}
.airframe-panel[hidden]{display:none!important}
.airframe-panel{display:grid;gap:1rem}
.airframe-toolbar{display:flex;gap:.65rem;align-items:end;flex-wrap:wrap}
.airframe-toolbar label{min-width:min(100%,320px);flex:1}
.airframe-toolbar input,.airframe-toolbar select{width:100%}
.airframe-help{margin:.2rem 0 0;opacity:.72;font-size:.92rem}
.airframe-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.8rem 1rem}
.airframe-form-grid>label,.airframe-form-grid>div{min-width:0}
.airframe-form-grid input,.airframe-form-grid select,.airframe-form-grid textarea{width:100%}
.airframe-span-2{grid-column:1/-1}
.airframe-actions{display:flex;gap:.55rem;flex-wrap:wrap;align-items:center}
.airframe-secondary{appearance:none;border:1px solid var(--border-color,#d7dce2);background:transparent;color:inherit;border-radius:8px;padding:.55rem .75rem;cursor:pointer;font:inherit}
.airframe-advanced,.airframe-editor{border:1px solid var(--border-color,#d7dce2);border-radius:10px;padding:.15rem .85rem}
.airframe-advanced>summary,.airframe-editor>summary{cursor:pointer;font-weight:700;padding:.75rem 0}
.airframe-advanced[open],.airframe-editor[open]{padding-bottom:.85rem}
.airframe-advanced fieldset{margin:.75rem 0 0}
.airframe-editor+.airframe-editor{margin-top:.75rem}
.airframe-editor form{margin-top:.5rem}
.airframe-table-wrap{max-height:520px;overflow:auto}
.airframe-table-wrap table{margin:0}
.airframe-table-wrap thead{position:sticky;top:0;z-index:1}
.airframe-fleet-row{cursor:pointer}
.airframe-fleet-row td{transition:background-color .12s ease,border-color .12s ease}
.airframe-fleet-row:hover td,.airframe-fleet-row:focus td{background:var(--panel-bg,#fff)}
.airframe-fleet-row:focus{outline:2px solid currentColor;outline-offset:-2px}
.airframe-fleet-row.is-selected td:first-child{border-left:3px solid currentColor}
.airframe-selection-summary{margin:0;padding:.7rem .85rem;border:1px dashed var(--border-color,#d7dce2);border-radius:9px}
.airframe-assignment-select{min-height:15rem}
.airframe-subtitle{margin:0}
.airframe-empty{display:none;padding:1rem;text-align:center;opacity:.7}
.airframe-empty.is-visible{display:block}
@media (max-width:900px){
  .airframe-stats{grid-template-columns:repeat(2,minmax(0,1fr))}
  .airframe-form-grid{grid-template-columns:1fr}
  .airframe-span-2{grid-column:auto}
}
@media (max-width:560px){
  .airframe-stats{grid-template-columns:1fr}
  .airframe-tabs{display:grid;grid-template-columns:1fr 1fr}
}
</style>
@endpush

@section('content')
@php
  $configuredAircraft = collect($aircraft)->filter(fn($row) => !empty($row['resolved']['variant']['name']) || !empty($row['resolved']['configuration']['name']))->count();
  $fleetSearchIndex = collect($aircraft)->map(function ($row) {
      $resolved = $row['resolved'];

      return implode(' ', array_filter([
          $row['model']->registration,
          $row['model']->name,
          $resolved['aircraft']['icao'] ?? null,
          $resolved['aircraft']['type_name'] ?? null,
          $resolved['variant']['code'] ?? null,
          $resolved['variant']['name'] ?? null,
          $resolved['configuration']['code'] ?? null,
          $resolved['configuration']['name'] ?? null,
      ]));
  })->join(' ');
@endphp

<div class="airframe-console" data-airframe-console>
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">FLOTTE · AIRFRAMES</span>
      <h1>Configurer la flotte, sans se perdre.</h1>
      <p class="airframe-intro">Commencez par une immatriculation et sa variante réelle. Les réglages SimBrief, masses, moteurs et sources restent disponibles, mais uniquement quand vous en avez besoin.</p>
    </div>
  </div>

  <div class="airframe-stats" aria-label="Résumé de la flotte technique">
    <div class="airframe-stat"><strong>{{ count($aircraft) }}</strong><span>Immatriculations</span></div>
    <div class="airframe-stat"><strong>{{ $configuredAircraft }}</strong><span>Déjà configurées</span></div>
    <div class="airframe-stat"><strong>{{ count($variants) }}</strong><span>Variantes réelles</span></div>
    <div class="airframe-stat"><strong>{{ count($configurations) }}</strong><span>Configurations</span></div>
  </div>

  <div class="admin-master-detail"
       id="airframes-workspace"
       data-admin-master-detail
       data-workspace-key="airframes"
       data-master-default="fleet">
    <aside class="admin-master-pane" aria-label="Gestion des airframes">
      <div class="admin-master-toolbar">
        <label>Rechercher une section ou un appareil
          <input type="search" data-master-filter data-airframe-global-search placeholder="Flotte, A340, F-IOCA, SimBrief…">
        </label>
      </div>
      <div class="admin-master-list" role="tablist" aria-orientation="vertical">
        <button type="button" class="admin-master-row" data-master-target="fleet" data-master-search="flotte immatriculations affectation variante configuration {{ $fleetSearchIndex }}">
          <span class="admin-master-row-main">
            <strong>Flotte</strong>
            <small>{{ count($aircraft) }} immatriculation(s) · {{ $configuredAircraft }} configurée(s)</small>
          </span>
          <span class="tag">OPÉRATIONNEL</span>
        </button>
        <button type="button" class="admin-master-row" data-master-target="catalog" data-master-search="catalogue avion types variantes configurations simbrief">
          <span class="admin-master-row-main">
            <strong>Catalogue avion</strong>
            <small>{{ count($variants) }} variante(s) · {{ count($configurations) }} configuration(s)</small>
          </span>
          <span class="tag">RÉFÉRENTIEL</span>
        </button>
        <button type="button" class="admin-master-row" data-master-target="simulator" data-master-search="simulateur addons hermes profils télémétrie">
          <span class="admin-master-row-main">
            <strong>Simulateur</strong>
            <small>Reconnaissance des addons Hermès</small>
          </span>
          <span class="tag">HERMÈS</span>
        </button>
        <button type="button" class="admin-master-row" data-master-target="history" data-master-search="historique modifications cabine moteur équipement">
          <span class="admin-master-row-main">
            <strong>Historique</strong>
            <small>Évolutions documentées de la flotte</small>
          </span>
        </button>
        <div class="admin-master-empty" data-master-empty hidden>Aucune section ne correspond.</div>
      </div>
    </aside>

    <div class="admin-detail-pane">
      <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>

  <section class="airframe-panel admin-detail-panel" role="tabpanel" data-airframe-panel="fleet" data-detail-panel="fleet">
    <section class="panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">FLOTTE</span>
          <h2>État des immatriculations</h2>
          <p class="airframe-help">Recherchez une immatriculation ou un type. Le tableau montre uniquement le résultat final utilisé par Prométhée et Hermès.</p>
        </div>
      </div>

      <div class="airframe-toolbar">
        <label>Rechercher un appareil
          <input type="search" id="airframeFleetSearch" placeholder="F-GPMA, A319, Nord 262…" autocomplete="off">
        </label>
      </div>
      <p class="airframe-help" id="airframeFleetStatus" aria-live="polite"></p>

      <div class="table-wrap airframe-table-wrap">
        <table>
          <thead>
            <tr>
              <th>Immatriculation</th>
              <th>Type</th>
              <th>Variante réelle</th>
              <th>Configuration</th>
              <th>PAX</th>
              <th>SimBrief</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="airframeFleetRows">
          @foreach($aircraft as $row)
            @php($r=$row['resolved'])
            <tr class="airframe-fleet-row"
                data-aircraft-id="{{ $row['model']->id }}"
                data-variant-id="{{ $r['variant']['id'] ?? '' }}"
                data-configuration-id="{{ $r['configuration']['id'] ?? '' }}"
                tabindex="0">
              <td><strong>{{ $row['model']->registration }}</strong></td>
              <td>{{ $r['aircraft']['type_name'] }}</td>
              <td>{{ $r['variant']['name'] ?? '—' }}</td>
              <td>{{ $r['configuration']['name'] ?? '—' }}</td>
              <td>{{ $r['effective']['max_pax'] ?? '—' }}</td>
              <td>{{ $r['simbrief']['strategy'] }} · {{ $r['simbrief']['value'] ?? 'AUTO' }}</td>
              <td><button type="button" class="airframe-secondary" data-airframe-configure="{{ $row['model']->id }}">Configurer</button></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="airframe-empty" id="airframeFleetEmpty">Aucune immatriculation ne correspond à cette recherche.</div>
    </section>

    <section class="panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">AFFECTATION</span>
          <h2>Configurer une ou plusieurs immatriculations</h2>
          <p class="airframe-help">Dans la majorité des cas, vous n'avez besoin que de trois choses : les appareils, leur variante réelle et éventuellement leur configuration cabine.</p>
        </div>
      </div>

      <form method="post" action="{{ route('admin.promethee.airframes.assign') }}" class="stack" id="airframeAssignmentForm">
        @csrf
        <div class="airframe-form-grid">
          <div class="airframe-span-2">
            <label for="airframeAircraftSearch">1. Trouver les immatriculations</label>
            <input type="search" id="airframeAircraftSearch" placeholder="Filtrer la liste : F-GPM, A320, Mercure…">
          </div>

          <label class="airframe-span-2">2. Sélectionner les appareils
            <select class="airframe-assignment-select" id="airframeAircraftSelect" name="aircraft_ids[]" multiple size="10" required>
              @foreach($aircraft as $row)
                <option value="{{ $row['model']->id }}"
                        data-variant-id="{{ $row['resolved']['variant']['id'] ?? '' }}"
                        data-configuration-id="{{ $row['resolved']['configuration']['id'] ?? '' }}">{{ $row['model']->registration }} · {{ $row['model']->name }}</option>
              @endforeach
            </select>
          </label>

          <div class="airframe-actions airframe-span-2">
            <button class="airframe-secondary" type="button" id="airframeSelectVisible">Sélectionner les résultats visibles</button>
            <button class="airframe-secondary" type="button" id="airframeClearSelection">Effacer la sélection</button>
          </div>
          <p class="airframe-selection-summary airframe-span-2" id="airframeSelectionSummary" aria-live="polite">Aucune immatriculation sélectionnée.</p>

          <label>3. Variante réelle
            <select name="variant_id" id="airframeVariantSelect" required>
              <option value="">Choisir une variante…</option>
              @foreach($variants as $variant)
                <option value="{{ $variant->id }}">{{ $variant->type_key }} · {{ $variant->name }}</option>
              @endforeach
            </select>
          </label>

          <label>4. Configuration cabine
            <select name="configuration_id" id="airframeConfigurationSelect">
              <option value="">Aucune configuration spécifique</option>
              @foreach($configurations as $config)
                <option value="{{ $config->id }}" data-variant-id="{{ $config->variant_id }}">{{ $config->variant?->code }} · {{ $config->name }}</option>
              @endforeach
            </select>
          </label>
        </div>

        <details class="airframe-advanced">
          <summary>Période d'application</summary>
          <div class="airframe-form-grid">
            <label>Début d'application <input type="date" name="valid_from"></label>
            <label>Fin d'application <input type="date" name="valid_until"></label>
          </div>
        </details>

        <details class="airframe-advanced">
          <summary>Remplacer exceptionnellement les données techniques</summary>
          <p class="airframe-help">Laissez vide pour hériter automatiquement de la variante ou de la configuration.</p>
          @include('promethee::admin.partials.airframe-technical-fields')
        </details>

        <details class="airframe-advanced">
          <summary>Source et traçabilité</summary>
          @include('promethee::admin.partials.airframe-source-fields')
        </details>

        <button class="button" type="submit">Appliquer aux immatriculations</button>
      </form>
    </section>
  </section>

  <section class="airframe-panel admin-detail-panel" role="tabpanel" data-airframe-panel="catalog" data-detail-panel="catalog" hidden>
    <section class="panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">CATALOGUE AVION</span>
          <h2>Types, variantes et configurations</h2>
          <p class="airframe-help">Ces écrans servent à créer les références réutilisables. Pour affecter une référence à un avion, revenez dans l'onglet Flotte.</p>
        </div>
      </div>

      <details class="airframe-editor" open>
        <summary>Variante réelle — ex. Nord 262A, A320-211</summary>
        <form method="post" action="{{ route('admin.promethee.airframes.variants.save') }}" class="stack">
          @csrf
          <div class="airframe-form-grid">
            <label>Type
              <select name="type_key" required>
                @foreach($types as $type)<option value="{{ $type->type_key }}">{{ $type->type_key }} · {{ $type->name }}</option>@endforeach
              </select>
            </label>
            <label>Code <input name="code" required placeholder="N262A"></label>
            <label class="airframe-span-2">Nom <input name="name" required placeholder="Nord 262A"></label>
          </div>

          <details class="airframe-advanced">
            <summary>Détails historiques</summary>
            <div class="airframe-form-grid">
              <label>Variante constructeur <input name="manufacturer_variant"></label>
              <label>Variante opérateur <input name="operator_variant"></label>
              <label>ICAO <input name="icao_type" maxlength="16"></label>
              <span></span>
              <label>Valide depuis <input type="date" name="valid_from"></label>
              <label>Valide jusqu'au <input type="date" name="valid_until"></label>
            </div>
          </details>

          <details class="airframe-advanced">
            <summary>SimBrief</summary>
            @include('promethee::admin.partials.airframe-simbrief-fields')
          </details>
          <details class="airframe-advanced">
            <summary>Données techniques</summary>
            @include('promethee::admin.partials.airframe-technical-fields')
          </details>
          <details class="airframe-advanced">
            <summary>Source et traçabilité</summary>
            @include('promethee::admin.partials.airframe-source-fields')
          </details>

          <input type="hidden" name="active" value="1">
          <button class="button" type="submit">Enregistrer la variante</button>
        </form>
      </details>

      <details class="airframe-editor">
        <summary>Configuration — ex. cabine modernisée, haute densité, phase 2</summary>
        <form method="post" action="{{ route('admin.promethee.airframes.configurations.save') }}" class="stack">
          @csrf
          <div class="airframe-form-grid">
            <label>Variante
              <select name="variant_id" required>
                @foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->type_key }} · {{ $variant->name }}</option>@endforeach
              </select>
            </label>
            <label>Code <input name="code" required placeholder="AIR_INTER_PHASE_2"></label>
            <label class="airframe-span-2">Nom <input name="name" required placeholder="Air Inter configuration modernisée"></label>
            <label>Nature
              <select name="configuration_kind">
                <option value="historical">Historique documentée</option>
                <option value="VA_operational">Air Inter VA opérationnelle</option>
              </select>
            </label>
            <label>Phase / standard <input name="phase" placeholder="Phase 2"></label>
          </div>

          <details class="airframe-advanced">
            <summary>Dates de validité</summary>
            <div class="airframe-form-grid">
              <label>Valide depuis <input type="date" name="valid_from"></label>
              <label>Valide jusqu'au <input type="date" name="valid_until"></label>
            </div>
          </details>
          <details class="airframe-advanced">
            <summary>SimBrief</summary>
            @include('promethee::admin.partials.airframe-simbrief-fields')
          </details>
          <details class="airframe-advanced">
            <summary>Données techniques</summary>
            @include('promethee::admin.partials.airframe-technical-fields')
          </details>
          <details class="airframe-advanced">
            <summary>Source et traçabilité</summary>
            @include('promethee::admin.partials.airframe-source-fields')
          </details>

          <input type="hidden" name="active" value="1">
          <button class="button" type="submit">Enregistrer la configuration</button>
        </form>
      </details>

      <details class="airframe-editor">
        <summary>Type avion — à utiliser seulement si la famille n'existe pas encore</summary>
        <form method="post" action="{{ route('admin.promethee.airframes.types.save') }}" class="stack">
          @csrf
          <div class="airframe-form-grid">
            <label>Clé type <input name="type_key" required placeholder="N262"></label>
            <label>Nom <input name="name" required placeholder="Nord 262"></label>
          </div>
          <details class="airframe-advanced">
            <summary>SimBrief</summary>
            @include('promethee::admin.partials.airframe-simbrief-fields')
          </details>
          <details class="airframe-advanced">
            <summary>Données techniques</summary>
            @include('promethee::admin.partials.airframe-technical-fields')
          </details>
          <details class="airframe-advanced">
            <summary>Source et traçabilité</summary>
            @include('promethee::admin.partials.airframe-source-fields')
          </details>
          <button class="button" type="submit">Enregistrer le type</button>
        </form>
      </details>

      <details class="airframe-editor">
        <summary>Dupliquer rapidement une variante existante</summary>
        <div class="stack">
          @foreach($variants as $variant)
            <form method="post" action="{{ route('admin.promethee.airframes.variants.duplicate',$variant) }}" class="inline-form">
              @csrf
              <strong>{{ $variant->code }}</strong>
              <input name="code" required placeholder="Nouveau code">
              <input name="name" required placeholder="Nouveau nom">
              <button type="submit">Dupliquer</button>
            </form>
          @endforeach
        </div>
      </details>
    </section>
  </section>

  <section class="airframe-panel admin-detail-panel" role="tabpanel" data-airframe-panel="simulator" data-detail-panel="simulator" hidden>
    <section class="panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">SIMULATEUR</span>
          <h2>Associer un addon à une variante</h2>
          <p class="airframe-help">Ici, on décrit uniquement comment Hermès reconnaît l'appareil simulé. Cela ne change jamais la variante historique réelle.</p>
        </div>
      </div>

      <form method="post" action="{{ route('admin.promethee.airframes.simulator-profiles.save') }}" class="stack">
        @csrf
        <div class="airframe-form-grid">
          <label>Variante réelle
            <select name="variant_id" required>
              @foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->code }} · {{ $variant->name }}</option>@endforeach
            </select>
          </label>
          <label>Configuration
            <select name="configuration_id">
              <option value="">Toutes les configurations</option>
              @foreach($configurations as $config)<option value="{{ $config->id }}">{{ $config->name }}</option>@endforeach
            </select>
          </label>
          <label>Simulateur
            <select name="simulator">
              <option>msfs2024</option><option>msfs2020</option><option>xplane</option><option>p3d</option><option>fsx</option><option>fs2004</option>
            </select>
          </label>
          <label>Addon <input name="addon_name" required placeholder="Fenix A319"></label>
          <label class="airframe-span-2">Airframe SimBrief
            <select name="simbrief_airframe_id">
              <option value="">Utiliser le profil hérité</option>
              @foreach($simbriefAirframes as $airframe)<option value="{{ $airframe->id }}">{{ $airframe->icao }} · {{ $airframe->name }}</option>@endforeach
            </select>
          </label>
        </div>

        <details class="airframe-advanced">
          <summary>Identification et télémétrie avancées</summary>
          <div class="airframe-form-grid">
            <label>Identifiant appareil <input name="aircraft_identifier"></label>
            <label>Profil télémétrie <input name="telemetry_profile"></label>
          </div>
        </details>

        <input type="hidden" name="active" value="1">
        <button class="button" type="submit">Ajouter le profil addon</button>
      </form>
    </section>
  </section>

  <section class="airframe-panel admin-detail-panel" role="tabpanel" data-airframe-panel="history" data-detail-panel="history" hidden>
    <section class="panel">
      <div class="panel-heading">
        <div>
          <span class="eyebrow">HISTORIQUE</span>
          <h2>Documenter une modification</h2>
          <p class="airframe-help">Pour garder la trace d'une évolution réelle : cabine, moteur, équipement, capacité ou autre modification documentée.</p>
        </div>
      </div>

      <form method="post" action="{{ route('admin.promethee.airframes.modifications.save') }}" class="stack">
        @csrf
        <div class="airframe-form-grid">
          <label>Immatriculation
            <select name="aircraft_id"><option value="">Toutes / non précisée</option>@foreach($aircraft as $row)<option value="{{ $row['model']->id }}">{{ $row['model']->registration }}</option>@endforeach</select>
          </label>
          <label>Variante
            <select name="variant_id"><option value="">Non précisée</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->code }}</option>@endforeach</select>
          </label>
          <label>Configuration
            <select name="configuration_id"><option value="">Non précisée</option>@foreach($configurations as $config)<option value="{{ $config->id }}">{{ $config->name }}</option>@endforeach</select>
          </label>
          <label>Catégorie
            <select name="category">@foreach($modificationCategories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select>
          </label>
          <label class="airframe-span-2">Nom de la modification <input name="name" required placeholder="Cabine haute densité"></label>
          <label>Avant <input name="previous_value"></label>
          <label>Après <input name="new_value"></label>
        </div>

        <details class="airframe-advanced">
          <summary>Dates et source</summary>
          <div class="airframe-form-grid">
            <label>Du <input type="date" name="effective_from"></label>
            <label>Au <input type="date" name="effective_until"></label>
            <label>Source <input name="source"></label>
            <label>URL source <input type="url" name="source_url"></label>
          </div>
        </details>

        <button class="button" type="submit">Ajouter à l'historique</button>
      </form>
    </section>
  </section>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
<script>
(() => {
  'use strict';

  const init = () => {
    const root = document.querySelector('[data-airframe-console]');
    if (!root) return;

    const normalize = value => (value || '')
      .toString()
      .trim()
      .toLocaleLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');

    const fleetSearch = root.querySelector('#airframeFleetSearch');
    const fleetRows = [...root.querySelectorAll('#airframeFleetRows tr[data-aircraft-id]')];
    const fleetEmpty = root.querySelector('#airframeFleetEmpty');
    const fleetStatus = root.querySelector('#airframeFleetStatus');
    const fleetMasterButton = root.querySelector('[data-master-target="fleet"]');
    const globalSearch = root.querySelector('[data-airframe-global-search]');

    const aircraftSearch = root.querySelector('#airframeAircraftSearch');
    const aircraftSelect = root.querySelector('#airframeAircraftSelect');
    const assignmentForm = root.querySelector('#airframeAssignmentForm');
    const variantSelect = root.querySelector('#airframeVariantSelect');
    const configurationSelect = root.querySelector('#airframeConfigurationSelect');
    const selectionSummary = root.querySelector('#airframeSelectionSummary');

    const visibleFleetRows = () => fleetRows.filter(row => !row.hidden);

    const filterFleet = () => {
      const query = normalize(fleetSearch?.value);
      let visible = 0;

      fleetRows.forEach(row => {
        const match = !query || normalize(row.textContent).includes(query);
        row.hidden = !match;
        if (match) visible += 1;
      });

      fleetEmpty?.classList.toggle('is-visible', visible === 0);
      if (fleetStatus) {
        fleetStatus.textContent = visible === 0
          ? 'Aucun appareil trouvé.'
          : visible === 1
            ? '1 appareil trouvé · cliquez sur la ligne ou sur « Configurer ».'
            : visible + ' appareils trouvés · cliquez sur une ligne pour la configurer.';
      }
    };

    const filterConfigurations = () => {
      if (!configurationSelect) return;

      const variantId = variantSelect?.value || '';
      [...configurationSelect.options].forEach(option => {
        if (!option.value) {
          option.hidden = false;
          return;
        }
        option.hidden = !!variantId && option.dataset.variantId !== variantId;
      });

      const selected = configurationSelect.selectedOptions[0];
      if (selected?.hidden) configurationSelect.value = '';
    };

    const updateSelection = ({ prefill = true } = {}) => {
      if (!aircraftSelect) return;

      const selected = [...aircraftSelect.selectedOptions];
      const ids = new Set(selected.map(option => option.value));
      fleetRows.forEach(row => row.classList.toggle('is-selected', ids.has(row.dataset.aircraftId)));

      if (selectionSummary) {
        selectionSummary.textContent = selected.length === 0
          ? 'Aucune immatriculation sélectionnée.'
          : selected.length === 1
            ? 'Sélection : ' + selected[0].textContent.trim() + '. La variante/configuration actuelle est préchargée si elle existe.'
            : selected.length + ' immatriculations sélectionnées. Les valeurs communes sont préchargées automatiquement.';
      }

      if (!prefill || selected.length === 0 || !variantSelect) return;

      const variantIds = [...new Set(selected.map(option => option.dataset.variantId || '').filter(Boolean))];
      const configurationIds = [...new Set(selected.map(option => option.dataset.configurationId || '').filter(Boolean))];

      if (variantIds.length === 1) {
        variantSelect.value = variantIds[0];
      } else if (selected.length > 1) {
        variantSelect.value = '';
      }

      filterConfigurations();

      if (configurationSelect) {
        if (configurationIds.length === 1) {
          const candidate = [...configurationSelect.options].find(option => option.value === configurationIds[0] && !option.hidden);
          configurationSelect.value = candidate ? configurationIds[0] : '';
        } else if (selected.length > 1) {
          configurationSelect.value = '';
        }
      }
    };

    const configureAircraft = (aircraftId, { scroll = true } = {}) => {
      if (!aircraftSelect) return;

      const option = [...aircraftSelect.options].find(item => item.value === String(aircraftId));
      if (!option) return;

      [...aircraftSelect.options].forEach(item => { item.selected = item === option; });
      updateSelection();

      if (fleetMasterButton && root.dataset.activeDetail !== 'fleet') {
        fleetMasterButton.click();
      }

      if (scroll && assignmentForm) {
        assignmentForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
        window.setTimeout(() => aircraftSelect.focus({ preventScroll: true }), 250);
      }
    };

    fleetSearch?.addEventListener('input', filterFleet);
    fleetSearch?.addEventListener('keydown', event => {
      if (event.key !== 'Enter') return;
      const first = visibleFleetRows()[0];
      if (!first) return;
      event.preventDefault();
      configureAircraft(first.dataset.aircraftId);
    });

    fleetRows.forEach(row => {
      row.addEventListener('click', event => {
        if (event.target.closest('button,a,input,select,textarea,label')) return;
        configureAircraft(row.dataset.aircraftId);
      });
      row.addEventListener('keydown', event => {
        if (!['Enter', ' '].includes(event.key)) return;
        event.preventDefault();
        configureAircraft(row.dataset.aircraftId);
      });
    });

    root.querySelectorAll('[data-airframe-configure]').forEach(button => {
      button.addEventListener('click', () => configureAircraft(button.dataset.airframeConfigure));
    });

    const filterAircraft = () => {
      const query = normalize(aircraftSearch?.value);
      [...(aircraftSelect?.options || [])].forEach(option => {
        option.hidden = !!query && !normalize(option.textContent).includes(query);
      });
    };
    aircraftSearch?.addEventListener('input', filterAircraft);
    aircraftSearch?.addEventListener('keydown', event => {
      if (event.key !== 'Enter' || !aircraftSelect) return;
      const first = [...aircraftSelect.options].find(option => !option.hidden);
      if (!first) return;
      event.preventDefault();
      configureAircraft(first.value, { scroll: false });
    });

    aircraftSelect?.addEventListener('change', () => updateSelection());
    variantSelect?.addEventListener('change', filterConfigurations);

    root.querySelector('#airframeSelectVisible')?.addEventListener('click', () => {
      [...(aircraftSelect?.options || [])].forEach(option => {
        if (!option.hidden) option.selected = true;
      });
      updateSelection();
      aircraftSelect?.focus();
    });

    root.querySelector('#airframeClearSelection')?.addEventListener('click', () => {
      [...(aircraftSelect?.options || [])].forEach(option => { option.selected = false; });
      updateSelection({ prefill: false });
      aircraftSelect?.focus();
    });

    globalSearch?.addEventListener('input', () => {
      const raw = globalSearch.value || '';
      const query = normalize(raw);
      const matchingAircraft = query
        ? fleetRows.filter(row => normalize(row.textContent).includes(query))
        : [];

      if (query && matchingAircraft.length) {
        if (fleetMasterButton) fleetMasterButton.hidden = false;
        if (fleetSearch) {
          fleetSearch.value = raw;
          filterFleet();
        }
        if (root.dataset.activeDetail !== 'fleet') fleetMasterButton?.click();
      }
    });

    globalSearch?.addEventListener('keydown', event => {
      if (event.key !== 'Enter') return;
      const first = visibleFleetRows()[0];
      if (!first || normalize(globalSearch.value) === '') return;
      event.preventDefault();
      configureAircraft(first.dataset.aircraftId);
    });

    filterConfigurations();
    filterFleet();
    updateSelection({ prefill: false });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
  } else {
    init();
  }
})();
</script>
@endpush
