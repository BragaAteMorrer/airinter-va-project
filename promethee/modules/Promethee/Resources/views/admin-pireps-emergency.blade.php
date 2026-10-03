@extends('promethee::layout')
@section('title','PIREP · remise à zéro urgence')
@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">OUTIL ADMINISTRATEUR · URGENCE</span>
        <h1>Remise à zéro d’un PIREP.</h1>
        <p>Supprime définitivement un PIREP bloqué afin que le pilote puisse recréer son opération. Utilisez cet outil uniquement pour un incident avéré.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.dashboard') }}">← Administration</a>
</div>

<section class="panel">
    <div class="notice error" style="margin-bottom:1rem">
        <strong>Action destructive.</strong>
        La suppression efface le PIREP et ses données associées. Un PIREP accepté est d’abord rejeté pour retirer ses heures/compteurs avant suppression. L’action est journalisée.
    </div>

    <form class="filters" method="get">
        <label style="min-width:280px">Pilote, ID PIREP, vol ou aéroport
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="IT199, IT749, LFPO, ID du PIREP…">
        </label>
        <label>État
            <select name="state">
                @foreach(['all'=>'Tous','pending'=>'En attente','accepted'=>'Accepté','rejected'=>'Rejeté','in_progress'=>'En cours','cancelled'=>'Annulé'] as $value=>$label)
                    <option value="{{ $value }}" @selected(($filters['state'] ?? 'all') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button class="button" type="submit">Rechercher</button>
        <a class="button outline" href="{{ route('admin.promethee.pireps-emergency') }}">Réinitialiser</a>
    </form>
</section>

<section class="panel table-wrap">
<table>
    <thead>
        <tr><th>PIREP</th><th>Pilote</th><th>Vol</th><th>Route</th><th>Appareil</th><th>État</th><th>Déposé</th><th>Urgence</th></tr>
    </thead>
    <tbody>
    @forelse($pireps as $pirep)
        <tr>
            <td><strong>{{ $pirep->id }}</strong><small style="display:block;opacity:.65">{{ $pirep->source_name ?: 'Source inconnue' }}</small></td>
            <td>{{ $pirep->user?->pilot_id ?? '—' }} · {{ $pirep->user?->name ?? 'Pilote inconnu' }}</td>
            <td>{{ $pirep->ident ?: ($pirep->flight?->ident ?? '—') }}</td>
            <td>{{ $pirep->dpt_airport_id ?: '—' }} → {{ $pirep->arr_airport_id ?: '—' }}</td>
            <td>{{ $pirep->aircraft?->registration ?? '—' }}</td>
            <td><strong>{{ $pirep->state }}</strong> · {{ $pirep->status }}</td>
            <td>{{ optional($pirep->submitted_at ?? $pirep->created_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</td>
            <td>
                <form method="POST" action="{{ route('admin.promethee.pireps-emergency.delete', $pirep->id) }}" onsubmit="return confirm('SUPPRESSION DÉFINITIVE du PIREP {{ $pirep->id }} ? Vérifiez bien le pilote et le vol avant de continuer.');">
                    @csrf
                    @method('DELETE')
                    <input name="confirmation" required autocomplete="off" placeholder="Tapez SUPPRIMER" style="min-width:145px;margin-bottom:.4rem">
                    <button class="button danger" type="submit">Supprimer en urgence</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="8">Aucun PIREP ne correspond à la recherche.</td></tr>
    @endforelse
    </tbody>
</table>
</section>

{{ $pireps->links('pagination::bootstrap-4') }}
@endsection
