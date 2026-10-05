@extends('promethee::layout')
@section('title','SOP & scoring Hermès')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page">
<div class="ops-header compact">
    <div>
        <span class="eyebrow">AIR INTER · FLIGHT STANDARDS</span>
        <h1>SOP & scoring Hermès.</h1>
        <p>Le barème PIREP ci-dessous est la configuration réellement utilisée pour le score Hermès. Les règles SOP restent un moteur de supervision séparé, sans retrait de points.</p>
    </div>
</div>

@if(session('success')) <div class="notice success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="notice warning">{{ $errors->first() }}</div> @endif



<div class="admin-master-detail"
     id="sop-workspace"
     data-admin-master-detail
     data-workspace-key="sop"
     data-master-default="sop-score-panel">
  <aside class="admin-master-pane" aria-label="SOP et scoring">
    <div class="admin-master-toolbar">
      <label>Rechercher
        <input type="search" data-master-filter placeholder="Scoring, règle, alerte…">
      </label>
    </div>
    <div class="admin-master-list" role="tablist" aria-orientation="vertical">
      <button type="button" class="admin-master-row" data-master-target="sop-score-panel" data-master-search="barème hermès scoring pirep vmsacars points pénalités">
        <span class="admin-master-row-main"><strong>Barème Hermès</strong><small>{{ count($scoringRules) }} règle(s) de score</small></span>
        <span class="tag">SCORE</span>
      </button>
      <button type="button" class="admin-master-row" data-master-target="sop-create-panel" data-master-search="créer règle sop supervision">
        <span class="admin-master-row-main"><strong>Créer une règle SOP</strong><small>Supervision sans impact score</small></span>
        <span class="tag">+</span>
      </button>
      <button type="button" class="admin-master-row" data-master-target="sop-rules-panel" data-master-search="règles sop alertes politique compagnie">
        <span class="admin-master-row-main"><strong>Règles SOP</strong><small>{{ count($rules) }} règle(s) configurée(s)</small></span>
        <span class="tag">POLITIQUE</span>
      </button>
      <button type="button" class="admin-master-row" data-master-target="sop-alerts-panel" data-master-search="alertes dispatch évaluations ack">
        <span class="admin-master-row-main"><strong>Alertes Dispatch</strong><small>{{ count($alerts) }} évaluation(s) récente(s)</small></span>
        @if(count($alerts))<span class="tag">{{ count($alerts) }}</span>@endif
      </button>
      <div class="admin-master-empty" data-master-empty hidden>Aucune section ne correspond.</div>
    </div>
  </aside>

  <div class="admin-detail-pane">
    <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>
    <div class="admin-detail-panel" data-detail-panel="sop-score-panel"><section class="panel admin-workspace-section" id="sop-scoring">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">BARÈME HERMÈS · SOURCE AUTORITATIVE</span>
            <h2>Score PIREP / vmsACARS</h2>
        </div>
        <span>{{ count($scoringRules) }} règle(s)</span>
    </div>
    <p class="muted">
        Ces valeurs proviennent directement de <code>vmsacars_rules</code>, la table lue par
        <code>LegacyPirepScoringService</code>. Toute modification s'applique aux prochains calculs
        et dépôts Hermès. Les scores déjà enregistrés restent figés dans leur snapshot historique.
    </p>

    @if(!$scoringRules)
        <div class="notice warning">Le barème vmsACARS est indisponible. Vérifiez les migrations du module VMSAcars.</div>
    @else
    <div class="table-scroll admin-table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Règle</th>
                    <th>Seuil</th>
                    <th>Points retirés</th>
                    <th>Délai (s)</th>
                    <th>Répétable</th>
                    <th>Cooldown (s)</th>
                    <th>Active</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($scoringRules as $rule)
                @php($formId = 'hermes-score-'.$rule['id'])
                <tr>
                    <td>
                        <strong>{{ $rule['name'] }}</strong><br>
                        <code>{{ $rule['id'] }}</code>
                        @if(!empty($rule['description']))
                            <div class="muted">{{ $rule['description'] }}</div>
                        @endif
                        @if($rule['id'] === 'RUNWAY_OVERRUN')
                            <div class="muted"><strong>Protection FDM active :</strong> Prométhée croise la trace Hermès avec la géométrie de la piste d'arrivée. Un dépassement confirmé immobilise automatiquement l'appareil jusqu'à inspection technique.</div>
                        @endif
                    </td>
                    <td>
                        @if($rule['id'] === 'STABILIZED_APPROACH')
                            <strong>1 000 ft AGL → toucher</strong>
                            <div class="muted">VS ≥ −1 000 ft/min · 4 s continus</div>
                        @elseif($rule['id'] === 'EXCESS_GFORCE')
                            <strong>+2,5 g / −1,0 g</strong>
                            <div class="muted">Hors seuil structurel ; une seule pénalité par vol.</div>
                        @elseif($rule['id'] === 'EXCESS_GFORCE_MAINTENANCE')
                            <strong>≥ +2,9 g / ≤ −1,2 g</strong>
                            <div class="muted"><strong>Mise en maintenance automatique.</strong> Ce seuil remplace la pénalité −15 pts.</div>
                        @elseif($rule['has_parameter'])
                            <input form="{{ $formId }}" name="parameter" type="number" step="1" value="{{ $rule['parameter'] }}" required class="admin-field-lg">
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td><input form="{{ $formId }}" name="points" type="number" min="0" max="100" step="1" value="{{ $rule['points'] }}" required class="admin-field-md"></td>
                    @if($rule['id'] === 'STABILIZED_APPROACH')
                        <td>
                            <input form="{{ $formId }}" name="delay" type="hidden" value="4">
                            <strong>4 s continus</strong>
                        </td>
                        <td><span class="muted">Non</span></td>
                        <td>
                            <input form="{{ $formId }}" name="cooldown" type="hidden" value="0">
                            <span class="muted">—</span>
                        </td>
                    @else
                        <td><input form="{{ $formId }}" name="delay" type="number" min="0" max="3600" step="1" value="{{ $rule['delay'] }}" required class="admin-field-md"></td>
                        <td><input form="{{ $formId }}" name="repeatable" type="checkbox" value="1" @checked($rule['repeatable'])></td>
                        <td><input form="{{ $formId }}" name="cooldown" type="number" min="0" max="86400" step="1" value="{{ $rule['cooldown'] }}" required class="admin-field-md"></td>
                    @endif
                    <td><input form="{{ $formId }}" name="enabled" type="checkbox" value="1" @checked($rule['enabled'])></td>
                    <td>
                        <form id="{{ $formId }}" method="post" action="{{ route('admin.promethee.sop.scoring.update', $rule['id']) }}">
                            @csrf
                            @method('PUT')
                            <button class="button small" type="submit">Enregistrer</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</section></div>
    <div class="admin-detail-panel" data-detail-panel="sop-create-panel" hidden><section class="panel admin-workspace-section" id="sop-create">
    <div class="panel-heading"><div><span class="eyebrow">SUPERVISION SOP · SANS IMPACT SCORE</span><h2>Créer une règle SOP</h2></div></div>
    <form method="post" action="{{ route('admin.promethee.sop.rules.save') }}" class="form-grid">
        @csrf
        <label>Nom<input name="name" required maxlength="120" placeholder="Vitesse de roulage"></label>
        <label>Fact code<input name="fact_code" required maxlength="80" placeholder="TAXI_SPEED_MAX"></label>
        <label>Opérateur
            <select name="operator">@foreach($operators as $operator)<option value="{{ $operator }}">{{ strtoupper($operator) }}</option>@endforeach</select>
        </label>
        <label>Seuil<input name="threshold" type="number" step="any" placeholder="30"></label>
        <label>Sévérité
            <select name="severity">@foreach($severities as $severity)<option value="{{ $severity }}">{{ $severity }}</option>@endforeach</select>
        </label>
        <label>Phases (séparées par des virgules)<input name="phases" placeholder="TAXI_OUT,TAXI_IN"></label>
        <label class="wide">Message<input name="message" maxlength="500" placeholder="Valeur observée : {value} {unit}."></label>
        <label><input type="checkbox" name="enabled" value="1" checked> Active</label>
        <label><input type="checkbox" name="pilot_review" value="1"> Revue pilote</label>
        <label><input type="checkbox" name="dispatch_alert" value="1"> Alerte Dispatch</label>
        <div class="wide"><button class="button" type="submit">Créer la règle</button></div>
    </form>
    <p class="muted">Variables disponibles : <code>{value}</code>, <code>{unit}</code>, <code>{code}</code>, <code>{phase}</code>, <code>{message}</code>.</p>
