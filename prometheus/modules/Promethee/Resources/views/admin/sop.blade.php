@extends('promethee::layout')
@section('title','SOP Engine')
@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">AIR INTER · FLIGHT STANDARDS</span>
        <h1>SOP Engine.</h1>
        <p>Hermès remonte des faits. Prométhée applique ici les règles compagnie, sans score ni pénalité automatique.</p>
    </div>
</div>

@if(session('success')) <div class="notice success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="notice warning">{{ $errors->first() }}</div> @endif

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">NOUVELLE RÈGLE</span><h2>Créer une règle SOP</h2></div></div>
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
</section>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">POLITIQUE COMPAGNIE</span><h2>Règles actives</h2></div><span>{{ count($rules) }} règle(s)</span></div>
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
</section>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">DISPATCH ALERTS</span><h2>Évaluations récentes</h2></div><span>{{ count($alerts) }}</span></div>
    @if(!$alerts)
        <p class="muted">Aucune alerte SOP reçue.</p>
    @else
    <div class="table-scroll"><table>
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
</section>
@endsection
