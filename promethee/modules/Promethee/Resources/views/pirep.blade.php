@extends('promethee::layout')
@section('title', 'Rapport de vol')
@section('content')
@php
    use App\Models\Enums\PirepState;
    use App\Models\Enums\PirepStatus;
    use App\Models\Enums\PirepSource;
    use App\Support\Money;
    $duration = fn ($minutes) => $minutes === null ? '—' : floor($minutes / 60).' h '.str_pad($minutes % 60, 2, '0', STR_PAD_LEFT).' min';
    $state = PirepState::label($pirep->state);
    $status = $pirep->status ? PirepStatus::label($pirep->status) : null;
    $actualPath = $pirep->acars->filter(fn ($point) => is_numeric($point->lat) && is_numeric($point->lon))->map(fn ($point) => [(float) $point->lat, (float) $point->lon])->values();
    $plannedPath = $pirep->acars_route->filter(fn ($point) => is_numeric($point->lat) && is_numeric($point->lon))->map(fn ($point) => [(float) $point->lat, (float) $point->lon])->values();
    $airportPath = collect([$pirep->dpt_airport, $pirep->arr_airport])->filter(fn ($airport) => is_numeric($airport->lat) && is_numeric($airport->lon))->map(fn ($airport) => [(float) $airport->lat, (float) $airport->lon])->values();
    $mapPath = $actualPath->isNotEmpty() ? $actualPath : ($plannedPath->isNotEmpty() ? $plannedPath : $airportPath);
    $isOwner = auth()->check() && auth()->id() === $pirep->user_id;
    $canRepeat = $isOwner && $pirep->flight && ($pirep->submitted_at !== null
        || in_array((int) $pirep->state, [PirepState::PENDING, PirepState::ACCEPTED, PirepState::REJECTED], true));
    $displayScore = $companyScore['score'] ?? $pirep->score;
    $scoreItems = collect($companyScore['items'] ?? []);
    $unavailableScoreRules = collect($companyScore['unavailable_rules'] ?? []);
    $isHermesPirep = str_starts_with((string) $pirep->source_name, 'Hermes ACARS [op_');
