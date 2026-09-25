@extends('promethee::layout')
@section('title','Finances des compagnies')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">AIR INTER · FINANCES</span>
    <h1>Finances des compagnies.</h1>
    <p>Suivi comptable par compagnie à partir des journaux phpVMS.</p>
  </div>
  <span class="tag metric-tag">{{ $financeRows->count() }} compagnie(s)</span>
</div>

<section class="panel">
  <form method="get" class="flight-filter">
    <label>Période
      <select name="period">
        <option value="month" @selected($period === 'month')>Mois en cours</option>
        <option value="6months" @selected($period === '6months')>6 derniers mois</option>
        <option value="year" @selected($period === 'year')>Année en cours</option>
      </select>
    </label>
    <button>Afficher</button>
  </form>
</section>

<section class="control-strip">
@foreach($financeRows as $row)
  <article>
    <span>{{ $row['airline']->icao }} · {{ $row['airline']->name }}</span>
    <strong>{{ $row['balance'] }}</strong>
    <small>Solde courant</small>
  </article>
@endforeach
</section>

@foreach($financeRows as $row)
  @php
    $maxMovement = max(
      1,
      collect($row['monthly'])->map(fn($point) => max(abs($point['credits']), abs($point['debits']), abs($point['net'])))->max() ?: 1
    );
  @endphp
  <section class="panel">
    <div class="panel-heading">
      <div>
        <span class="eyebrow">{{ $row['airline']->icao }}</span>
        <h2>{{ $row['airline']->name }}</h2>
      </div>
      <span class="tag">{{ $row['transactions'] }} écriture(s)</span>
    </div>

    <div class="control-strip">
      <article><span>Crédits</span><strong>{{ $row['credits'] }}</strong><small>sur la période</small></article>
      <article><span>Débits</span><strong>{{ $row['debits'] }}</strong><small>sur la période</small></article>
      <article><span>Résultat net</span><strong>{{ $row['net'] }}</strong><small>sur la période</small></article>
      <article><span>Solde</span><strong>{{ $row['balance'] }}</strong><small>journal courant</small></article>
    </div>

    <div class="finance-chart" role="img" aria-label="Évolution financière mensuelle de {{ $row['airline']->name }}">
      @foreach($row['monthly'] as $point)
        @php
          $creditWidth = round((abs($point['credits']) / $maxMovement) * 100, 1);
          $debitWidth = round((abs($point['debits']) / $maxMovement) * 100, 1);
          $netWidth = round((abs($point['net']) / $maxMovement) * 100, 1);
        @endphp
        <div class="finance-chart-row">
          <strong>{{ $point['label'] }}</strong>
          <div class="finance-bars">
            <div class="finance-bar-line"><span>Crédits</span><i style="width:{{ $creditWidth }}%"></i><b>{{ new \App\Support\Money($point['credits']) }}</b></div>
            <div class="finance-bar-line"><span>Débits</span><i style="width:{{ $debitWidth }}%"></i><b>{{ new \App\Support\Money($point['debits']) }}</b></div>
            <div class="finance-bar-line"><span>Net</span><i style="width:{{ $netWidth }}%"></i><b>{{ new \App\Support\Money($point['net']) }}</b></div>
          </div>
        </div>
      @endforeach
    </div>
  </section>
@endforeach

<section class="panel table-wrap">
  <div class="panel-heading">
    <div><span class="eyebrow">SYNTHÈSE</span><h2>Comparatif des compagnies</h2></div>
    <p>Depuis {{ $start->format('d/m/Y') }}.</p>
  </div>
  <table>
    <thead><tr><th>Compagnie</th><th>Solde courant</th><th>Crédits</th><th>Débits</th><th>Net</th><th>Écritures</th></tr></thead>
    <tbody>
    @forelse($financeRows as $row)
      <tr>
        <td><strong>{{ $row['airline']->icao }}</strong> · {{ $row['airline']->name }}</td>
        <td>{{ $row['balance'] }}</td>
        <td>{{ $row['credits'] }}</td>
        <td>{{ $row['debits'] }}</td>
        <td>{{ $row['net'] }}</td>
        <td>{{ number_format($row['transactions']) }}</td>
      </tr>
    @empty
      <tr><td colspan="6">Aucune compagnie active.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>
@endsection

@push('styles')
<style>
.finance-chart{display:grid;gap:1rem;margin-top:1.25rem}
.finance-chart-row{display:grid;grid-template-columns:minmax(90px,140px) 1fr;gap:1rem;align-items:start}
.finance-bars{display:grid;gap:.45rem}
.finance-bar-line{display:grid;grid-template-columns:70px minmax(80px,1fr) minmax(100px,auto);gap:.65rem;align-items:center}
.finance-bar-line i{display:block;height:10px;min-width:2px;border-radius:999px;background:currentColor;opacity:.55}
.finance-bar-line span,.finance-bar-line b{font-size:.82rem}
@media(max-width:760px){.finance-chart-row{grid-template-columns:1fr}.finance-bar-line{grid-template-columns:60px 1fr}.finance-bar-line b{grid-column:2}}
</style>
@endpush
