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
      <label><input type="checkbox" name="engine_overhaul" value="1"> Révision moteurs autorisée sur ce site</label>
      <label><input type="checkbox" name="active" value="1" checked> Site actif</label>
      <button>Enregistrer le site</button>
    </form>
  </section>
</div>

<section class="panel">
  <div class="panel-heading regional-aircraft-heading">
    <div><span class="eyebrow">ROTATION FLOTTE</span><h2>Faire tourner les immatriculations entre bases</h2><p>Seuls les appareils stationnés dans leur propre base, parkés, non réservés et sans mission/PIREP/maintenance en cours sont éligibles. Les permutations se font uniquement entre appareils de la même sous-flotte.</p></div>
    <form method="post" action="{{ route('admin.promethee.regional.rotation.run') }}" class="inline-form" onsubmit="return confirm('Lancer maintenant une rotation forcée des appareils actuellement éligibles ?');">
      @csrf
      <button type="submit" class="outline">Lancer une rotation maintenant</button>
    </form>
  </div>
  <form method="post" action="{{ route('admin.promethee.regional.rotation.settings') }}" class="form-grid">
    @csrf
    <label><input type="checkbox" name="enabled" value="1" @checked($rotationSettings['enabled'])> Rotation automatique activée</label>
    <label>Fréquence
      <select name="frequency">
        <option value="daily" @selected($rotationSettings['frequency']==='daily')>Tous les jours</option>
        <option value="weekly" @selected($rotationSettings['frequency']==='weekly')>Toutes les semaines</option>
      </select>
    </label>
    <label>Part de flotte à faire tourner
      <input type="number" name="percent" min="0" max="100" value="{{ $rotationSettings['percent'] }}" required>
      <small>% des appareils éligibles</small>
    </label>
    <label>Inactivité minimale
      <input type="number" name="min_idle_hours" min="0" max="720" value="{{ $rotationSettings['min_idle_hours'] }}" required>
      <small>heures depuis le dernier atterrissage</small>
    </label>
    <label>Délai avant nouvelle rotation
      <input type="number" name="cooldown_days" min="0" max="365" value="{{ $rotationSettings['cooldown_days'] }}" required>
      <small>jours</small>
    </label>
    <label>Priorité maintenance moteur
      <input type="number" name="maintenance_bias_hours" min="0" max="5000" value="{{ $rotationSettings['maintenance_bias_hours'] }}" required>
      <small>heures TBO restantes : l’appareil est orienté vers un site capable de révision</small>
    </label>
    <button>Enregistrer la rotation</button>
  </form>
  <p class="hint">Dernière exécution : {{ $rotationSettings['last_run_at'] ? CarbonCarbon::parse($rotationSettings['last_run_at'])->locale('fr')->diffForHumans() : 'jamais' }}. Une rotation ne crée jamais de mission de rapatriement : position et base attitrée sont permutées ensemble.</p>
