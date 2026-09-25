@extends('promethee::layout')
@section('title','Finances des compagnies')
@section('content')
<div class="ops-header compact">
  <div><span class="eyebrow">AIR INTER · FINANCES</span><h1>Finances des compagnies.</h1><p>Lecture des journaux phpVMS des compagnies, sans doublonner la comptabilité existante.</p></div>
  <span class="tag metric-tag">{{ $financeRows->count() }} compagnie(s)</span>
</div>

<section class="control-strip">
@foreach($financeRows as $row)
  <article>
    <span>{{ $row['airline']->icao }} · {{ $row['airline']->name }}</span>
    <strong>{{ $row['balance'] }}</strong>
    <small>Solde courant</small>
  </article>
@endforeach
</section>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">MOIS EN COURS</span><h2>Mouvements financiers</h2></div><p>Crédits et débits enregistrés depuis le début du mois, heure de Paris.</p></div>
  <table>
    <thead><tr><th>Compagnie</th><th>Solde courant</th><th>Crédits du mois</th><th>Débits du mois</th><th>Net du mois</th><th>Écritures</th></tr></thead>
    <tbody>
    @forelse($financeRows as $row)
      <tr><td><strong>{{ $row['airline']->icao }}</strong> · {{ $row['airline']->name }}</td><td>{{ $row['balance'] }}</td><td>{{ $row['credits'] }}</td><td>{{ $row['debits'] }}</td><td>{{ $row['net'] }}</td><td>{{ number_format($row['transactions']) }}</td></tr>
    @empty
      <tr><td colspan="6">Aucune compagnie active.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>
@endsection