@endphp
<div class="ops-header compact report-heading">
  <div>
    <span class="eyebrow">RAPPORT DE VOL · AIR INTER</span>
    <h1>{{ $pirep->ident }}</h1>
    <p>{{ $pirep->dpt_airport_id }} → {{ $pirep->arr_airport_id }} · {{ optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m/Y') ?? 'En préparation' }}</p>
    @if($isHermesPirep)
      <div class="pirep-made-by" aria-label="Made by Hermès">
        <span>Made by</span>
        <img src="{{ asset('promethee-assets/logos/hermes-logo.png') }}" alt="Hermès">
      </div>
    @endif
  </div>
  <div class="toolbar no-print">
    @if($isOwner && !$pirep->read_only)
      <a class="button outline" href="{{ route('frontend.pireps.edit', $pirep->id) }}">Modifier le rapport</a>
    @endif
    @if($isOwner && !$pirep->read_only)
      <form method="post" action="{{ route('frontend.pireps.submit', $pirep->id) }}">@csrf<button type="submit">Soumettre</button></form>
    @endif
    <a class="button outline" href="{{ route('promethee.replay', $pirep->id) }}">Ouvrir le Replay</a>
    @if($canRepeat)
      <form method="post" action="{{ route('promethee.pireps.repeat', $pirep->id) }}">
        @csrf
        <button class="button outline" type="submit">Refaire ce vol</button>
      </form>
    @endif
    @if($canDeletePirep ?? false)
      <form method="post" action="{{ route('promethee.pireps.delete', $pirep->id) }}" onsubmit="return confirm('Supprimer définitivement ce PIREP ?\n\nCette tentative, sa télémétrie et ses données Hermès seront supprimées. Cette action est irréversible.');">
        @csrf @method('DELETE')
        <button class="button danger" type="submit">Abandonner le PIREP</button>
      </form>
    @endif
  </div>
</div>
@if($errors->has('reservation'))
  <div class="alert alert-danger no-print">{{ $errors->first('reservation') }}</div>
@endif
<section class="control-strip report-strip">
  <article><span>État</span><strong>{{ $state }}</strong><small>{{ $status ?: 'Rapport de vol' }}</small></article>
  <article><span>Temps de vol</span><strong>{{ $duration($pirep->flight_time) }}</strong><small>Bloc : {{ $duration($pirep->block_time) }}</small></article>
  <article><span>Distance</span><strong>{{ $pirep->distance ? number_format($pirep->distance->toUnit('nmi'), 0, ',', ' ') : '—' }}</strong><small>milles nautiques</small></article>
  <article><span>Atterrissage</span><strong>{{ $pirep->landing_rate !== null ? number_format($pirep->landing_rate, 0, ',', ' ') : '—' }}</strong><small>ft/min</small></article>
  <article><span>Passagers</span><strong>{{ $passengerCount !== null ? number_format($passengerCount, 0, ',', ' ') : '—' }}</strong><small>{{ $passengerSource ? 'source '.$passengerSource : 'chargement non disponible' }}</small></article>
  <article><span>Score compagnie</span><strong>{{ $displayScore !== null ? $displayScore.'/100' : '—' }}</strong><small>{{ ($companyScore['available'] ?? false) ? '−'.(int) ($companyScore['penalty_total'] ?? 0).' pts appliqués' : 'barème indisponible' }}</small></article>
</section>
<section class="panel route-summary"><div class="airport-card"><span class="eyebrow">DÉPART</span><strong>{{ $pirep->dpt_airport_id }}</strong><p>{{ $pirep->dpt_airport?->full_name ?: $pirep->dpt_airport?->name }}</p><small>{{ $pirep->block_off_time ? $pirep->block_off_time->setTimezone('Europe/Paris')->format('d/m/Y · H:i') : 'Heure non relevée' }}</small></div><div class="route-line"><i>✈</i><span>{{ $pirep->progress_percent }}% du trajet</span></div><div class="airport-card arrival"><span class="eyebrow">ARRIVÉE</span><strong>{{ $pirep->arr_airport_id }}</strong><p>{{ $pirep->arr_airport?->full_name ?: $pirep->arr_airport?->name }}</p><small>{{ $pirep->block_on_time ? $pirep->block_on_time->setTimezone('Europe/Paris')->format('d/m/Y · H:i') : 'Heure non relevée' }}</small></div></section>
<div class="report-tabs" role="tablist"><button class="active" data-report-tab="map" role="tab">Carte et trace</button><button data-report-tab="log" role="tab">Journal de vol <span>{{ $flightJournal->count() }}</span></button><button data-report-tab="score" role="tab">Score compagnie <span>{{ $scoreItems->count() }}</span></button><button data-report-tab="finance" role="tab">Finances <span>{{ $finance['company_transactions']->count() }}</span></button><button data-report-tab="details" role="tab">Informations</button></div>
<section class="panel report-tab-panel" data-report-panel="map"><div class="panel-heading"><div><span class="eyebrow">TRAJECTOIRE</span><h2>Route effectuée</h2></div><small class="mono muted">{{ $actualPath->isNotEmpty() ? 'Trace ACARS' : ($plannedPath->isNotEmpty() ? 'Route planifiée' : 'Liaison aéroports') }}</small></div><div id="pirep-map" class="ops-leaflet-map"></div>@if($pirep->route)<p class="report-route mono">{{ $pirep->route }}</p>@endif</section>
<section class="panel report-tab-panel" data-report-panel="log" hidden>
  <div class="panel-heading">
    <div><span class="eyebrow">ACARS · HERMÈS</span><h2>Journal chronologique</h2></div>
    <small class="mono muted">{{ $flightJournal->count() }} événement{{ $flightJournal->count() > 1 ? 's' : '' }}</small>
  </div>
  <div class="flight-log">
    @forelse($flightJournal as $entry)
      <article class="pirep-journal-entry">
        <time>{{ $entry['occurred_at']->setTimezone('Europe/Paris')->format('d/m/Y H:i:s') }}</time>
        <div>
          <div class="pirep-journal-meta">
            <span>{{ $entry['source'] }}</span>
            <code>{{ $entry['code'] }}</code>
          </div>
          <p>{{ $entry['message'] }}</p>
          @if($entry['detail'])<small>{{ $entry['detail'] }}</small>@endif
        </div>
      </article>
    @empty
      <p class="empty">Aucun événement ACARS ou échantillon de télémétrie Hermès n’est disponible pour ce rapport.</p>
    @endforelse
  </div>
</section>
<section class="panel report-tab-panel" data-report-panel="score" hidden>
  <div class="panel-heading">
    <div><span class="eyebrow">FDM · BARÈME VMSACARS</span><h2>Détail du score compagnie</h2></div>
    <small class="mono muted">{{ $displayScore !== null ? $displayScore.'/100' : 'Score indisponible' }}</small>
  </div>
  @if($companyScore['available'] ?? false)
    <div class="control-strip">
      <article><span>Capital initial</span><strong>{{ (int) ($companyScore['starting_score'] ?? 100) }}</strong><small>points</small></article>
      <article><span>Retraits</span><strong>−{{ (int) ($companyScore['penalty_total'] ?? 0) }}</strong><small>{{ $scoreItems->sum('occurrences') }} occurrence{{ $scoreItems->sum('occurrences') > 1 ? 's' : '' }}</small></article>
      <article><span>Score final</span><strong>{{ (int) ($companyScore['score'] ?? 0) }}/100</strong><small>version barème {{ $companyScore['version'] ?? '—' }}</small></article>
    </div>
    <div class="flight-log">
      @forelse($scoreItems as $item)
        <article class="pirep-journal-entry">
          <time>−{{ (int) ($item['deduction'] ?? 0) }} pts</time>
          <div>
            <div class="pirep-journal-meta"><span>RÈGLE COMPAGNIE</span><code>{{ $item['rule_id'] ?? '—' }}</code></div>
            <p>{{ $item['name'] ?? 'Règle de scoring' }}</p>
            <small>{{ (int) ($item['occurrences'] ?? 0) }} occurrence{{ (int) ($item['occurrences'] ?? 0) > 1 ? 's' : '' }} × {{ (int) ($item['points_each'] ?? 0) }} pt{{ (int) ($item['points_each'] ?? 0) > 1 ? 's' : '' }} = −{{ (int) ($item['deduction'] ?? 0) }} pts</small>
            @foreach(($item['events'] ?? []) as $event)
              <small class="mono">{{ $event['code'] ?? strtoupper((string) ($event['source'] ?? 'FDM')) }} · {{ $event['value'] ?? '—' }}{{ !empty($event['unit']) ? ' '.$event['unit'] : '' }}{{ !empty($event['at']) ? ' · '.$event['at'] : '' }}</small>
            @endforeach
          </div>
        </article>
      @empty
        <p class="empty">Aucun retrait de point : le vol conserve les {{ (int) ($companyScore['starting_score'] ?? 100) }} points du barème compagnie.</p>
      @endforelse
    </div>
    @if($unavailableScoreRules->isNotEmpty())
      <div class="alert alert-warning">
        <strong>{{ $unavailableScoreRules->count() }} règle{{ $unavailableScoreRules->count() > 1 ? 's' : '' }} non évaluée{{ $unavailableScoreRules->count() > 1 ? 's' : '' }}</strong>
        <span>Ces règles n’ont généré aucune pénalité : les données nécessaires étaient absentes ou insuffisantes.</span>
      </div>
      <div class="flight-log score-unavailable-rules">
        @foreach($unavailableScoreRules as $rule)
          <article class="pirep-journal-entry">
            <time>NON ÉVALUÉE</time>
            <div>
              <div class="pirep-journal-meta">
                <span>RÈGLE COMPAGNIE</span>
                <code>{{ $rule['rule_id'] ?? '—' }}</code>
              </div>
              <p>{{ $rule['name'] ?? 'Règle de scoring' }}</p>
              <small>{{ $rule['reason'] ?? 'Télémétrie Hermès insuffisante pour cette règle.' }}</small>
              @if(!empty($rule['points']))
                <small class="mono">Aucun retrait appliqué · barème potentiel : {{ (int) $rule['points'] }} pt{{ (int) $rule['points'] > 1 ? 's' : '' }}</small>
              @endif
            </div>
          </article>
        @endforeach
      </div>
    @endif
  @else
    <p class="empty">Le détail du barème compagnie n’est pas disponible pour ce rapport.</p>
  @endif
</section>
<section class="panel report-tab-panel" data-report-panel="finance" hidden>
  <div class="panel-heading">
    <div><span class="eyebrow">ÉCONOMIE · PHPVMS</span><h2>Compte d’exploitation du vol</h2></div>
    <small class="mono muted">{{ $finance['company_transactions']->count() }} écriture{{ $finance['company_transactions']->count() > 1 ? 's' : '' }} compagnie</small>
  </div>

  <section class="pirep-finance-kpis">
    <article>
      <small>Recettes</small>
      <strong>{{ (string) $finance['credits'] }}</strong>
      <span>Journal compagnie</span>
    </article>
    <article>
      <small>Dépenses</small>
      <strong>{{ (string) $finance['debits'] }}</strong>
      <span>Charges d’exploitation</span>
    </article>
    <article>
      <small>Résultat opérationnel</small>
      <strong class="{{ $finance['net']->getAmount() < 0 ? 'negative' : 'positive' }}">{{ (string) $finance['net'] }}</strong>
      <span>Recettes − dépenses</span>
    </article>
    <article>
      <small>Marge opérationnelle</small>
      <strong>{{ $finance['margin'] !== null ? number_format($finance['margin'], 1, ',', ' ').' %' : '—' }}</strong>
      <span>Résultat / recettes</span>
    </article>
    <article>
      <small>Rémunération pilote</small>
      <strong>{{ (string) $finance['pilot_net'] }}</strong>
      <span>Journal pilote séparé</span>
    </article>
    <article>
      <small>Comptabilisation</small>
      <strong>{{ $finance['company_transactions']->isNotEmpty() ? 'Traitée' : 'En attente' }}</strong>
      <span>{{ $finance['company_transactions']->isNotEmpty() ? 'Écritures phpVMS rattachées' : 'Traitée à l’acceptation du PIREP' }}</span>
    </article>
  </section>

  @if($finance['company_transactions']->isNotEmpty())
    <div class="pirep-finance-grid">
      <article class="pirep-finance-card pirep-finance-card-wide">
        <div class="panel-heading compact">
          <div><span class="eyebrow">DÉTAIL COMPTABLE</span><h3>Recettes et charges du vol</h3></div>
        </div>
        <div class="table-wrap">
          <table class="pirep-finance-table">
            <thead>
              <tr>
                <th>Poste</th>
                <th>Catégorie</th>
                <th class="amount">Recettes</th>
                <th class="amount">Dépenses</th>
                <th class="amount">Solde</th>
              </tr>
            </thead>
            <tbody>
              @foreach($finance['rows'] as $row)
                <tr>
                  <td>
                    <strong>{{ $row['label'] }}</strong>
                    @if($row['transaction']->memo && $row['transaction']->memo !== $row['label'])
                      <small>{{ $row['transaction']->memo }}</small>
                    @endif
                  </td>
                  <td><span class="finance-category">{{ $row['category'] }}</span></td>
                  <td class="amount positive">{{ $row['credit'] ? (string) $row['credit'] : '—' }}</td>
                  <td class="amount negative">{{ $row['debit'] ? (string) $row['debit'] : '—' }}</td>
                  <td class="amount {{ $row['is_credit'] ? 'positive' : 'negative' }}">
                    {{ $row['is_credit'] ? '+' : '−' }}{{ (string) $row['amount'] }}
                  </td>
                </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th colspan="2">TOTAL</th>
                <th class="amount positive">{{ (string) $finance['credits'] }}</th>
                <th class="amount negative">{{ (string) $finance['debits'] }}</th>
                <th class="amount {{ $finance['net']->getAmount() < 0 ? 'negative' : 'positive' }}">{{ (string) $finance['net'] }}</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </article>

      <article class="pirep-finance-card">
        <div class="panel-heading compact">
          <div><span class="eyebrow">STRUCTURE DES COÛTS</span><h3>Dépenses par catégorie</h3></div>
        </div>
        <div class="finance-category-list">
          @forelse($finance['category_summary'] as $category)
            <div class="finance-category-row">
              <div>
                <strong>{{ $category['category'] }}</strong>
                <small>{{ number_format($category['share'], 1, ',', ' ') }} % des dépenses</small>
              </div>
              <span>{{ (string) $category['amount'] }}</span>
            </div>
          @empty
            <p class="empty">Aucune dépense catégorisée pour ce vol.</p>
          @endforelse
        </div>
      </article>

      <article class="pirep-finance-card">
        <div class="panel-heading compact">
          <div><span class="eyebrow">INDICATEURS</span><h3>Performance du vol</h3></div>
        </div>
        <dl class="finance-indicators">
          <div><dt>Passagers</dt><dd>{{ $passengerCount !== null ? number_format($passengerCount, 0, ',', ' ') : '—' }}</dd></div>
          <div><dt>Revenu moyen / passager</dt><dd>{{ $finance['revenue_per_passenger'] ? (string) $finance['revenue_per_passenger'] : '—' }}</dd></div>
          <div><dt>Coût moyen / passager</dt><dd>{{ $finance['cost_per_passenger'] ? (string) $finance['cost_per_passenger'] : '—' }}</dd></div>
          <div><dt>Résultat / passager</dt><dd>{{ $finance['net_per_passenger'] ? (string) $finance['net_per_passenger'] : '—' }}</dd></div>
          <div><dt>Marge opérationnelle</dt><dd>{{ $finance['margin'] !== null ? number_format($finance['margin'], 1, ',', ' ').' %' : '—' }}</dd></div>
        </dl>
      </article>
    </div>
  @else
    <p class="empty">Aucune écriture financière compagnie n’est encore rattachée à ce PIREP. Les recettes et dépenses apparaissent après le traitement financier phpVMS du rapport.</p>
  @endif

  @if($finance['pilot_transactions']->isNotEmpty())
    <section class="pirep-finance-card pilot-finance-card">
      <div class="panel-heading">
        <div><span class="eyebrow">PILOTE</span><h2>Rémunération liée au vol</h2></div>
        <small class="mono muted">{{ $finance['pilot_transactions']->count() }} écriture{{ $finance['pilot_transactions']->count() > 1 ? 's' : '' }}</small>
      </div>
      <div class="table-wrap">
        <table class="pirep-finance-table">
          <thead><tr><th>Date</th><th>Écriture</th><th>Libellé</th><th class="amount">Montant</th></tr></thead>
          <tbody>
            @foreach($finance['pilot_transactions'] as $transaction)
              @php
                $pilotCredit = (int) ($transaction->credit ?? 0);
                $pilotDebit = (int) ($transaction->debit ?? 0);
                $pilotAmount = $pilotCredit - $pilotDebit;
              @endphp
              <tr>
                <td>{{ $transaction->post_date ? $transaction->post_date->setTimezone('Europe/Paris')->format('d/m/Y H:i:s') : '—' }}</td>
                <td><code>{{ $transaction->transaction_group ?: 'PIREP' }}</code></td>
                <td>{{ $transaction->memo ?: 'Écriture pilote phpVMS' }}</td>
                <td class="amount {{ $pilotAmount >= 0 ? 'positive' : 'negative' }}">{{ $pilotAmount >= 0 ? '+' : '−' }}{{ (string) new Money(abs($pilotAmount)) }}</td>
              </tr>
            @endforeach
          </tbody>
          <tfoot>
            <tr><th colspan="3">TOTAL CRÉDITÉ AU PILOTE</th><th class="amount positive">{{ (string) $finance['pilot_net'] }}</th></tr>
          </tfoot>
        </table>
      </div>
    </section>
  @endif

  <details class="finance-raw-details">
    <summary>Voir le journal phpVMS brut</summary>
    <div class="flight-log">
      @foreach($finance['company_transactions'] as $transaction)
        @php
          $credit = (int) ($transaction->credit ?? 0);
          $debit = (int) ($transaction->debit ?? 0);
          $amount = $credit - $debit;
        @endphp
        <article class="pirep-journal-entry">
          <time>{{ $transaction->post_date ? $transaction->post_date->setTimezone('Europe/Paris')->format('d/m/Y H:i:s') : '—' }}</time>
          <div>
            <div class="pirep-journal-meta">
              <span>{{ $amount >= 0 ? 'RECETTE' : 'DÉPENSE' }}</span>
              <code>{{ $transaction->transaction_group ?: 'PIREP' }}</code>
            </div>
            <p>{{ $transaction->memo ?: 'Écriture financière phpVMS' }}</p>
            <small>{{ $amount >= 0 ? '+' : '−' }}{{ (string) new Money(abs($amount)) }}{{ !empty($transaction->tags) ? ' · '.implode(' · ', (array) $transaction->tags) : '' }}</small>
          </div>
        </article>
      @endforeach
    </div>
  </details>
</section>
<section class="report-tab-panel" data-report-panel="details" hidden>
  <div class="two-columns report-details-grid"><section class="panel"><div class="panel-heading"><div><span class="eyebrow">VOL</span><h2>Informations de mission</h2></div></div><dl class="report-list"><div><dt>Pilote</dt><dd>{{ $pirep->user?->pilot_id }} · {{ $pirep->user?->name ?: '—' }}</dd></div><div><dt>Appareil</dt><dd>{{ $pirep->aircraft?->registration ?: '—' }} {{ $pirep->aircraft?->icao ? '· '.$pirep->aircraft->icao : '' }}</dd></div><div><dt>Source</dt><dd>{{ PirepSource::label($pirep->source) }}</dd></div><div><dt>Niveau / type</dt><dd>{{ $pirep->level ? 'FL'.$pirep->level : '—' }} · {{ \App\Models\Enums\FlightType::label($pirep->flight_type) }}</dd></div><div><dt>Route déposée</dt><dd class="mono">{{ $pirep->route ?: '—' }}</dd></div>
@if($pirep->notes)<div><dt>Notes</dt><dd>{{ $pirep->notes }}</dd></div>@endif
  </dl></section><section class="panel"><div class="panel-heading"><div><span class="eyebrow">EXPLOITATION</span><h2>Carburant et classes</h2></div></div><dl class="report-list"><div><dt>Carburant bloc</dt><dd>{{ $pirep->block_fuel ? number_format($pirep->block_fuel->local(), 0, ',', ' ') .' '.$pirep->block_fuel->localUnit : '—' }}</dd></div><div><dt>Carburant utilisé</dt><dd>{{ $pirep->fuel_used ? number_format($pirep->fuel_used->local(), 0, ',', ' ') .' '.$pirep->fuel_used->localUnit : '—' }}</dd></div><div><dt>Passagers transportés</dt><dd>{{ $passengerCount !== null ? number_format($passengerCount, 0, ',', ' ') : '—' }}{{ $passengerSource ? ' · '.$passengerSource : '' }}</dd></div>
@forelse($pirep->fares as $fare)<div><dt>{{ $fare->name }} ({{ $fare->code }})</dt><dd>{{ $fare->count }}{{ (int) $fare->type === 0 ? ' pax' : '' }}</dd></div>@empty<div><dt>Répartition par classe</dt><dd>Non disponible sur ce rapport</dd></div>@endforelse
  </dl></section></div>
@if($pirep->field_values->isNotEmpty())
  <section class="panel"><div class="panel-heading"><div><span class="eyebrow">CHAMPS COMPLÉMENTAIRES</span><h2>Rapport détaillé</h2></div></div><dl class="report-list two-up">@foreach($pirep->field_values as $field)<div><dt>{{ $field->name }}</dt><dd>{{ $field->value ?: '—' }}</dd></div>@endforeach</dl></section>
@endif
@if($pirep->comments->isNotEmpty())
  <section class="panel"><div class="panel-heading"><div><span class="eyebrow">ÉCHANGES</span><h2>Commentaires</h2></div></div><div class="flight-log">@foreach($pirep->comments as $comment)<article><time>{{ $comment->user?->name ?: 'Équipe Air Inter' }} · {{ optional($comment->created_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</time><p>{{ $comment->comment }}</p></article>@endforeach</div></section>
@endif
</section>
@push('styles')
<style>
.pirep-finance-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.8rem;margin:0 0 1rem}.pirep-finance-kpis article{border:1px solid #dce6ee;border-radius:12px;padding:.9rem 1rem;background:linear-gradient(180deg,#fff,#f8fbfd)}.pirep-finance-kpis small,.pirep-finance-kpis span{display:block}.pirep-finance-kpis small{font-size:.72rem;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#6d7f91}.pirep-finance-kpis strong{display:block;margin:.2rem 0;font-size:1.25rem;color:#173a5e}.pirep-finance-kpis span{font-size:.74rem;color:#8795a3}
.pirep-finance-grid{display:grid;grid-template-columns:minmax(0,2fr) minmax(260px,.8fr);gap:1rem}.pirep-finance-card{border:1px solid #dce6ee;border-radius:12px;background:#fff;padding:1rem;min-width:0}.pirep-finance-card-wide{grid-row:span 2}.panel-heading.compact{margin-bottom:.6rem}.panel-heading.compact h3{margin:.1rem 0;color:#173a5e}
.pirep-finance-table{width:100%;border-collapse:collapse;font-size:.82rem}.pirep-finance-table th{background:#edf3f7;color:#43576c;text-align:left;padding:.62rem;border-bottom:1px solid #d7e1e9}.pirep-finance-table td{padding:.62rem;border-bottom:1px solid #edf2f5;vertical-align:top}.pirep-finance-table td strong,.pirep-finance-table td small{display:block}.pirep-finance-table td small{margin-top:.18rem;color:#8795a3}.pirep-finance-table .amount{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.pirep-finance-table tfoot th{border-top:2px solid #cfdbe5;background:#f7fafc}.finance-category{display:inline-block;border-radius:999px;padding:.24rem .48rem;background:#edf3f7;color:#52677c;font-size:.7rem;font-weight:800;white-space:nowrap}
.finance-category-list{display:grid;gap:.2rem}.finance-category-row{display:flex;justify-content:space-between;gap:1rem;padding:.68rem 0;border-bottom:1px solid #edf2f5}.finance-category-row:last-child{border-bottom:0}.finance-category-row strong,.finance-category-row small{display:block}.finance-category-row small{margin-top:.15rem;color:#8795a3;font-size:.75rem}.finance-category-row>span{font-weight:900;color:#173a5e;white-space:nowrap}
.finance-indicators{margin:0}.finance-indicators>div{display:flex;justify-content:space-between;gap:1rem;padding:.62rem 0;border-bottom:1px solid #edf2f5}.finance-indicators>div:last-child{border-bottom:0}.finance-indicators dt{color:#66798c}.finance-indicators dd{margin:0;font-weight:900;color:#173a5e;text-align:right}.pilot-finance-card{margin-top:1rem}.finance-raw-details{margin-top:1rem;border-top:1px solid #dce6ee;padding-top:.8rem}.finance-raw-details summary{cursor:pointer;font-weight:800;color:#52677c}.positive{color:#18884a!important}.negative{color:#c03939!important}
@media(max-width:1000px){.pirep-finance-kpis{grid-template-columns:repeat(2,1fr)}.pirep-finance-grid{grid-template-columns:1fr}.pirep-finance-card-wide{grid-row:auto}}
@media(max-width:640px){.pirep-finance-kpis{grid-template-columns:1fr}.pirep-finance-table{font-size:.75rem}.pirep-finance-table th,.pirep-finance-table td{padding:.5rem}.finance-category{white-space:normal}}
</style>
@endpush

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script><script>(()=>{const tabs=document.querySelectorAll('[data-report-tab]'),panels=document.querySelectorAll('[data-report-panel]');tabs.forEach(tab=>tab.addEventListener('click',()=>{tabs.forEach(t=>t.classList.toggle('active',t===tab));panels.forEach(p=>p.hidden=p.dataset.reportPanel!==tab.dataset.reportTab);window.dispatchEvent(new Event('resize'));}));const path=@json($mapPath),mapNode=document.querySelector('#pirep-map');if(!mapNode||!window.L)return;const map=L.map(mapNode,{scrollWheelZoom:false,minZoom:2,maxZoom:14});L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);if(path.length>1){L.polyline(path,{color:'#155cb8',weight:4,opacity:.8}).addTo(map);L.marker(path[0]).bindTooltip('Départ').addTo(map);L.marker(path[path.length-1]).bindTooltip('Arrivée').addTo(map);map.fitBounds(path,{padding:[35,35],maxZoom:9});}else if(path.length){L.marker(path[0]).addTo(map);map.setView(path[0],7);}else{mapNode.innerHTML='<p class="empty">Aucune coordonnée n’est disponible pour tracer ce vol.</p>';}})();</script>
@endpush
@endsection
