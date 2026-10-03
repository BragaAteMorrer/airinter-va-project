@extends('promethee::layout')
@section('title','Maintenance cellule & moteurs')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page">
@php
  $requiredMaintenanceRoutes = [
    'admin.promethee.maintenance.sync',
    'admin.promethee.maintenance.airframe-settings.save',
    'admin.promethee.maintenance.airframe.start',
    'admin.promethee.maintenance.engine-profiles.save',
    'admin.promethee.maintenance.engines.create',
    'admin.promethee.maintenance.engines.overhaul',
    'admin.promethee.maintenance.engines.install',
  ];
  $missingMaintenanceRoutes = collect($requiredMaintenanceRoutes)
    ->reject(fn ($routeName) => \Illuminate\Support\Facades\Route::has($routeName))
    ->values();
  $maintenanceActionsReady = $missingMaintenanceRoutes->isEmpty();
@endphp

@if(!$maintenanceActionsReady)
<section class="panel">
  <strong>Actions de maintenance temporairement indisponibles.</strong>
  <p>Le cache des routes Laravel est incomplet. La page reste consultable en lecture seule au lieu de provoquer une erreur 500.</p>
  <small>Routes manquantes : {{ $missingMaintenanceRoutes->join(', ') }}</small>
</section>
@endif
<div class="ops-header compact">
  <div>
    <span class="eyebrow">AIR INTER · DIRECTION TECHNIQUE</span>
    <h1>Maintenance cellule & moteurs.</h1>
    <p>Prométhée suit séparément les checks A/B/C de la cellule et le potentiel TBO de chaque moteur, avec des compteurs heures + cycles issus des PIREPs acceptés.</p>
  </div>
  <div class="inline-form">
    <span class="tag">{{ $airframeSummary['total'] }} cellule(s)</span>
    <span class="tag">{{ $engineUnits->count() }} moteur(s)</span>
    <form method="post" action="{{ $maintenanceActionsReady ? route('admin.promethee.maintenance.sync') : '#' }}">
      @csrf
      <button type="submit" @disabled(!$maintenanceActionsReady)>Synchroniser toute la flotte</button>
    </form>
  </div>
</div>
<nav class="admin-workspace-nav" aria-label="Navigation locale">
  <a href="#maintenance-airframe">Cellule</a>
  <a href="#maintenance-airframe-history">Historique cellule</a>
  <a href="#maintenance-engines">Moteurs</a>
  <a href="#maintenance-engine-stock">Parc moteurs</a>
  <a href="#maintenance-engine-history">Historique moteurs</a>
</nav>


<section class="control-strip">
  <article><span>Moteurs suivis</span><strong>{{ $engineSummary['total'] }}</strong><small>{{ $engineSummary['installed'] }} installés · {{ $engineSummary['stock'] }} en stock</small></article>
  <article><span>Disponibles</span><strong>{{ $engineSummary['serviceable'] }}</strong><small>potentiel hors alerte</small></article>
  <article><span>À planifier</span><strong>{{ $engineSummary['warning'] }}</strong><small>dans la fenêtre d’alerte TBO</small></article>
  <article><span>TBO atteint</span><strong>{{ $engineSummary['due'] }}</strong><small>révision requise</small></article>
</section>

@if($errors->any())
<section class="panel"><strong>Impossible d’enregistrer :</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></section>
@endif

<section class="control-strip">
  <article><span>Cellules suivies</span><strong>{{ $airframeSummary['total'] }}</strong><small>compteurs A/B/C actifs</small></article>
  <article><span>À surveiller</span><strong>{{ $airframeSummary['warning'] }}</strong><small>dans la fenêtre d’alerte</small></article>
  <article><span>Check dû</span><strong>{{ $airframeSummary['due'] }}</strong><small>limite heures ou cycles atteinte</small></article>
  <article><span>En maintenance</span><strong>{{ $airframeSummary['maintenance'] }}</strong><small>immobilisation en cours</small></article>
</section>

