@extends('promethee::layout')
@section('title','Critères tarifaires ITF')

@section('content')
<div class="page-heading">
  <div>
    <span class="eyebrow">DIRECTION COMMERCIALE · AIR INTER</span>
    <h1>Critères tarifaires ITF.</h1>
    <p>Classez les lignes Air Inter entre réseau principal et diagonales régionales, puis utilisez ces groupes dans l’éditeur de tarifs.</p>
  </div>
  <div>
    <a class="button outline" href="{{ route('admin.promethee.economy') }}">Économie</a>
    <a class="button outline" href="{{ route('admin.promethee.bbr') }}">Bleu-Blanc-Rouge</a>
  </div>
</div>

@if(!$itf)
<section class="panel"><div class="notice error">La compagnie ITF n’a pas été trouvée.</div></section>
@else
<section class="control-strip">
  <article><span>Lignes principales</span><strong>{{ $counts['principal'] }}</strong><small>réseau structurant</small></article>
  <article><span>Diagonales</span><strong>{{ $counts['diagonal'] }}</strong><small>liaisons régionales transversales</small></article>
  <article><span>À classer</span><strong>{{ $counts['unclassified'] }}</strong><small>aucun critère défini</small></article>
  <article><span>Périodes</span><strong>{{ $seasons->count() }}</strong><small>saisons tarifaires disponibles</small></article>
</section>

<section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">TYPOLOGIE DU RÉSEAU</span>
      <h2>Principales / diagonales régionales</h2>
      <p>Cette classification ne modifie aucun vol. Elle sert uniquement de critère de gestion tarifaire.</p>
    </div>
  </div>

  <form method="post" action="{{ route('admin.promethee.pricing-criteria.save') }}">
    @csrf
    <div class="form-grid">
      <label>Classer les lignes sélectionnées
        <select name="network_class" required>
          <option value="principal">Lignes principales</option>
          <option value="diagonal">Diagonales régionales</option>
          <option value="unclassified">Retirer le classement</option>
        </select>
      </label>
      <div style="align-self:end"><button type="submit">Appliquer le classement</button></div>
    </div>

    <div class="table-wrap">
      <table>
        <thead><tr><th><input type="checkbox" id="criteria-select-all" aria-label="Tout sélectionner"></th><th>Vol</th><th>Départ</th><th>Arrivée</th><th>Classement</th></tr></thead>
        <tbody>
        @forelse($flights as $flight)
          <tr>
            <td><input type="checkbox" name="flight_ids[]" value="{{ $flight->id }}"></td>
            <td><strong>{{ $flight->ident }}</strong></td>
            <td>{{ $flight->dpt_airport_id }} · {{ $flight->dpt_airport?->location ?: $flight->dpt_airport?->name }}</td>
            <td>{{ $flight->arr_airport_id }} · {{ $flight->arr_airport?->location ?: $flight->arr_airport?->name }}</td>
            <td>
              @if($flight->pricing_network_class === 'principal')
                <span class="tag">PRINCIPALE</span>
              @elseif($flight->pricing_network_class === 'diagonal')
                <span class="tag">DIAGONALE</span>
              @else
                <span class="muted">À classer</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="5">Aucune ligne ITF active.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </form>

  <div class="panel-heading" style="margin-top:20px">
    <div><strong>Actions tarifaires rapides</strong><p>Ouvre l’économie directement filtrée sur le groupe choisi. Tu peux ensuite « Tout sélectionner les résultats » et changer les tarifs en une seule opération.</p></div>
    <div>
      <a class="button" href="{{ route('admin.promethee.economy',['flight_airline'=>'ITF','flight_network_class'=>'principal']) }}#prix-vols">Tarifs lignes principales</a>
      <a class="button outline" href="{{ route('admin.promethee.economy',['flight_airline'=>'ITF','flight_network_class'=>'diagonal']) }}#prix-vols">Tarifs diagonales</a>
    </div>
  </div>
</section>

<section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">PÉRIODES DE L’ANNÉE</span>
      <h2>Saisons tarifaires</h2>
      <p>Les règles tarifaires peuvent déjà être rattachées à une saison. Les périodes créées ici restent compatibles avec le moteur existant de Prométhée.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.seasons') }}">Gestion avancée des saisons</a>
  </div>

  <form method="post" action="{{ route('admin.promethee.seasons.save') }}" class="form-grid">
    @csrf
    <label>Nom de la période<input name="name" required maxlength="80" placeholder="Ex. Hiver, Été, Vacances de Noël"></label>
    <label>Début<input type="date" name="starts_on" required></label>
    <label>Fin<input type="date" name="ends_on" required></label>
    <label class="full">Notes<textarea name="notes" rows="2" placeholder="Critères ou consignes tarifaires de la période"></textarea></label>
    <label><input type="checkbox" name="active" value="1"> Définir comme période active</label>
    <div><button type="submit">Créer la période</button></div>
  </form>

  <div class="table-wrap" style="margin-top:18px">
    <table>
      <thead><tr><th>Période</th><th>Début</th><th>Fin</th><th>État</th></tr></thead>
      <tbody>
      @forelse($seasons as $season)
        <tr><td><strong>{{ $season->name }}</strong></td><td>{{ $season->starts_on }}</td><td>{{ $season->ends_on }}</td><td>{{ $season->active ? 'Active' : 'Inactive' }}</td></tr>
      @empty
        <tr><td colspan="4">Aucune période définie.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</section>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">ÉVOLUTION PRÉVUE</span><h2>Décote des diagonales</h2></div></div>
  <p>La classification est prête pour appliquer plus tard une décote automatique aux diagonales (par exemple un cran tarifaire ou un pourcentage inférieur). Aucune décote automatique n’est activée pour l’instant : le niveau reste volontairement à définir.</p>
</section>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const all = document.getElementById('criteria-select-all');
  if (!all) return;
  all.addEventListener('change', () => {
    document.querySelectorAll('input[name="flight_ids[]"]').forEach((box) => { box.checked = all.checked; });
  });
});
</script>
@endpush
