@extends('promethee::layout')
@section('title','Variantes & configurations appareils')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">AIR INTER · FLOTTE</span>
    <h1>Variantes, configurations & historique.</h1>
    <p>Le type réel, la variante historique, la configuration Air Inter et l'add-on simulateur sont gérés séparément. Aucune donnée historique n'est créée automatiquement.</p>
  </div>
  <span class="tag">{{ $variants->count() }} variantes · {{ $configurations->count() }} configurations</span>
</div>

@if(session('success'))<section class="panel"><strong>{{ session('success') }}</strong></section>@endif
@if($errors->any())<section class="panel"><strong>Impossible d'enregistrer :</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></section>@endif

<div class="two-columns">
<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">TYPE → VARIANTE</span><h2>Créer une variante réelle</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.aircraft-configurations.variants.save') }}" class="form-grid">
    @csrf
    <label>Type / clé ICAO<input name="aircraft_type_key" required placeholder="N262"></label>
    <label>Nom<input name="name" required placeholder="Nord 262A"></label>
    <label>Nom court<input name="short_name" placeholder="N262A"></label>
    <label>ICAO<input name="icao_type" placeholder="N262"></label>
    <label>Variante constructeur<input name="manufacturer_variant"></label>
    <label>Variante opérateur<input name="operator_variant"></label>
    <label>Phase / standard<input name="phase" placeholder="Standard modernisé"></label>
    <label>Capacité PAX<input type="number" min="0" name="max_pax"></label>
    <label>MTOW<input type="number" min="0" name="mtow"></label>
    <label>OEW<input type="number" min="0" name="oew"></label>
    <label>MZFW<input type="number" min="0" name="mzfw"></label>
    <label>MLW<input type="number" min="0" name="mlw"></label>
    <label>Moteur constructeur<input name="engine_manufacturer"></label>
    <label>Moteur modèle<input name="engine_model"></label>
    <label>Moteur variante<input name="engine_variant"></label>
    <label>Nombre moteurs<input type="number" min="1" max="8" name="engine_count"></label>
    <label>Stratégie SimBrief<select name="simbrief_strategy"><option value="">Héritée</option><option value="type">Type</option><option value="internal_id">Internal ID</option><option value="proxy">Proxy</option></select></label>
    <label>Type SimBrief<input name="simbrief_type"></label>
    <label>Internal ID<input name="simbrief_internal_id"></label>
    <label>Proxy SimBrief<input name="simbrief_proxy_type"></label>
    <label>Valide depuis<input type="date" name="valid_from"></label>
    <label>Valide jusqu'au<input type="date" name="valid_until"></label>
    <label>Confiance<select name="historical_confidence" required><option value="confirmed">Confirmé</option><option value="probable">Probable</option><option value="va_configuration" selected>Configuration VA</option></select></label>
    <label>Source<input name="source" placeholder="Archive, livre, manuel…"></label>
    <label>URL source<input name="source_url" type="url"></label>
    <label class="wide">Notes<textarea name="notes"></textarea></label>
    <label><input type="checkbox" name="active" value="1" checked> Active</label>
    <button>Créer la variante</button>
  </form>
</section>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">VARIANTE → CONFIGURATION</span><h2>Créer une configuration</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.aircraft-configurations.configurations.save') }}" class="form-grid">
    @csrf
    <label>Variante<select name="variant_id"><option value="">Héritage type uniquement</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->aircraft_type_key }} · {{ $variant->name }}</option>@endforeach</select></label>
    <label>Code<input name="code" required placeholder="N262A_ITF_1975"></label>
    <label>Nom<input name="name" required placeholder="Air Inter standard 1975"></label>
    <label>Nature<select name="kind" required><option value="historical">Historique documentée</option><option value="va_operational" selected>Air Inter VA opérationnelle</option></select></label>
    <label>Phase<input name="phase"></label>
    <label>Capacité PAX<input type="number" min="0" name="max_pax"></label>
    <label>MTOW<input type="number" min="0" name="mtow"></label>
    <label>OEW<input type="number" min="0" name="oew"></label>
    <label>MZFW<input type="number" min="0" name="mzfw"></label>
    <label>MLW<input type="number" min="0" name="mlw"></label>
    <label>Stratégie SimBrief<select name="simbrief_strategy"><option value="">Héritée</option><option value="type">Type</option><option value="internal_id">Internal ID</option><option value="proxy">Proxy</option></select></label>
    <label>Type SimBrief<input name="simbrief_type"></label>
    <label>Internal ID<input name="simbrief_internal_id"></label>
    <label>Proxy SimBrief<input name="simbrief_proxy_type"></label>
    <label>Valide depuis<input type="date" name="valid_from"></label>
    <label>Valide jusqu'au<input type="date" name="valid_until"></label>
    <label>Confiance<select name="historical_confidence" required><option value="confirmed">Confirmé</option><option value="probable">Probable</option><option value="va_configuration" selected>Configuration VA</option></select></label>
    <label>Source<input name="source"></label>
    <label>URL source<input name="source_url" type="url"></label>
    <label class="wide">Notes<textarea name="notes"></textarea></label>
    <label><input type="checkbox" name="active" value="1" checked> Active</label>
    <button>Créer la configuration</button>
  </form>
