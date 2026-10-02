@php
  $sb = $simbrief_profile ?? [];
  $weights = is_array($sb['weights_kg'] ?? null) ? $sb['weights_kg'] : [];
  $performance = is_array($sb['performance'] ?? null) ? $sb['performance'] : [];
  $strategy = old('simbrief_strategy', $sb['strategy'] ?? (filled($airframe->airframe_id ?? null) ? 'custom_airframe' : 'native'));
@endphp

<div class="row">
  <div class="form-group col-sm-4">
    {{ Form::label('icao', 'ICAO réel:') }}
    {{ Form::text('icao', null, ['class' => 'form-control', 'placeholder' => 'N262']) }}
    <p class="text-danger">{{ $errors->first('icao') }}</p>
  </div>
  <div class="form-group col-sm-4">
    {{ Form::label('name', 'Nom appareil:') }}
    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'NORD 262']) }}
    <p class="text-danger">{{ $errors->first('name') }}</p>
  </div>
  <div class="form-group col-sm-4" data-sb-section="custom_airframe">
    {{ Form::label('airframe_id', 'SimBrief Internal ID:') }}
    {{ Form::text('airframe_id', null, ['class' => 'form-control', 'placeholder' => '123456_1582090020']) }}
    <p class="help-block">Utilisé uniquement avec le mode « Airframe personnalisé ».</p>
    <p class="text-danger">{{ $errors->first('airframe_id') }}</p>
  </div>
</div>

<hr>
<h4>SimBrief</h4>
<p class="text-muted">
  sb-airframe est la source de vérité : l’ICAO ci-dessus reste l’identité réelle de l’appareil.
  Le type proxy n’est utilisé que comme moteur de calcul SimBrief.
</p>

<div class="row">
  <div class="form-group col-sm-4">
    <label for="simbrief_strategy">Mode de génération</label>
    <select class="form-control" id="simbrief_strategy" name="simbrief_strategy">
      <option value="native" @selected($strategy === 'native')>Type SimBrief natif</option>
      <option value="custom_airframe" @selected($strategy === 'custom_airframe')>Airframe personnalisé SimBrief</option>
      <option value="proxy" @selected($strategy === 'proxy')>Profil proxy</option>
    </select>
    <p class="text-danger">{{ $errors->first('simbrief_strategy') }}</p>
  </div>
  <div class="form-group col-sm-4" data-sb-section="proxy">
    <label for="simbrief_proxy_type">Type SimBrief de référence</label>
    <input class="form-control" id="simbrief_proxy_type" name="simbrief_proxy_type"
           value="{{ old('simbrief_proxy_type', $sb['proxy_type'] ?? '') }}" placeholder="SH33">
    <p class="help-block">Base de performances uniquement. Ne remplace jamais l’ICAO réel.</p>
    <p class="text-danger">{{ $errors->first('simbrief_proxy_type') }}</p>
  </div>
  <div class="form-group col-sm-4" data-sb-section="proxy">
    <label for="simbrief_name">Nom transmis à SimBrief</label>
    <input class="form-control" id="simbrief_name" name="simbrief_name" maxlength="12"
           value="{{ old('simbrief_name', $sb['name'] ?? '') }}" placeholder="NORD 262">
    <p class="help-block">1 à 12 caractères. Le nom d’affichage Prométhée reste inchangé.</p>
    <p class="text-danger">{{ $errors->first('simbrief_name') }}</p>
  </div>
</div>
<div class="row" data-sb-section="proxy">
  <div class="form-group col-sm-4">
    <label for="simbrief_engines">Moteur transmis</label>
    <input class="form-control" id="simbrief_engines" name="simbrief_engines" maxlength="12"
           value="{{ old('simbrief_engines', $sb['engines'] ?? '') }}" placeholder="BASTAN VIC">
    <p class="help-block">1 à 12 caractères, conformément à l’API SimBrief.</p>
    <p class="text-danger">{{ $errors->first('simbrief_engines') }}</p>
  </div>
</div>