<section class="panel admin-workspace-section" id="maintenance-airframe">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">MAINTENANCE CELLULE</span>
      <h2>Paramètres des checks A / B / C</h2>
      <p>Un check devient exigible dès que la limite en heures <strong>ou</strong> la limite en cycles est atteinte. La durée correspond à l’immobilisation planifiée de l’appareil.</p>
    </div>
  </div>
  <form method="post" action="{{ $maintenanceActionsReady ? route('admin.promethee.maintenance.airframe-settings.save') : '#' }}" class="form-grid">
    @csrf
    <label>A Check Time Limit
      <input type="number" name="a_time_limit_hours" min="0.1" max="100000" step="0.1" value="{{ $airframeSettings['checks']['a']['time_limit_hours'] }}" required>
      <small>heures de vol depuis le dernier A/B/C Check</small>
    </label>
    <label>A Check Cycle Limit
      <input type="number" name="a_cycle_limit" min="1" max="100000" value="{{ $airframeSettings['checks']['a']['cycle_limit'] }}" required>
      <small>cycles depuis le dernier A/B/C Check</small>
    </label>
    <label>A Check Duration
      <input type="number" name="a_duration_hours" min="0" max="10000" step="0.1" value="{{ $airframeSettings['checks']['a']['duration_hours'] }}" required>
      <small>heures d’immobilisation</small>
    </label>

    <label>B Check Time Limit
      <input type="number" name="b_time_limit_hours" min="0.1" max="100000" step="0.1" value="{{ $airframeSettings['checks']['b']['time_limit_hours'] }}" required>
      <small>heures de vol depuis le dernier B/C Check</small>
    </label>
    <label>B Check Cycle Limit
      <input type="number" name="b_cycle_limit" min="1" max="100000" value="{{ $airframeSettings['checks']['b']['cycle_limit'] }}" required>
      <small>cycles depuis le dernier B/C Check</small>
    </label>
    <label>B Check Duration
      <input type="number" name="b_duration_hours" min="0" max="10000" step="0.1" value="{{ $airframeSettings['checks']['b']['duration_hours'] }}" required>
      <small>heures d’immobilisation</small>
    </label>

    <label>C Check Time Limit
      <input type="number" name="c_time_limit_hours" min="0.1" max="100000" step="0.1" value="{{ $airframeSettings['checks']['c']['time_limit_hours'] }}" required>
      <small>heures de vol depuis le dernier C Check</small>
    </label>
    <label>C Check Cycle Limit
      <input type="number" name="c_cycle_limit" min="1" max="100000" value="{{ $airframeSettings['checks']['c']['cycle_limit'] }}" required>
      <small>cycles depuis le dernier C Check</small>
    </label>
    <label>C Check Duration
      <input type="number" name="c_duration_hours" min="0" max="10000" step="0.1" value="{{ $airframeSettings['checks']['c']['duration_hours'] }}" required>
      <small>heures d’immobilisation</small>
    </label>

    <label>Fenêtre d’alerte cellule
      <input type="number" name="warning_percent" min="0" max="100" step="0.1" value="{{ $airframeSettings['warning_percent'] }}" required>
      <small>% de potentiel restant avant mise en évidence dans Prométhée</small>
    </label>
    <button type="submit" @disabled(!$maintenanceActionsReady)>Enregistrer les cycles / durées</button>
  </form>
  <p class="hint">La fin d’un B Check remet aussi les compteurs A à zéro. La fin d’un C Check remet les compteurs A, B et C à zéro. Les PIREPs rejetés sont retirés des compteurs.</p>
</section>

