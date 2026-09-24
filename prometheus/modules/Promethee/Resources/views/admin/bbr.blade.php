@extends('promethee::layout')

@section('title', 'Prométhée · Tarifs Bleu-Blanc-Rouge')

@section('content')
<div class="page-heading">
  <div>
    <span class="eyebrow">DIRECTION COMMERCIALE</span>
    <h1>Tarifs Bleu-Blanc-Rouge.</h1>
    <p>Définissez la modulation tarifaire Air Inter et les plages de remplissage utilisées par Prométhée et Hermès.</p>
  </div>
  <a class="button outline" href="{{ route('admin.promethee.economy') }}">Ouvrir l’économie</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">CALENDRIER AIR INTER</span>
      <h2>Tarification BBR</h2>
      <p>Rouge reste le plein tarif de référence. Blanc et Bleu sont exprimés en pourcentage de ce tarif Rouge.</p>
    </div>
  </div>

  <form method="post" action="{{ route('admin.promethee.bbr.save') }}">
    @csrf

    <div class="form-grid">
      <label>
        <span>Activer Bleu-Blanc-Rouge</span>
        <select name="enabled">
          <option value="1" @selected(old('enabled', $bbr['enabled'] ? '1' : '0') === '1')>Activé</option>
          <option value="0" @selected(old('enabled', $bbr['enabled'] ? '1' : '0') === '0')>Désactivé</option>
        </select>
      </label>

      <label>
        Tarif Bleu (% du Rouge)
        <input name="blue" type="number" min="1" max="100" step="0.1" value="{{ old('blue', $bbr['fares']['bleu']) }}" required>
        @error('blue')<small class="text-danger">{{ $message }}</small>@enderror
      </label>

      <label>
        Tarif Blanc (% du Rouge)
        <input name="white" type="number" min="1" max="100" step="0.1" value="{{ old('white', $bbr['fares']['blanc']) }}" required>
        @error('white')<small class="text-danger">{{ $message }}</small>@enderror
      </label>

      <label>
        Tarif Rouge
        <input value="100 % · plein tarif" readonly>
      </label>
    </div>

    <div class="panel-heading" style="margin-top:24px">
      <div>
        <span class="eyebrow">REMPLISSAGE</span>
        <h2>Prévision de passagers</h2>
        <p>Ces fourchettes sont utilisées lorsqu’un vol n’a pas déjà son propre load factor phpVMS. Le tirage est stable pour une même opération et un même appareil.</p>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Couleur</th><th>Minimum</th><th>Maximum</th><th>Lecture opérationnelle</th></tr>
        </thead>
        <tbody>
          @foreach([
            'blue' => ['label' => 'Bleu', 'key' => 'bleu', 'help' => 'période moins chargée · réductions les plus avantageuses'],
            'white' => ['label' => 'Blanc', 'key' => 'blanc', 'help' => 'trafic intermédiaire'],
            'red' => ['label' => 'Rouge', 'key' => 'rouge', 'help' => 'forte demande · plein tarif'],
          ] as $field => $meta)
            <tr>
              <td><strong>{{ $meta['label'] }}</strong></td>
              <td>
                <input name="{{ $field }}_min" type="number" min="1" max="100" step="0.1" value="{{ old($field.'_min', $bbr['loads'][$meta['key']]['min']) }}" required> %
                @error($field.'_min')<small class="text-danger">{{ $message }}</small>@enderror
              </td>
              <td>
                <input name="{{ $field }}_max" type="number" min="1" max="100" step="0.1" value="{{ old($field.'_max', $bbr['loads'][$meta['key']]['max']) }}" required> %
              </td>
              <td class="text-muted">{{ $meta['help'] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="panel-heading" style="margin-top:20px">
      <div>
        <p><strong>Exemple :</strong> un Airbus A319 de 144 places sur un vol Blanc à 78,4 % donnera 113 passagers. Ce chiffre restera identique à chaque rafraîchissement de cette opération.</p>
      </div>
      <button type="submit">Enregistrer le profil BBR</button>
    </div>
  </form>
</section>
@endsection
