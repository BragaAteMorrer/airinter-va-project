@extends('promethee::layout')
@section('title', __('promethee.navigation_menu.bookings'))
@section('content')
@php
    $operationStatusLabels = [
        'AIRCRAFT_REQUIRED' => 'Appareil à sélectionner',
        'OFP_REQUIRED' => 'OFP à préparer',
        'PIREP_REQUIRED' => 'Préparation à finaliser',
        'READY' => 'Prêt au départ',
        'IN_PROGRESS' => 'Vol en cours',
        'AWAITING_FILING' => 'Flight Review à finaliser',
        'COMPLETED' => 'Terminé',
        'CANCELLED' => 'Annulé',
    ];
@endphp
<div class="ops-header compact">
    <div><span class="eyebrow">ESPACE PILOTE · OPÉRATIONS</span><h1>{{ __('promethee.navigation_menu.bookings') }}</h1><p>Suivez chaque vol depuis la réservation jusqu’au PIREP terminé.</p></div>
    <a class="button" href="{{ route('promethee.flights') }}">{{ __('promethee.flight_schedule') }}</a>
</div>

@if($errors->has('booking'))
    <div class="alert alert-danger">{{ $errors->first('booking') }}</div>
@endif

<section class="panel table-wrap">
<table>
<thead><tr><th>Opération</th><th>Itinéraire</th><th>Appareil</th><th>Progression</th><th>État</th><th>Prochaine action</th><th></th></tr></thead>
<tbody>
@forelse($bookings as $booking)
<tr>
    <td>
        <strong>{{ $booking->flight?->ident ?? '—' }}</strong>
        <small class="operation-id">{{ $booking->operation_id }}</small>
    </td>
    <td>{{ $booking->flight?->dpt_airport_id ?? '—' }} → {{ $booking->flight?->arr_airport_id ?? '—' }}</td>
    <td>{{ $booking->aircraft?->registration ?? 'À sélectionner' }}</td>
    <td class="operation-progress-cell">
        <progress class="operation-progress" value="{{ $booking->operation_progress }}" max="100" aria-label="Progression de l’opération : {{ $booking->operation_progress }} %">{{ $booking->operation_progress }}%</progress>
        <small>{{ $booking->operation_progress }}%</small>
    </td>
    <td>
        <strong class="operation-status">{{ $operationStatusLabels[$booking->operation_status] ?? str_replace('_', ' ', $booking->operation_status) }}</strong>
        @if($booking->operation_legacy_ghost ?? false)
            <small class="operation-warning">Ancien PIREP fantôme détecté</small>
        @endif
    </td>
    <td>{{ $booking->operation_next_action ?? '—' }}</td>
    <td>
        <div class="operation-actions">
            @if($booking->flight)
                <a class="button" href="{{ route('promethee.flights.show', $booking->flight->id) }}">
                    {{ in_array($booking->operation_status, ['COMPLETED','CANCELLED'], true) ? 'Voir le vol' : 'Ouvrir l’opération' }}
                </a>
            @endif
            @if($booking->operation_can_delete)
                <form method="POST" action="{{ route('promethee.bookings.cancel', $booking->id) }}" onsubmit="return confirm('Annuler cette réservation ? Cette action est possible uniquement tant qu’aucun PIREP Hermès n’a été créé.');">
                    @csrf @method('DELETE')
                    <button class="button button-secondary" type="submit">Annuler la réservation</button>
                </form>
            @endif
            @if($booking->operation_can_delete_pirep)
                <form method="POST" action="{{ route('promethee.bookings.pirep.delete', $booking->id) }}" onsubmit="return confirm('Abandonner et supprimer votre PIREP {{ $booking->operation_pirep?->id }} ?\n\nLa télémétrie et les données liées à cette tentative seront supprimées. La réservation restera disponible pour recommencer. Cette action est irréversible.');">
                    @csrf @method('DELETE')
                    <button class="button danger" type="submit">Abandonner le PIREP</button>
                </form>
            @endif
        </div>
    </td>
</tr>
@empty
<tr><td colspan="7">Aucune opération active. <a href="{{ route('promethee.flights') }}">Consulter le programme des vols</a>.</td></tr>
@endforelse
</tbody>
</table>
</section>

<p class="page-footnote"><small>Une réservation sans PIREP peut être annulée. En cas de crash ou d’abandon, le pilote peut aussi supprimer son propre PIREP tant qu’il est en préparation, en cours, en pause ou en attente de validation. Un PIREP accepté, rejeté ou annulé reste protégé et nécessite l’intervention d’un administrateur.</small></p>
@endsection
