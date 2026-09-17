@extends('promethee::layout')

@section('title', 'Modifier le prix d’une ligne')

@section('content')
<div class="page-heading">
    <div><span class="eyebrow">PRIX DES VOLS</span><h1>Modifier {{ $flight->ident }}.</h1><p>Cette fiche ne modifie que la ligne sélectionnée.</p></div>
    <a class="button outline" href="{{ route('admin.promethee.economy') }}#prix-vols">Retour aux lignes</a>
</div>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">LIGNE SÉLECTIONNÉE</span><h2>{{ $flight->airline?->icao }} {{ $flight->ident }}</h2><p>{{ $flight->dpt_airport_id }} · {{ $flight->dpt_airport?->name }} <strong>→</strong> {{ $flight->arr_airport_id }} · {{ $flight->arr_airport?->name }}</p></div></div>
    <div class="form-grid">
        <div><span class="eyebrow">TARIF</span><p>{{ $fareCode }} · {{ $fare?->name ?? 'Tarif de référence' }}</p></div>
        <div><span class="eyebrow">PRIX ACTUEL</span><p><strong>{{ $price }} {{ setting('units.currency', 'EUR') }}</strong> {{ $fareCode === 'CGO' ? '/ kg' : '/ passager' }}</p></div>
        <div><span class="eyebrow">COULEUR</span><p>{{ ucfirst($band) }}</p></div>
    </div>
</section>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">MODIFICATION</span><h2>Nouveau prix</h2><p>Le changement est enregistré dans l’historique des prix.</p></div></div>
    <form method="post" action="{{ route('admin.promethee.economy.flight-prices') }}">
        @csrf
        <input type="hidden" name="flight_ids[]" value="{{ $flight->id }}">
        <div class="form-grid">
            <label>Opération <select name="mode"><option value="set">Fixer le prix rouge</option><option value="add">Ajouter / retirer un montant</option><option value="percent">Augmenter / réduire en %</option><option value="band">Changer uniquement la couleur</option></select></label>
            <label>Valeur <input name="value" type="number" step="0.01" value="{{ $price }}"></label>
            <label>Tarif de la ligne <select name="band"><option value="keep">Conserver {{ ucfirst($band) }}</option><option value="rouge" @selected($band === 'rouge')>Rouge · plein tarif</option><option value="blanc" @selected($band === 'blanc')>Blanc · tarif réduit</option><option value="bleu" @selected($band === 'bleu')>Bleu · tarif réduit</option></select></label>
            <button type="submit">Enregistrer le prix de cette ligne</button>
        </div>
    </form>
</section>
@endsection
