@extends('promethee::layout')

@section('title', 'Édition groupée du carburant')

@section('content')
<div class="page-heading"><div><span class="eyebrow">PRIX DU CARBURANT</span><h1>Édition groupée.</h1><p>La modification sera appliquée à tous les aéroports des périmètres sélectionnés.</p></div><a class="button outline" href="{{ route('admin.promethee.economy') }}#prix-carburant">Retour aux tarifs carburant</a></div>
<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">PÉRIMÈTRES</span><h2>{{ count($selectedScopes) }} périmètre(s) retenu(s)</h2><p>Les périmètres Pays entier et Province peuvent se recouper : chaque aéroport ne sera modifié qu’une seule fois.</p></div></div>
    <form method="post" action="{{ route('admin.promethee.economy.fuel-prices') }}">
        @csrf
        @foreach($selectedScopes as $scope)<input type="hidden" name="scope_keys[]" value="{{ $scope }}">@endforeach
        <div class="form-grid"><label>Carburant <select name="fuel_type"><option value="fuel_jeta_cost">Jet A</option><option value="fuel_100ll_cost">100LL</option><option value="fuel_mogas_cost">Mogas</option></select></label><label>Opération <select name="mode"><option value="set">Fixer le prix</option><option value="add">Ajouter / retirer un montant</option><option value="percent">Augmenter / réduire en %</option></select></label><label>Valeur <input name="value" type="number" step="0.0001" required placeholder="Ex. 1.25 ou -10"></label><button type="submit" @disabled(!count($selectedScopes))>Enregistrer la modification</button></div>
    </form>
</section>
@endsection
