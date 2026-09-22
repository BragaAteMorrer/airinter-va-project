@extends('promethee::layout')
@section('title', __('promethee.navigation_menu.bookings'))
@section('content')
<div class="ops-header compact">
    <div><span class="eyebrow">ESPACE PILOTE · OPÉRATIONS</span><h1>{{ __('promethee.navigation_menu.bookings') }}</h1><p>Vos réservations deviennent des opérations suivies de la préparation au PIREP.</p></div>
    <a class="button" href="{{ route('promethee.flights') }}">{{ __('promethee.flight_schedule') }}</a>
</div>

@if($errors->has('booking'))
    <div class="alert alert-danger">{{ $errors->first('booking') }}</div>
@endif

<section class="panel table-wrap">
<table>
<thead><tr><th>Vol</th><th>Itinéraire</th><th>Appareil</th><th>Préparation</th><th>État</th><th></th></tr></thead>
<tbody>
@forelse($bookings as $booking)
<tr>
    <td>
        <strong>{{ $booking->flight?->ident ?? '—' }}</strong>
        <small style="display:block;opacity:.65">{{ $booking->operation_id }}</small>
    </td>
    <td>{{ $booking->flight?->dpt_airport_id ?? '—' }} → {{ $booking->flight?->arr_airport_id ?? '—' }}</td>
    <td>{{ $booking->aircraft?->registration ?? 'À sélectionner' }}</td>
    <td>
        <span>{{ $booking->aircraft_id ? '✓' : '—' }} Appareil</span><br>
        <span>{{ $booking->operation_ofp ? '✓' : '—' }} OFP</span><br>
        <span>{{ $booking->operation_pirep ? '✓' : '—' }} PIREP</span>
    </td>
    <td><strong>{{ $booking->operation_status }}</strong></td>
    <td>
        <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
            @if($booking->flight)
                <a class="button" href="{{ route('promethee.flights.show', $booking->flight->id) }}">Préparer le vol</a>
            @endif
            @if(!$booking->operation_pirep)
                <form method="POST" action="{{ route('promethee.bookings.cancel', $booking->id) }}" onsubmit="return confirm('Supprimer cette réservation ?');">
                    @csrf @method('DELETE')
                    <button class="button button-secondary" type="submit">Supprimer</button>
                </form>
            @endif
        </div>
    </td>
</tr>
@empty
<tr><td colspan="6">Aucune réservation active. <a href="{{ route('promethee.flights') }}">Consulter le programme des vols</a>.</td></tr>
@endforelse
</tbody>
</table>
</section>
@endsection
