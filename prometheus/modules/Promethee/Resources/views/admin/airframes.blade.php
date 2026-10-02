@extends('promethee::layout')
@section('title','Flotte technique')
@section('content')
<div class="ops-header compact">
  <div><span class="eyebrow">FLOTTE · SB-AIRFRAME</span><h1>Types, variantes et configurations.</h1>
  <p>La variante historique est liée à l’immatriculation. Le profil simulateur reste un choix d’addon et ne redéfinit jamais l’avion réel.</p></div>
</div>

@if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert danger"><strong>Configuration refusée.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">RÉSOLUTION ACTUELLE</span><h2>Immatriculations</h2></div></div>
  <div class="table-wrap"><table><thead><tr><th>Immatriculation</th><th>Type</th><th>Variante réelle</th><th>Configuration</th><th>PAX</th><th>SimBrief</th></tr></thead><tbody>
  @foreach($aircraft as $row)
    @php($r=$row['resolved'])
    <tr><td><strong>{{ $row['model']->registration }}</strong></td><td>{{ $r['aircraft']['type_name'] }}</td>
      <td>{{ $r['variant']['name'] ?? '—' }}</td><td>{{ $r['configuration']['name'] ?? '—' }}</td>
      <td>{{ $r['effective']['max_pax'] ?? '—' }}</td>
      <td>{{ $r['simbrief']['strategy'] }} · {{ $r['simbrief']['value'] ?? 'AUTO' }}</td></tr>
  @endforeach
  </tbody></table></div>
</section>

<div class="two-columns">
<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">1 · TYPE</span><h2>Profil de type</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.airframes.types.save') }}" class="stack">
    @csrf
    <label>Clé type <input name="type_key" required placeholder="N262"></label>
    <label>Nom <input name="name" required placeholder="Nord 262"></label>
    @include('promethee::admin.partials.airframe-simbrief-fields')
    @include('promethee::admin.partials.airframe-technical-fields')
    @include('promethee::admin.partials.airframe-source-fields')
    <button class="button" type="submit">Enregistrer le type</button>
  </form>
</section>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">2 · VARIANTE RÉELLE</span><h2>Variante historique</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.airframes.variants.save') }}" class="stack">
    @csrf
    <label>Type <select name="type_key" required>@foreach($types as $type)<option value="{{ $type->type_key }}">{{ $type->type_key }} · {{ $type->name }}</option>@endforeach</select></label>
    <label>Code <input name="code" required placeholder="N262A"></label>
    <label>Nom <input name="name" required placeholder="Nord 262A"></label>
    <label>Variante constructeur <input name="manufacturer_variant"></label>
    <label>Variante opérateur <input name="operator_variant"></label>
    <label>ICAO <input name="icao_type" maxlength="16"></label>
    <label>Valide depuis <input type="date" name="valid_from"></label><label>Valide jusqu’au <input type="date" name="valid_until"></label>
    @include('promethee::admin.partials.airframe-simbrief-fields')
    @include('promethee::admin.partials.airframe-technical-fields')
    @include('promethee::admin.partials.airframe-source-fields')
    <input type="hidden" name="active" value="1">
    <button class="button" type="submit">Créer / mettre à jour</button>
  </form>
</section>
</div>

<div class="two-columns">
<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">3 · CONFIGURATION</span><h2>Cabine, masses et phase</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.airframes.configurations.save') }}" class="stack">
    @csrf
    <label>Variante <select name="variant_id" required>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->type_key }} · {{ $variant->name }}</option>@endforeach</select></label>
    <label>Code <input name="code" required placeholder="AIR_INTER_PHASE_2"></label>
    <label>Nom <input name="name" required placeholder="Air Inter configuration modernisée"></label>
    <label>Nature <select name="configuration_kind"><option value="historical">Historique documentée</option><option value="VA_operational">Air Inter VA opérationnelle</option></select></label>
    <label>Phase / standard <input name="phase" placeholder="Phase 2"></label>
    <label>Valide depuis <input type="date" name="valid_from"></label><label>Valide jusqu’au <input type="date" name="valid_until"></label>
    @include('promethee::admin.partials.airframe-simbrief-fields')
    @include('promethee::admin.partials.airframe-technical-fields')
    @include('promethee::admin.partials.airframe-source-fields')
    <input type="hidden" name="active" value="1">
    <button class="button" type="submit">Enregistrer la configuration</button>
  </form>
