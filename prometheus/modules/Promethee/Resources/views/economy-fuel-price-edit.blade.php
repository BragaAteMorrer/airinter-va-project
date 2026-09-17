@extends('promethee::layout')

@section('title', 'Modifier les prix du carburant')

@section('content')
<div class="page-heading">
    <div><span class="eyebrow">PRIX DU CARBURANT</span><h1>Modifier {{ $country }}{{ $region ? ' · '.$region : '' }}.</h1><p>Cette fiche applique le changement aux {{ $airportCount }} aéroport(s) du périmètre.</p></div>
    <a class="button outline" href="{{ route('admin.promethee.economy') }}#prix-carburant">Retour aux tarifs carburant</a>
</div>
<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">PÉRIMÈTRE SÉLECTIONNÉ</span><h2>{{ $country }}{{ $region ? ' · '.$region : '' }}</h2><p>Prix par litre. « Variés » signifie que les aéroports n’ont pas encore tous la même valeur ; l’opération « Fixer » les uniformise.</p></div></div>
    <div class="form-grid"><div><span class="eyebrow">JET A</span><p><strong>{{ $prices['fuel_jeta_cost'] ?? 'Variés / défaut' }}</strong> €/L</p></div><div><span class="eyebrow">100LL</span><p><strong>{{ $prices['fuel_100ll_cost'] ?? 'Variés / défaut' }}</strong> €/L</p></div><div><span class="eyebrow">MOGAS</span><p><strong>{{ $prices['fuel_mogas_cost'] ?? 'Variés / défaut' }}</strong> €/L</p></div></div>
</section>
<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">MODIFICATION</span><h2>Nouveau prix carburant</h2><p>Le changement est inscrit dans l’historique pour chaque aéroport concerné.</p></div></div>
    <form method="post" action="{{ route('admin.promethee.economy.fuel-prices') }}">
        @csrf<input type="hidden" name="country" value="{{ $country }}"><input type="hidden" name="region" value="{{ $region }}">
        <div class="form-grid"><label>Carburant <select name="fuel_type"><option value="fuel_jeta_cost">Jet A</option><option value="fuel_100ll_cost">100LL</option><option value="fuel_mogas_cost">Mogas</option></select></label><label>Opération <select name="mode"><option value="set">Fixer le prix</option><option value="add">Ajouter / retirer un montant</option><option value="percent">Augmenter / réduire en %</option></select></label><label>Valeur <input name="value" type="number" step="0.0001" required placeholder="Ex. 1.25 ou -10"></label><button type="submit">Enregistrer le prix carburant</button></div>
    </form>
</section>
@endsection