</section></div>
    <div class="admin-detail-panel" data-detail-panel="sop-rules-panel" hidden><section class="panel admin-workspace-section" id="sop-rules">
    <div class="panel-heading"><div><span class="eyebrow">POLITIQUE COMPAGNIE · SUPERVISION</span><h2>Règles SOP / alertes</h2></div><span>{{ count($rules) }} règle(s)</span></div>
    <p class="muted">Ces règles évaluent les faits remontés par Hermès pour la revue pilote et le Dispatch. Elles ne modifient pas le score PIREP.</p>
    <div class="route-list">
        @foreach($rules as $rule)
        <details class="sop-rule" @if(!$loop->first) @else open @endif>
            <summary>
                <strong>{{ $rule['name'] }}</strong>
                <span>{{ $rule['fact_code'] }} · {{ strtoupper($rule['operator']) }} @if($rule['threshold'] !== null) {{ $rule['threshold'] }} @endif · {{ $rule['severity'] }}</span>
            </summary>
            <form method="post" action="{{ route('admin.promethee.sop.rules.update', $rule['id']) }}" class="form-grid">
                @csrf @method('PUT')
                <label>Nom<input name="name" value="{{ $rule['name'] }}" required></label>
                <label>Fact code<input name="fact_code" value="{{ $rule['fact_code'] }}" required></label>
                <label>Opérateur<select name="operator">@foreach($operators as $operator)<option value="{{ $operator }}" @selected($rule['operator']===$operator)>{{ strtoupper($operator) }}</option>@endforeach</select></label>
                <label>Seuil<input name="threshold" type="number" step="any" value="{{ $rule['threshold'] }}"></label>
                <label>Sévérité<select name="severity">@foreach($severities as $severity)<option value="{{ $severity }}" @selected($rule['severity']===$severity)>{{ $severity }}</option>@endforeach</select></label>
                <label>Phases<input name="phases" value="{{ implode(',', $rule['phases'] ?? []) }}"></label>
                <label class="wide">Message<input name="message" value="{{ $rule['message'] }}"></label>
                <label><input type="checkbox" name="enabled" value="1" @checked($rule['enabled'])> Active</label>
                <label><input type="checkbox" name="pilot_review" value="1" @checked($rule['pilot_review'])> Revue pilote</label>
                <label><input type="checkbox" name="dispatch_alert" value="1" @checked($rule['dispatch_alert'])> Alerte Dispatch</label>
                <div class="wide"><button class="button" type="submit">Enregistrer</button></div>
            </form>
            <form method="post" action="{{ route('admin.promethee.sop.rules.delete', $rule['id']) }}" onsubmit="return confirm('Supprimer cette règle SOP ?')">
                @csrf @method('DELETE')
                <button class="button ghost" type="submit">Supprimer</button>
            </form>
        </details>
        @endforeach
    </div>