<section class="panel table-wrap">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">POTENTIEL CELLULE</span>
      <h2>Checks A / B / C par appareil</h2>
      <p>Les deux limites sont suivies en parallèle : la première atteinte déclenche le besoin de maintenance.</p>
    </div>
  </div>
  <table>
    <thead><tr><th>Appareil</th><th>Position</th><th>État</th><th>A Check restant</th><th>B Check restant</th><th>C Check restant</th><th>Check actif</th><th>Action</th></tr></thead>
    <tbody>
    @forelse($airframeStates as $state)
      <tr>
        <td><strong>{{ $state->registration }}</strong><br><small>{{ $state->airline_icao ?: '—' }} · {{ $state->subfleet_name ?: $state->subfleet_type }}</small></td>
        <td>{{ $state->airport_id ?: '—' }}</td>
        <td>
          @if($state->maintenance_state === 'maintenance')
            <span class="tag">MAINTENANCE</span>
          @elseif($state->maintenance_state === 'due')
            <span class="tag">CHECK DÛ</span>
          @elseif($state->maintenance_state === 'warning')
            <span class="tag">À PLANIFIER</span>
          @else
            <span class="tag">SERVICE</span>
          @endif
        </td>
        @foreach(['a','b','c'] as $check)
          @php($checkState = $state->checks[$check])
          <td>
            <strong>{{ number_format($checkState['remaining_hours'],1,',',' ') }} h</strong><br>
            <small>{{ number_format($checkState['remaining_cycles']) }} cycles · {{ number_format($checkState['progress_percent'],1,',',' ') }} % consommé</small><br>
            @if($checkState['due'])
              <span class="tag">{{ strtoupper($check) }} DÛ</span>
            @elseif($checkState['warning'])
              <span class="tag">{{ strtoupper($check) }} À PLANIFIER</span>
            @endif
          </td>
        @endforeach
        <td>
          @if($state->active_check)
            <strong>{{ strtoupper($state->active_check) }} Check</strong><br>
            <small>
              {{ $state->active_started_at ? \Carbon\Carbon::parse($state->active_started_at)->locale('fr')->isoFormat('DD/MM HH:mm') : '—' }}
              →
              {{ $state->active_due_at ? \Carbon\Carbon::parse($state->active_due_at)->locale('fr')->isoFormat('DD/MM HH:mm') : '—' }}
            </small>
          @else
            —
          @endif
        </td>
        <td>
          @if(!$state->active_check)
            <form method="post" action="{{ $maintenanceActionsReady ? route('admin.promethee.maintenance.airframe.start', $state->aircraft_id) : '#' }}" class="inline-form" onsubmit="return confirm('Immobiliser cet appareil pour le check sélectionné ?');">
              @csrf
              <select name="check" required>
                <option value="a" @selected($state->next_check === 'a')>A Check</option>
                <option value="b" @selected($state->next_check === 'b')>B Check</option>
                <option value="c" @selected($state->next_check === 'c')>C Check</option>
              </select>
              <button type="submit" @disabled(!$maintenanceActionsReady)>Démarrer</button>
            </form>
          @else
            <small>Remise en service automatique à l’échéance.</small>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="8">Aucun appareil à suivre.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>

<section class="panel table-wrap admin-workspace-section" id="maintenance-airframe-history">
  <div class="panel-heading"><div><span class="eyebrow">JOURNAL CELLULE</span><h2>Derniers checks A / B / C</h2></div></div>
  <table>
    <thead><tr><th>Date</th><th>Appareil</th><th>Check</th><th>Événement</th><th>Site</th><th>Situation avant</th><th>Note</th></tr></thead>
    <tbody>
    @forelse($airframeEvents as $event)
      <tr>
        <td>{{ \Carbon\Carbon::parse($event->occurred_at)->locale('fr')->isoFormat('DD/MM/YYYY HH:mm') }}</td>
        <td><strong>{{ $event->registration }}</strong></td>
        <td>{{ strtoupper($event->check_type) }} Check</td>
        <td>{{ $event->event_type === 'started' ? 'Début' : 'Terminé' }}</td>
        <td>{{ $event->airport_id ?: '—' }}</td>
        <td>{{ $event->minutes_before !== null ? number_format($event->minutes_before / 60,1,',',' ') . ' h' : '—' }} / {{ $event->cycles_before !== null ? number_format($event->cycles_before) . ' cycles' : '—' }}</td>
        <td>{{ $event->notes ?: '—' }}</td>
      </tr>
    @empty
      <tr><td colspan="7">Aucun check cellule enregistré.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>