<div data-sb-section="proxy">
  <h5>Aircraft data overrides</h5>
  <div class="row">
    <div class="form-group col-sm-3">
      <label for="simbrief_maxpax">PAX maximum réel</label>
      <input type="number" min="0" max="999" class="form-control" id="simbrief_maxpax" name="simbrief_maxpax"
             value="{{ old('simbrief_maxpax', $sb['maxpax'] ?? '') }}">
    </div>
    <div class="form-group col-sm-3">
      <label for="simbrief_ceiling">Plafond (ft)</label>
      <input type="number" min="0" max="70000" class="form-control" id="simbrief_ceiling" name="simbrief_ceiling"
             value="{{ old('simbrief_ceiling', $sb['ceiling'] ?? '') }}">
    </div>
    <div class="form-group col-sm-3">
      <label for="simbrief_per">Catégorie performance ICAO</label>
      <select class="form-control" id="simbrief_per" name="simbrief_per">
        <option value="">AUTO</option>
        @foreach(['A','B','C','D','E'] as $value)
          <option value="{{ $value }}" @selected(old('simbrief_per', $sb['per'] ?? '') === $value)>{{ $value }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group col-sm-3">
      <label for="simbrief_cruiseoffset">Cruise offset</label>
      <input class="form-control" id="simbrief_cruiseoffset" name="simbrief_cruiseoffset"
             value="{{ old('simbrief_cruiseoffset', $sb['cruiseoffset'] ?? '') }}" placeholder="P2000 / M0500">
    </div>
  </div>

  <div class="row">
    <div class="form-group col-sm-4">
      <label for="simbrief_cat">Wake category</label>
      <select class="form-control" id="simbrief_cat" name="simbrief_cat">
        <option value="">AUTO</option>
        @foreach(['L','M','H','J'] as $value)
          <option value="{{ $value }}" @selected(old('simbrief_cat', $sb['cat'] ?? '') === $value)>{{ $value }}</option>
        @endforeach
      </select>
      <p class="help-block">cat/equip/transponder doivent être tous renseignés ensemble.</p>
    </div>
    <div class="form-group col-sm-4">
      <label for="simbrief_equip">Equipment</label>
      <input class="form-control" id="simbrief_equip" name="simbrief_equip"
             value="{{ old('simbrief_equip', $sb['equip'] ?? '') }}">
    </div>
    <div class="form-group col-sm-4">
      <label for="simbrief_transponder">Transponder</label>
      <input class="form-control" id="simbrief_transponder" name="simbrief_transponder"
             value="{{ old('simbrief_transponder', $sb['transponder'] ?? '') }}">
    </div>
  </div>

  <div class="row">
    <div class="form-group col-sm-4">
      <label for="simbrief_pbn">PBN</label>
      <input class="form-control" id="simbrief_pbn" name="simbrief_pbn"
             value="{{ old('simbrief_pbn', $sb['pbn'] ?? '') }}">
    </div>
    <div class="form-group col-sm-4">
      <label for="simbrief_hexcode">Mode-S hexcode</label>
      <input class="form-control" id="simbrief_hexcode" name="simbrief_hexcode"
             value="{{ old('simbrief_hexcode', $sb['hexcode'] ?? '') }}">
    </div>
    <div class="form-group col-sm-4">
      <label for="simbrief_extrarmk">Section 18 / remarques</label>
      <input class="form-control" id="simbrief_extrarmk" name="simbrief_extrarmk"
             value="{{ old('simbrief_extrarmk', $sb['extrarmk'] ?? '') }}">
    </div>
  </div>

  <h5>Masses réelles — kg</h5>
  <p class="text-muted">Stockées en kg dans sb-airframe. La conversion SimBrief en milliers de livres est effectuée uniquement par l’adaptateur.</p>
  <div class="row">
    @foreach([
      'oew' => 'OEW',
      'mzfw' => 'MZFW',
      'mtow' => 'MTOW',
      'mlw' => 'MLW',
      'maxfuel' => 'Carburant max',
      'maxcargo' => 'Cargo max',
    ] as $key => $label)
      <div class="form-group col-sm-2">
        <label for="simbrief_{{ $key }}_kg">{{ $label }}</label>
        <input type="number" min="0" step="0.01" class="form-control"
               id="simbrief_{{ $key }}_kg" name="simbrief_{{ $key }}_kg"
               value="{{ old('simbrief_'.$key.'_kg', $weights[$key] ?? '') }}">
      </div>
    @endforeach
  </div>

  <div class="row">
    <div class="form-group col-sm-3">
      <label for="simbrief_paxwgt">Poids PAX (lb)</label>
      <input type="number" min="0" step="0.1" class="form-control" id="simbrief_paxwgt" name="simbrief_paxwgt"
             value="{{ old('simbrief_paxwgt', $sb['paxwgt'] ?? '') }}">
    </div>
    <div class="form-group col-sm-3">
      <label for="simbrief_bagwgt">Poids bagage (lb)</label>
      <input type="number" min="0" step="0.1" class="form-control" id="simbrief_bagwgt" name="simbrief_bagwgt"
             value="{{ old('simbrief_bagwgt', $sb['bagwgt'] ?? '') }}">
    </div>
  </div>

  <h5>Performance corrections</h5>
  <div class="row">
    @foreach([
      'fuelfactor' => ['Fuel factor', 'P00'],
      'climb' => ['Climb', 'AUTO'],
      'cruise' => ['Cruise', 'AUTO'],
      'descent' => ['Descent', 'AUTO'],
    ] as $key => [$label, $placeholder])
      <div class="form-group col-sm-3">
        <label for="simbrief_{{ $key }}">{{ $label }}</label>
        <input class="form-control" id="simbrief_{{ $key }}" name="simbrief_{{ $key }}"
               value="{{ old('simbrief_'.$key, $performance[$key] ?? '') }}" placeholder="{{ $placeholder }}">
      </div>
    @endforeach
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="text-right">
      {{ Form::hidden('source', \App\Models\Enums\AirframeSource::INTERNAL) }}
      {{ Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-success']) }}
    </div>
  </div>
</div>

@push('scripts')
<script>
(function () {
  const select = document.getElementById('simbrief_strategy');
  if (!select) return;
  const refresh = () => {
    document.querySelectorAll('[data-sb-section]').forEach((node) => {
      const section = node.getAttribute('data-sb-section');
      node.style.display = section === select.value ? '' : 'none';
    });
  };
  select.addEventListener('change', refresh);
  refresh();
})();
</script>
@endpush