</section></div>
    <div class="admin-detail-panel" data-detail-panel="sop-alerts-panel" hidden><section class="panel admin-workspace-section" id="sop-alerts">
    <div class="panel-heading"><div><span class="eyebrow">DISPATCH ALERTS</span><h2>Évaluations récentes</h2></div><span>{{ count($alerts) }}</span></div>
    @if(!$alerts)
        <p class="muted">Aucune alerte SOP reçue.</p>
    @else
    <div class="table-scroll admin-table-scroll"><table>
        <thead><tr><th>Heure</th><th>Opération</th><th>Règle</th><th>Fait</th><th>Sévérité</th><th>Observation</th><th>État</th></tr></thead>
        <tbody>
        @foreach($alerts as $alert)
            <tr>
                <td>{{ $alert['created_at'] ?? '—' }}</td>
                <td><code>{{ $alert['operation_id'] ?? '—' }}</code></td>
                <td>{{ $alert['rule_name'] ?? '—' }}</td>
                <td><code>{{ $alert['fact_code'] ?? '—' }}</code></td>
                <td><strong>{{ $alert['severity'] ?? '—' }}</strong></td>
                <td>{{ $alert['message'] ?? '—' }}</td>
                <td>
                    @if(!empty($alert['dispatch_acknowledged_at']))
                        ACK
                    @else
                        <form method="post" action="{{ route('admin.promethee.sop.alerts.ack', $alert['id']) }}">
                            @csrf
                            <input type="hidden" name="operation" value="{{ $alert['operation_id'] }}">
                            <button class="button small" type="submit">ACK</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table></div>
    @endif
</section></div>
  </div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
@endpush