<div class="two-columns admin-workspace-grid">
  <section class="panel admin-workspace-section" id="maintenance-engines">
    <div class="panel-heading"><div><span class="eyebrow">RÉFÉRENTIEL</span><h2>Profil moteur par sous-flotte</h2></div></div>
    <form method="post" action="{{ $maintenanceActionsReady ? route('admin.promethee.maintenance.engine-profiles.save') : '#' }}" class="form-grid">
      @csrf
      <label>Sous-flotte
        <select name="subfleet_id" required>
          @foreach($subfleets as $subfleet)
            <option value="{{ $subfleet->id }}">{{ $subfleet->airline?->icao ?: '—' }} · {{ $subfleet->name }} ({{ $subfleet->type }})</option>
          @endforeach
        </select>
      </label>
      <label>Type moteur<input name="engine_type" maxlength="80" placeholder="CFM56-5A1" required></label>
      <label>Nombre de moteurs<input type="number" name="engine_count" min="1" max="4" value="2" required></label>
      <label>TBO heures<input type="number" name="tbo_hours" min="1" max="100000" step="0.1" placeholder="12000"></label>
      <label>TBO cycles<input type="number" name="tbo_cycles" min="1" max="100000" placeholder="9000"></label>
      <label>Alerte avant TBO (h)<input type="number" name="warning_hours" min="0" max="10000" step="0.1" value="100" required></label>
      <label>Alerte avant TBO (cycles)<input type="number" name="warning_cycles" min="0" max="10000" placeholder="100"></label>
      <label><input type="checkbox" name="active" value="1" checked> Suivi actif</label>
      <button @disabled(!$maintenanceActionsReady)>Enregistrer / synchroniser la flotte</button>
    </form>
    <p class="hint">À la première synchronisation, Prométhée crée automatiquement des moteurs virtuels AUTO-* pour les appareils de la sous-flotte. Ils peuvent ensuite être remplacés par des moteurs de stock identifiés par numéro de série.</p>
    <p class="hint"><strong>Synchroniser toute la flotte</strong> complète aussi les profils moteurs manquants depuis le référentiel Air Inter VA embarqué, sans écraser les profils déjà personnalisés.</p>
  </section>

  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">STOCK</span><h2>Ajouter un moteur</h2></div></div>
    <form method="post" action="{{ $maintenanceActionsReady ? route('admin.promethee.maintenance.engines.create') : '#' }}" class="form-grid">
      @csrf
      <label>Profil moteur
        <select name="engine_profile_id" required>
          @foreach($profiles as $profile)
            <option value="{{ $profile->id }}">{{ $profile->airline_icao ?: '—' }} · {{ $profile->subfleet_name }} · {{ $profile->engine_type }}</option>
          @endforeach
        </select>
      </label>
      <label>Numéro de série<input name="serial_number" maxlength="96" required placeholder="CF6-50-XXXX"></label>
      <label>Heures depuis révision<input type="number" name="hours_since_overhaul" min="0" max="100000" step="0.1" value="0"></label>
      <label>Cycles depuis révision<input type="number" name="cycles_since_overhaul" min="0" max="100000" value="0"></label>
      <button @disabled(!$maintenanceActionsReady)>Ajouter au stock</button>
    </form>
    <div class="hint">
      <strong>Sites capables de révision moteur :</strong>
      @forelse($engineSites as $site)<span class="tag">{{ $site->airport_id }}</span> @empty aucun site configuré @endforelse
    </div>
  </section>
</div>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">CONFIGURATION</span><h2>Profils moteurs</h2></div></div>
  <table>
    <thead><tr><th>Compagnie / sous-flotte</th><th>Moteur</th><th>Qté</th><th>TBO</th><th>Alerte</th><th>État</th></tr></thead>
    <tbody>
    @forelse($profiles as $profile)
      <tr>
        <td><strong>{{ $profile->airline_icao ?: '—' }}</strong> · {{ $profile->subfleet_name }} <small>{{ $profile->subfleet_type }}</small></td>
        <td>{{ $profile->engine_type }}</td>
        <td>{{ $profile->engine_count }}</td>
        <td>{{ $profile->tbo_hours !== null ? number_format($profile->tbo_hours,1,',',' ') . ' h' : '—' }} / {{ $profile->tbo_cycles !== null ? number_format($profile->tbo_cycles) . ' cycles' : '—' }}</td>
        <td>{{ number_format($profile->warning_hours,1,',',' ') }} h / {{ $profile->warning_cycles !== null ? number_format($profile->warning_cycles) . ' cycles' : '—' }}</td>
        <td><span class="tag">{{ $profile->active ? 'ACTIF' : 'INACTIF' }}</span></td>
      </tr>
    @empty
      <tr><td colspan="6">Aucun profil moteur. Renseignez les valeurs engine/TBO de votre référentiel pour commencer.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>