</section>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">RÉSEAU TECHNIQUE</span><h2>Sites opérationnels</h2></div></div>
  <table>
    <thead><tr><th>Aéroport</th><th>Rôles</th><th>A CHECK</th><th>B CHECK</th><th>C CHECK</th><th>Révision moteur</th><th>État</th></tr></thead>
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
        <td>{{ !empty($base->engine_overhaul) ? 'Oui' : 'Non' }}</td>
        <td>{{ $base->active ? 'Actif' : 'Inactif' }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
</section>

<section class="panel table-wrap" id="aircraft-assignments">
  <div class="panel-heading regional-aircraft-heading">
    <div>
      <span class="eyebrow">AFFECTATION</span>
      <h2>Base attitrée des appareils</h2>
      <p>Filtrez et triez la flotte sans perdre votre position dans la page.</p>
    </div>
    <form method="post" action="{{ route('admin.promethee.regional.repatriation.sync') }}" class="inline-form" onsubmit="return confirm('Créer immédiatement les missions de rapatriement pour tous les appareils actuellement hors de leur base attitrée ?');">
      @csrf
      <button type="submit" class="outline">Générer les rapatriements maintenant</button>
    </form>
  </div>

  <div class="regional-aircraft-tools" data-aircraft-tools>
    <label>Recherche
      <input type="search" id="aircraftFilterQuery" placeholder="Immat, type, compagnie, aéroport…">
    </label>
    <label>Trier par
      <select id="aircraftSort">
        <option value="registration">Immatriculation</option>
        <option value="type">Type appareil</option>
        <option value="base">Base attitrée</option>
        <option value="position">Position actuelle</option>
        <option value="airline">Compagnie</option>
      </select>
    </label>
    <label>Type
      <select id="aircraftFilterType">
        <option value="">Tous les types</option>
        @foreach($aircraft->map(fn($plane) => $plane->subfleet?->name ?: $plane->icao)->filter()->unique()->sort() as $type)
          <option value="{{ $type }}">{{ $type }}</option>
        @endforeach
      </select>
    </label>
    <label>Base
      <select id="aircraftFilterBase">
        <option value="">Toutes les bases</option>
        @foreach($bases->where('active', true)->sortBy('airport_id') as $base)
          <option value="{{ $base->airport_id }}">{{ $base->airport_id }}</option>
        @endforeach
      </select>
    </label>
    <label>Compagnie
      <select id="aircraftFilterAirline">
        <option value="">Toutes les compagnies</option>
        @foreach($aircraft->map(fn($plane) => $plane->subfleet?->airline?->icao)->filter()->unique()->sort() as $airline)
          <option value="{{ $airline }}">{{ $airline }}</option>
        @endforeach
      </select>
    </label>
    <span class="tag" id="aircraftVisibleCount">{{ $aircraft->count() }} appareil(s)</span>
  </div>

  <table id="aircraftBaseTable">
    <thead><tr><th>Appareil</th><th>Type</th><th>Compagnie</th><th>Position</th><th>Base attitrée</th><th>État hors base</th><th></th></tr></thead>
    <tbody>
    @foreach($aircraft as $plane)
      @php($assignment = $assignments->get($plane->id))
      @php($typeLabel = $plane->subfleet?->name ?: $plane->icao ?: '—')
      @php($airlineIcao = $plane->subfleet?->airline?->icao ?: '—')
      @php($assignedBase = $assignment?->base_airport_id ?: 'LFPO')
      <tr
        data-registration="{{ strtoupper($plane->registration ?: '') }}"
        data-type="{{ $typeLabel }}"
        data-airline="{{ $airlineIcao }}"
        data-position="{{ strtoupper($plane->airport_id ?: '') }}"
        data-base="{{ strtoupper($assignedBase) }}">
        <td><strong class="aircraft-registration">{{ $plane->registration }}</strong></td>
        <td>{{ $typeLabel }}</td>
        <td>{{ $airlineIcao }}</td>
        <td>{{ $plane->airport_id ?: '—' }}</td>
        <td>{{ $assignedBase }}</td>
        <td>
          @if($assignment?->away_since)
            Depuis {{ \Carbon\Carbon::parse($assignment->away_since)->locale('fr')->diffForHumans() }}
          @else
            —
          @endif
        </td>
        <td>
          <form method="post" action="{{ route('admin.promethee.regional.aircraft.assign') }}" class="inline-form aircraft-base-form">
            @csrf
            <input type="hidden" name="aircraft_id" value="{{ $plane->id }}">
            <select name="base_airport_id">
              @foreach($bases->where('active', true) as $base)
                @php($baseRoles = collect([
                  !empty($base->is_hub) ? 'Hub' : null,
                  !empty($base->is_regional_platform) ? 'Régionale' : null,
                  !empty($base->is_technical_stop) ? 'Technique' : null,
                ])->filter()->implode(' + '))
                <option value="{{ $base->airport_id }}" @selected($assignedBase === $base->airport_id)>
                  {{ $base->airport_id }} · {{ $baseRoles ?: 'Site' }}
                </option>
              @endforeach
            </select>
            <label><input type="checkbox" name="rotation_locked" value="1" @checked(!empty($assignment?->rotation_locked))> Bloquer la rotation</label>
            <button>Affecter</button>
          </form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</section>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">HISTORIQUE ROTATION</span><h2>Dernières permutations automatiques</h2></div></div>
  <table>
    <thead><tr><th>Date</th><th>Appareil 1</th><th>Appareil 2</th><th>Permutation</th><th>Motif</th></tr></thead>
    <tbody>
    @forelse($rotationLog as $rotation)
      <tr>
        <td>{{ CarbonCarbon::parse($rotation->rotated_at)->locale('fr')->isoFormat('DD/MM/YYYY HH:mm') }}</td>
        <td><strong>{{ $rotation->first_registration }}</strong></td>
        <td><strong>{{ $rotation->second_registration }}</strong></td>
        <td>{{ $rotation->first_from_base }} ⇄ {{ $rotation->second_from_base }}</td>
        <td>{{ $rotation->reason === 'engine_maintenance_bias' ? 'Approche TBO moteur' : 'Rotation exploitation' }}</td>
      </tr>
    @empty
      <tr><td colspan="5">Aucune rotation enregistrée.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>

@push('scripts')
<script>
(() => {
  const table = document.getElementById('aircraftBaseTable');
  if (!table) return;
  const tbody = table.tBodies[0];
  const rows = [...tbody.rows];
  const q = document.getElementById('aircraftFilterQuery');
  const sort = document.getElementById('aircraftSort');
  const type = document.getElementById('aircraftFilterType');
  const base = document.getElementById('aircraftFilterBase');
  const airline = document.getElementById('aircraftFilterAirline');
  const count = document.getElementById('aircraftVisibleCount');
  const scrollKey = 'promethee:regional-operations:scroll';
  const filtersKey = 'promethee:regional-operations:filters';

  try {
    const savedScroll = sessionStorage.getItem(scrollKey);
    if (savedScroll !== null) {
      sessionStorage.removeItem(scrollKey);
      requestAnimationFrame(() => window.scrollTo({ top: Number(savedScroll) || 0, behavior: 'instant' }));
    }
    const savedFilters = JSON.parse(sessionStorage.getItem(filtersKey) || '{}');
    if (savedFilters.q) q.value = savedFilters.q;
    if (savedFilters.sort) sort.value = savedFilters.sort;
    if (savedFilters.type) type.value = savedFilters.type;
    if (savedFilters.base) base.value = savedFilters.base;
    if (savedFilters.airline) airline.value = savedFilters.airline;
  } catch (_) {}

  const normalize = value => String(value || '').trim().toLocaleUpperCase('fr-FR');
  const apply = () => {
    const query = normalize(q.value);
    const wantedType = normalize(type.value);
    const wantedBase = normalize(base.value);
    const wantedAirline = normalize(airline.value);
    let visible = 0;

    rows.forEach(row => {
      const haystack = normalize([
        row.dataset.registration,
        row.dataset.type,
        row.dataset.airline,
        row.dataset.position,
        row.dataset.base
      ].join(' '));
      const show = (!query || haystack.includes(query))
        && (!wantedType || normalize(row.dataset.type) === wantedType)
        && (!wantedBase || normalize(row.dataset.base) === wantedBase)
        && (!wantedAirline || normalize(row.dataset.airline) === wantedAirline);
      row.hidden = !show;
      if (show) visible++;
    });

    const key = sort.value || 'registration';
    [...rows].sort((a, b) => normalize(a.dataset[key]).localeCompare(normalize(b.dataset[key]), 'fr', { numeric: true }))
      .forEach(row => tbody.appendChild(row));
    count.textContent = visible + ' appareil(s)';

    try {
      sessionStorage.setItem(filtersKey, JSON.stringify({
        q: q.value, sort: sort.value, type: type.value, base: base.value, airline: airline.value
      }));
    } catch (_) {}
  };

  [q, sort, type, base, airline].forEach(node => node.addEventListener(node === q ? 'input' : 'change', apply));
  document.querySelectorAll('.aircraft-base-form').forEach(form => {
    form.addEventListener('submit', () => {
      try { sessionStorage.setItem(scrollKey, String(window.scrollY)); } catch (_) {}
    });
  });
  apply();
})();
</script>
@endpush
@endsection
