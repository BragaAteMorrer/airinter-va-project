@extends('promethee::layout')
@section('title','Bases régionales & flotte')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">AIR INTER · EXPLOITATION</span>
    <h1>Bases régionales & flotte.</h1>
    <p>Orly est le hub principal. Les autres plateformes régionales assurent l'exploitation courante et la petite maintenance.</p>
  </div>
  <span class="tag">{{ $bases->where('active', true)->count() }} base(s) active(s)</span>
</div>

<div class="two-columns">
  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">RAPATRIEMENT</span><h2>Règles automatiques</h2></div></div>
    <form method="post" action="{{ route('admin.promethee.regional.settings') }}" class="form-grid">
      @csrf
      <label>Créer une mission après
        <input type="number" min="1" max="365" name="mission_after_days" value="{{ $settings['mission_after_days'] }}" required>
        <small>jours hors base</small>
      </label>
      <label>Rapatrier automatiquement après
        <input type="number" min="2" max="730" name="auto_return_after_days" value="{{ $settings['auto_return_after_days'] }}" required>
        <small>jours hors base sans réservation</small>
      </label>
      <label>Prime mission
        <input type="number" step="0.1" min="1" max="10" name="reward_multiplier" value="{{ $settings['reward_multiplier'] }}" required>
        <small>multiplicateur de rémunération</small>
      </label>
      <button>Enregistrer les règles</button>
    </form>
  </section>

  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">PLATEFORME</span><h2>Ajouter / modifier</h2></div></div>
    <form method="post" action="{{ route('admin.promethee.regional.bases.save') }}" class="form-grid">
      @csrf
      <label>ICAO / ID aéroport<input name="airport_id" maxlength="8" required placeholder="LFPO"></label>
      <label>Type
        <select name="kind">
          <option value="regional">Plateforme régionale</option>
          <option value="hub">Hub principal — LFPO uniquement</option>
        </select>
        <small>Orly (LFPO) est le seul hub autorisé.</small>
      </label>
      <label><input type="checkbox" name="small_maintenance" value="1" checked> Petite maintenance</label>
      <label><input type="checkbox" name="heavy_maintenance" value="1"> Grosse maintenance</label>
      <label><input type="checkbox" name="active" value="1" checked> Active</label>
      <button>Enregistrer la plateforme</button>
    </form>
  </section>
</div>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">RÉSEAU TECHNIQUE</span><h2>Plateformes</h2></div></div>
  <table>
    <thead><tr><th>Aéroport</th><th>Rôle</th><th>Petite maintenance</th><th>Grosse maintenance</th><th>État</th></tr></thead>
    <tbody>
    @foreach($bases as $base)
      <tr>
        <td><strong>{{ $base->airport_id }}</strong> · {{ $base->airport_name ?: '—' }}</td>
        <td><span class="tag">{{ $base->kind === 'hub' ? 'HUB' : 'RÉGIONALE' }}</span></td>
        <td>{{ $base->small_maintenance ? 'Oui' : 'Non' }}</td>
        <td>{{ $base->heavy_maintenance ? 'Oui' : 'Non' }}</td>
        <td>{{ $base->active ? 'Active' : 'Inactive' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
</section>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">AFFECTATION</span><h2>Base attitrée des appareils</h2></div></div>
  <table>
    <thead><tr><th>Appareil</th><th>Compagnie</th><th>Position</th><th>Base attitrée</th><th>État hors base</th><th></th></tr></thead>
    <tbody>
    @foreach($aircraft as $plane)
      @php($assignment = $assignments->get($plane->id))
      <tr>
        <td><strong>{{ $plane->registration }}</strong> · {{ $plane->icao }}</td>
        <td>{{ $plane->subfleet?->airline?->icao ?: '—' }}</td>
        <td>{{ $plane->airport_id ?: '—' }}</td>
        <td>{{ $assignment?->base_airport_id ?: 'LFPO' }}</td>
        <td>
          @if($assignment?->away_since)
            Depuis {{ \Carbon\Carbon::parse($assignment->away_since)->locale('fr')->diffForHumans() }}
          @else
            —
          @endif
        </td>
        <td>
          <form method="post" action="{{ route('admin.promethee.regional.aircraft.assign') }}" class="inline-form">
            @csrf
            <input type="hidden" name="aircraft_id" value="{{ $plane->id }}">
            <select name="base_airport_id">
              @foreach($bases->where('active', true) as $base)
                <option value="{{ $base->airport_id }}" @selected(($assignment?->base_airport_id ?: 'LFPO') === $base->airport_id)>
                  {{ $base->airport_id }} · {{ $base->kind === 'hub' ? 'Hub' : 'Régionale' }}
                </option>
              @endforeach
            </select>
            <button>Affecter</button>
          </form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</section>
@endsection