</section>
</div>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">IMMATRICULATIONS</span><h2>Appliquer en masse</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.aircraft-configurations.assign') }}" class="form-grid">
    @csrf
    <label class="wide">Appareils<select name="aircraft_ids[]" multiple size="10" required>@foreach($aircraft as $plane)<option value="{{ $plane->id }}">{{ $plane->registration }} · {{ $plane->subfleet?->name ?: $plane->name }}</option>@endforeach</select></label>
    <label>Variante<select name="variant_id"><option value="">—</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->aircraft_type_key }} · {{ $variant->name }}</option>@endforeach</select></label>
    <label>Configuration<select name="configuration_id"><option value="">—</option>@foreach($configurations as $configuration)<option value="{{ $configuration->id }}">{{ $configuration->code }} · {{ $configuration->name }}</option>@endforeach</select></label>
    <label>Depuis<input type="date" name="valid_from"></label>
    <label>Jusqu'au<input type="date" name="valid_until"></label>
    <label>Confiance<select name="historical_confidence" required><option value="confirmed">Confirmé</option><option value="probable">Probable</option><option value="va_configuration" selected>Configuration VA</option></select></label>
    <label>Source<input name="source"></label>
    <label>URL source<input name="source_url" type="url"></label>
    <label class="wide">Notes<textarea name="notes"></textarea></label>
    <label><input type="checkbox" name="active_for_va" value="1"> Configuration opérationnelle active de la VA</label>
    <button>Appliquer aux immatriculations</button>
  </form>
</section>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">ADD-ONS</span><h2>Profil simulateur</h2></div></div>
  <form method="post" action="{{ route('admin.promethee.aircraft-configurations.simulator-profiles.save') }}" class="form-grid">
    @csrf
    <label>Variante<select name="variant_id"><option value="">—</option>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->name }}</option>@endforeach</select></label>
    <label>Configuration<select name="configuration_id"><option value="">—</option>@foreach($configurations as $configuration)<option value="{{ $configuration->id }}">{{ $configuration->name }}</option>@endforeach</select></label>
    <label>Simulateur<select name="simulator" required><option>msfs2024</option><option>msfs2020</option><option>fsx</option><option>p3d</option><option>fs2004</option><option>xplane</option></select></label>
    <label>Add-on<input name="addon_name" required placeholder="Fenix A319"></label>
    <label>Version<input name="addon_version"></label>
    <label>Identifiant avion<input name="aircraft_identifier"></label>
    <label>Airframe SimBrief<input name="simbrief_airframe"></label>
    <label>Profil télémétrie<input name="telemetry_profile"></label>
    <label><input type="checkbox" name="active" value="1" checked> Actif</label>
    <button>Ajouter le profil</button>
  </form>
</section>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">HISTORIQUE</span><h2>Affectations</h2></div></div>
  <table><thead><tr><th>Immat.</th><th>Variante</th><th>Configuration</th><th>Période</th><th>VA</th><th>Confiance</th></tr></thead>
  <tbody>@forelse($assignments as $row)<tr><td><strong>{{ $row->registration }}</strong></td><td>{{ $row->variant_name ?: '—' }}</td><td>{{ $row->configuration_name ?: '—' }}</td><td>{{ $row->valid_from ?: '…' }} → {{ $row->valid_until ?: '…' }}</td><td>{{ $row->active_for_va ? 'ACTIVE' : '—' }}</td><td>{{ $row->historical_confidence }}</td></tr>@empty<tr><td colspan="6">Aucune affectation.</td></tr>@endforelse</tbody></table>
</section>
@endsection