</section>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">4 · IMMATRICULATION</span><h2>Affectation en masse</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.airframes.assign') }}" class="stack">
    @csrf
    <label>Immatriculations <select name="aircraft_ids[]" multiple size="10" required>@foreach($aircraft as $row)<option value="{{ $row['model']->id }}">{{ $row['model']->registration }} · {{ $row['model']->name }}</option>@endforeach</select></label>
    <label>Variante <select name="variant_id" required>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->type_key }} · {{ $variant->name }}</option>@endforeach</select></label>
    <label>Configuration <select name="configuration_id"><option value="">Sans configuration spécifique</option>@foreach($configurations as $config)<option value="{{ $config->id }}">{{ $config->variant?->code }} · {{ $config->name }}</option>@endforeach</select></label>
    <label>Début d’application <input type="date" name="valid_from"></label><label>Fin <input type="date" name="valid_until"></label>
    @include('promethee::admin.partials.airframe-technical-fields')
    @include('promethee::admin.partials.airframe-source-fields')
    <button class="button" type="submit">Appliquer aux immatriculations</button>
  </form>
</section>
</div>

<div class="two-columns">
<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">SIMULATEUR</span><h2>Compatibilité addons</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.airframes.simulator-profiles.save') }}" class="stack">
    @csrf
    <label>Variante <select name="variant_id" required>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->code }} · {{ $variant->name }}</option>@endforeach</select></label>
    <label>Configuration (optionnelle) <select name="configuration_id"><option value="">Toutes les configurations</option>@foreach($configurations as $config)<option value="{{ $config->id }}">{{ $config->name }}</option>@endforeach</select></label>
    <label>Simulateur <select name="simulator"><option>msfs2024</option><option>msfs2020</option><option>xplane</option><option>p3d</option><option>fsx</option><option>fs2004</option></select></label>
    <label>Addon <input name="addon_name" required placeholder="Fenix A319"></label>
    <label>Identifiant appareil <input name="aircraft_identifier"></label>
    <label>Profil télémétrie <input name="telemetry_profile"></label>
    <label>Airframe SimBrief <select name="simbrief_airframe_id"><option value="">Profil hérité</option>@foreach($simbriefAirframes as $airframe)<option value="{{ $airframe->id }}">{{ $airframe->icao }} · {{ $airframe->name }}</option>@endforeach</select></label>
    <input type="hidden" name="active" value="1">
    <button class="button" type="submit">Ajouter le profil addon</button>
  </form>
</section>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">HISTORIQUE</span><h2>Documenter une modification</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.airframes.modifications.save') }}" class="stack">
    @csrf
    <label>Immatriculation (optionnelle) <select name="aircraft_id"><option value="">—</option>@foreach($aircraft as $row)<option value="{{ $row['model']->id }}">{{ $row['model']->registration }}</option>@endforeach</select></label>
    <label>Variante (optionnelle) <select name="variant_id"><option value="">—</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->code }}</option>@endforeach</select></label>
    <label>Configuration (optionnelle) <select name="configuration_id"><option value="">—</option>@foreach($configurations as $config)<option value="{{ $config->id }}">{{ $config->name }}</option>@endforeach</select></label>
    <label>Nom <input name="name" required placeholder="Cabine haute densité"></label>
    <label>Catégorie <select name="category">@foreach($modificationCategories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select></label>
    <label>Avant <input name="previous_value"></label><label>Après <input name="new_value"></label>
    <label>Du <input type="date" name="effective_from"></label><label>Au <input type="date" name="effective_until"></label>
    <label>Source <input name="source"></label><label>URL source <input type="url" name="source_url"></label>
    <button class="button" type="submit">Ajouter à l’historique</button>
  </form>
</section>
</div>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">OUTILS</span><h2>Dupliquer une variante</h2></div></div>
  @foreach($variants as $variant)
  <form method="post" action="{{ route('admin.promethee.airframes.variants.duplicate',$variant) }}" class="inline-form">
    @csrf <strong>{{ $variant->code }}</strong>
    <input name="code" required placeholder="Nouveau code"><input name="name" required placeholder="Nouveau nom">
    <button type="submit">Dupliquer</button>
  </form>
  @endforeach
</section>
@endsection
