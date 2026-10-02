@extends('promethee::layout')
@section('title','Maintenance moteurs')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">AIR INTER · DIRECTION TECHNIQUE</span>
    <h1>Révisions moteurs & TBO.</h1>
    <p>Les checks A/B/C restent liés à la cellule. Les moteurs disposent ici de leurs propres heures, cycles, TBO, montages et révisions.</p>
  </div>
  <span class="tag">{{ $engineUnits->count() }} moteur(s)</span>
</div>

@if($errors->any())
<section class="panel"><strong>Impossible d’enregistrer :</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></section>
@endif

<div class="two-columns">
  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">RÉFÉRENTIEL</span><h2>Profil moteur par sous-flotte</h2></div></div>
    <form method="post" action="{{ route('admin.promethee.maintenance.engine-profiles.save') }}" class="form-grid">
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
      <button>Enregistrer / synchroniser la flotte</button>
    </form>
    <p class="hint">À la première synchronisation, Prométhée crée automatiquement des moteurs virtuels AUTO-* pour les appareils de la sous-flotte. Ils peuvent ensuite être remplacés par des moteurs de stock identifiés par numéro de série.</p>
  </section>

  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">STOCK</span><h2>Ajouter un moteur</h2></div></div>
    <form method="post" action="{{ route('admin.promethee.maintenance.engines.create') }}" class="form-grid">
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
      <button>Ajouter au stock</button>
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

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">MOTEURS</span><h2>Unités installées & stock</h2></div></div>
  <table>
    <thead><tr><th>N° série</th><th>Type</th><th>Appareil</th><th>Depuis révision</th><th>Restant</th><th>État</th><th>Actions</th></tr></thead>
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
        <td>{{ number_format($unit->hours_since_overhaul,1,',',' ') }} h / {{ number_format($unit->cycles_since_overhaul) }} cycles</td>
        <td>{{ $unit->remaining_hours !== null ? number_format($unit->remaining_hours,1,',',' ') . ' h' : '—' }} / {{ $unit->remaining_cycles !== null ? number_format($unit->remaining_cycles) . ' cycles' : '—' }}</td>
        <td><span class="tag">{{ strtoupper($unit->status) }}</span></td>
        <td>
          @if($unit->aircraft_id)
            <form method="post" action="{{ route('admin.promethee.maintenance.engines.overhaul', $unit->id) }}" class="inline-form" onsubmit="return confirm('Enregistrer la révision complète de ce moteur et remettre son TBO à zéro ?');">
              @csrf
              <input name="notes" maxlength="2000" placeholder="Note révision">
              <button type="submit">Réviser</button>
            </form>
          @else
            <form method="post" action="{{ route('admin.promethee.maintenance.engines.install', $unit->id) }}" class="inline-form">
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
              <button type="submit">Installer</button>
            </form>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="7">Aucun moteur configuré.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">JOURNAL TECHNIQUE</span><h2>Derniers événements moteurs</h2></div></div>
  <table>
    <thead><tr><th>Date</th><th>Moteur</th><th>Appareil</th><th>Événement</th><th>Site</th><th>Situation avant</th><th>Note</th></tr></thead>
    <tbody>
    @forelse($engineEvents as $event)
      <tr>
        <td>{{ CarbonCarbon::parse($event->occurred_at)->locale('fr')->isoFormat('DD/MM/YYYY HH:mm') }}</td>
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
@endsection
