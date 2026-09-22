@extends('promethee::layout')
@section('title', __('promethee.navigation_menu.bookings'))
@section('content')
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
        <small style="display:block;opacity:.65">{{ $booking->operation_id }}</small>
    </td>
    <td>{{ $booking->flight?->dpt_airport_id ?? '—' }} → {{ $booking->flight?->arr_airport_id ?? '—' }}</td>
    <td>{{ $booking->aircraft?->registration ?? 'À sélectionner' }}</td>
    <td style="min-width:150px">
        <div style="height:6px;background:rgba(127,127,127,.2);border-radius:99px;overflow:hidden">
            <div style="height:100%;width:{{ $booking->operation_progress }}%;background:currentColor"></div>
        </div>
        <small>{{ $booking->operation_progress }}%</small>
    </td>
    <td><strong>{{ str_replace('_', ' ', $booking->operation_status) }}</strong></td>
    <td>{{ $booking->operation_next_action ?? '—' }}</td>
    <td>
        <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
            @if($booking->flight)
                <a class="button" href="{{ route('promethee.flights.show', $booking->flight->id) }}">
                    {{ in_array($booking->operation_status, ['COMPLETED','CANCELLED'], true) ? 'Voir le vol' : 'Ouvrir l’opération' }}
                </a>
            @endif
            @if($booking->operation_can_delete)
                <form method="POST" action="{{ route('promethee.bookings.cancel', $booking->id) }}" onsubmit="return confirm('Supprimer cette réservation ?');">
                    @csrf @method('DELETE')
                    <button class="button button-secondary" type="submit">Supprimer</button>
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

<p style="opacity:.7;margin-top:1rem"><small>Une réservation peut être supprimée tant qu’aucun PIREP Hermès n’a été créé. Dès la préparation du PIREP, elle devient un enregistrement opérationnel conservé.</small></p>
@endsection
