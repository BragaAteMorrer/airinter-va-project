@extends('promethee::layout')
@section('title','Bases régionales & flotte')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">AIR INTER · EXPLOITATION</span>
    <h1>Bases, plateformes & escales techniques.</h1>
    <p>Un même aéroport peut cumuler plusieurs rôles. Orly reste le hub principal ; Orly et CDG disposent des checks A, B et C. Une escale technique apporte uniquement la capacité A CHECK.</p>
  </div>
  <span class="tag">{{ $bases->where('active', true)->count() }} site(s) actif(s)</span>
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
    <div class="panel-heading"><div><span class="eyebrow">SITE OPÉRATIONNEL</span><h2>Ajouter / modifier</h2></div></div>
    <form method="post" action="{{ route('admin.promethee.regional.bases.save') }}" class="form-grid">
      @csrf
      <label>ICAO / ID aéroport<input name="airport_id" maxlength="8" required placeholder="LFPO"></label>
      <fieldset>
        <legend>Rôles — cumulables</legend>
        <label><input type="checkbox" name="is_hub" value="1"> Hub principal <small>LFPO uniquement</small></label>
        <label><input type="checkbox" name="is_regional_platform" value="1" checked> Plateforme régionale</label>
        <label><input type="checkbox" name="is_technical_stop" value="1"> Escale technique</label>
      </fieldset>
      <div class="hint">
        <strong>Maintenance calculée automatiquement :</strong>
        A CHECK sur les sites opérationnels ; A/B/C à LFPO et LFPG. Une escale technique seule ne donne jamais accès aux checks B ou C.
      </div>
      <label><input type="checkbox" name="active" value="1" checked> Site actif</label>
      <button>Enregistrer le site</button>
    </form>
  </section>
</div>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">RÉSEAU TECHNIQUE</span><h2>Sites opérationnels</h2></div></div>
  <table>
    <thead><tr><th>Aéroport</th><th>Rôles</th><th>A CHECK</th><th>B CHECK</th><th>C CHECK</th><th>État</th></tr></thead>
    <tbody>
    @foreach($bases as $base)
      @php($roles = collect([
        !empty($base->is_hub) ? 'HUB PRINCIPAL' : null,
        !empty($base->is_regional_platform) ? 'PLATEFORME RÉGIONALE' : null,
        !empty($base->is_technical_stop) ? 'ESCALE TECHNIQUE' : null,
      ])->filter())
      <tr>
        <td><strong>{{ $base->airport_id }}</strong> · {{ $base->airport_name ?: '—' }}</td>
        <td>
          @forelse($roles as $role)<span class="tag">{{ $role }}</span> @empty — @endforelse
        </td>
        <td>{{ !empty($base->check_a) ? 'Oui' : 'Non' }}</td>
        <td>{{ !empty($base->check_b) ? 'Oui' : 'Non' }}</td>
        <td>{{ !empty($base->check_c) ? 'Oui' : 'Non' }}</td>
        <td>{{ $base->active ? 'Actif' : 'Inactif' }}</td>
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
                @php($baseRoles = collect([
                  !empty($base->is_hub) ? 'Hub' : null,
                  !empty($base->is_regional_platform) ? 'Régionale' : null,
                  !empty($base->is_technical_stop) ? 'Technique' : null,
                ])->filter()->implode(' + '))
                <option value="{{ $base->airport_id }}" @selected(($assignment?->base_airport_id ?: 'LFPO') === $base->airport_id)>
                  {{ $base->airport_id }} · {{ $baseRoles ?: 'Site' }}
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