<section class="panel table-wrap admin-workspace-section" id="maintenance-engine-stock">
  <div class="panel-heading"><div><span class="eyebrow">MOTEURS</span><h2>Unités installées & stock</h2></div></div>
  <table>
    <thead><tr><th>N° série</th><th>Type</th><th>Appareil</th><th>TBO nominal</th><th>Consommé depuis révision</th><th>Potentiel moteur</th><th>Dernière révision</th><th>État</th><th>Actions</th></tr></thead>
    <tbody>
    @forelse($engineUnits as $unit)
      <tr>
        <td><strong>{{ $unit->serial_number }}</strong></td>
        <td>{{ $unit->engine_type }}<br><small>{{ $unit->airline_icao ?: '—' }} · {{ $unit->subfleet_name }}</small></td>
        <td>
          @if($unit->aircraft_id)
            <strong>{{ $unit->registration }}</strong> · moteur {{ $unit->position }}<br><small>{{ $unit->airport_id ?: '—' }}</small>
          @else
            <span class="tag">STOCK</span>
          @endif
        </td>
        <td>
          {{ $unit->tbo_hours !== null ? number_format($unit->tbo_hours,1,',',' ') . ' h' : '—' }}<br>
          <small>{{ $unit->tbo_cycles !== null ? number_format($unit->tbo_cycles) . ' cycles' : '—' }}</small>
        </td>
        <td>
          {{ number_format($unit->hours_since_overhaul,1,',',' ') }} h<br>
          <small>{{ number_format($unit->cycles_since_overhaul) }} cycles</small>
        </td>
        <td>
          <strong>
            {{ $unit->remaining_hours !== null ? number_format($unit->remaining_hours,1,',',' ') . ' h' : '—' }}
            ·
            {{ $unit->remaining_cycles !== null ? number_format($unit->remaining_cycles) . ' cycles' : '—' }}
          </strong><br>
          @if($unit->potential_percent !== null)
            <span class="tag">{{ number_format($unit->potential_percent,1,',',' ') }} % restant</span>
            <small>limitant : {{ $unit->potential_basis }}</small><br>
            <small>
              H {{ $unit->potential_hours_percent !== null ? number_format($unit->potential_hours_percent,1,',',' ') . ' %' : '—' }}
              · C {{ $unit->potential_cycles_percent !== null ? number_format($unit->potential_cycles_percent,1,',',' ') . ' %' : '—' }}
            </small>
          @else
            <span class="tag">TBO non renseigné</span>
          @endif
        </td>
        <td>{{ $unit->last_overhaul_at ? \Carbon\Carbon::parse($unit->last_overhaul_at)->locale('fr')->isoFormat('DD/MM/YYYY') : 'Jamais / inconnu' }}</td>
        <td><span class="tag">{{ strtoupper($unit->status) }}</span></td>
        <td>
          @if($unit->aircraft_id)
            <form method="post" action="{{ $maintenanceActionsReady ? route('admin.promethee.maintenance.engines.overhaul', $unit->id) : '#' }}" class="inline-form" onsubmit="return confirm('Enregistrer la révision complète de ce moteur et remettre son TBO à zéro ?');">
              @csrf
              <input name="notes" maxlength="2000" placeholder="Note révision">
              <button type="submit" @disabled(!$maintenanceActionsReady)>Réviser</button>
            </form>
          @else
            <form method="post" action="{{ $maintenanceActionsReady ? route('admin.promethee.maintenance.engines.install', $unit->id) : '#' }}" class="inline-form">
              @csrf
              <select name="aircraft_id" required>
                <option value="">Appareil…</option>
                @foreach($aircraft->filter(fn($plane) => (int)$plane->subfleet_id === (int)$unit->subfleet_id) as $plane)
                  <option value="{{ $plane->id }}">{{ $plane->registration }} · {{ $plane->airport_id ?: '—' }}</option>
                @endforeach
              </select>
              <select name="position" required>
                @for($position=1;$position<=4;$position++)<option value="{{ $position }}">M{{ $position }}</option>@endfor
              </select>
              <button type="submit" @disabled(!$maintenanceActionsReady)>Installer</button>
            </form>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="9">Aucun moteur configuré. Créez un profil moteur puis synchronisez toute la flotte.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>

<section class="panel table-wrap admin-workspace-section" id="maintenance-engine-history">
  <div class="panel-heading"><div><span class="eyebrow">JOURNAL TECHNIQUE</span><h2>Derniers événements moteurs</h2></div></div>
  <table>
    <thead><tr><th>Date</th><th>Moteur</th><th>Appareil</th><th>Événement</th><th>Site</th><th>Situation avant</th><th>Note</th></tr></thead>
    <tbody>
    @forelse($engineEvents as $event)
      <tr>
        <td>{{ \Carbon\Carbon::parse($event->occurred_at)->locale('fr')->isoFormat('DD/MM/YYYY HH:mm') }}</td>
        <td><strong>{{ $event->serial_number }}</strong></td>
        <td>{{ $event->registration ?: 'Stock' }}</td>
        <td>{{ strtoupper($event->event_type) }}</td>
        <td>{{ $event->airport_id ?: '—' }}</td>
        <td>{{ $event->hours_before !== null ? number_format($event->hours_before,1,',',' ') . ' h' : '—' }} / {{ $event->cycles_before !== null ? number_format($event->cycles_before) . ' cycles' : '—' }}</td>
        <td>{{ $event->notes ?: '—' }}</td>
      </tr>
    @empty
      <tr><td colspan="7">Aucun événement moteur.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>
</div>
@endsection
