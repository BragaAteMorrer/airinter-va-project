@extends('promethee::layout')
@section('title','Calendrier')
@section('content')
@php
    $canManage = auth()->user()?->ability('admin','admin-access') ?? false;
    $first = $start->copy()->startOfWeek();
    $days = [];
    for ($i = 0; $i < 42; $i++) {
        $days[] = $first->copy()->addDays($i);
    }
@endphp

<div class="page-heading">
    <div>
        <span class="eyebrow">OPÉRATIONS · HEURE DE PARIS</span>
        <h1>Volons ensemble.</h1>
        <p>Vols en groupe, entraînements et rendez-vous de la compagnie.</p>
    </div>
    <form class="filters">
        <label>Mois<input type="month" name="month" value="{{ $month }}"></label>
        <button>Afficher</button>
    </form>
</div>

<section class="panel">
    <div class="panel-heading">
        <a href="?month={{ $start->copy()->subMonth()->format('Y-m') }}" aria-label="Mois précédent">←</a>
        <h2>{{ $start->copy()->locale('fr')->isoFormat('MMMM YYYY') }}</h2>
        <a href="?month={{ $start->copy()->addMonth()->format('Y-m') }}" aria-label="Mois suivant">→</a>
    </div>
    <div class="calendar">
        @foreach(['LUN','MAR','MER','JEU','VEN','SAM','DIM'] as $day)
            <div class="weekday">{{ $day }}</div>
        @endforeach
        @foreach($days as $date)
            <div class="calendar-day {{ $date->month !== $start->month ? 'outside' : '' }} {{ $date->isToday() ? 'today' : '' }}">
                <span>{{ $date->day }}</span>
                @foreach($events as $event)
                    @if(\Carbon\Carbon::parse($event->starts_at,'UTC')->setTimezone('Europe/Paris')->toDateString() === $date->toDateString())
                        <a class="calendar-event" href="#event-{{ $event->id }}">{{ $event->title }}</a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>
</section>

<div class="two-columns">
    <section class="panel">
        <h2>Les rendez-vous du mois</h2>
        @forelse($events as $event)
            <article class="event" id="event-{{ $event->id }}">
                <time>{{ \Carbon\Carbon::parse($event->starts_at,'UTC')->setTimezone('Europe/Paris')->format('d/m H:i') }} → {{ \Carbon\Carbon::parse($event->ends_at,'UTC')->setTimezone('Europe/Paris')->format('d/m H:i') }}</time>
                <h3>{{ $event->title }}</h3>
                <p class="preserve">{{ $event->description }}</p>
                <span class="tag">{{ $event->departure ?: 'Rendez-vous' }} {{ $event->arrival ? '→ '.$event->arrival : '' }}</span>
                <p class="muted">{{ $event->rsvp_count }} pilote(s) inscrit(s){{ $event->my_rsvp ? ' · votre réponse : '.($event->my_rsvp === 'going' ? 'présent' : 'peut-être') : '' }}</p>
                <form method="post" action="{{ route('promethee.calendar.rsvp',$event->id) }}" class="toolbar">@csrf<button name="status" value="going" class="outline">Je participe</button><button name="status" value="maybe" class="text-button">Peut-être</button></form>
                @if($canManage)
                    <form method="post" action="{{ route('admin.promethee.calendar.delete',$event->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-button">Supprimer cet événement</button>
                    </form>
                @endif
            </article>
        @empty
            <p class="empty">Aucun événement prévu ce mois-ci.</p>
        @endforelse
    </section>

    @if($canManage)
        <form class="panel form-grid" method="post" action="{{ route('admin.promethee.calendar.save') }}">
            @csrf
            <h2 class="full">Ajouter un rendez-vous</h2>
            <label class="full">Titre<input name="title" required maxlength="191" value="{{ old('title') }}"></label>
            <label>Début (Paris)<input type="datetime-local" name="starts_at" required value="{{ old('starts_at') }}"></label>
            <label>Fin (Paris)<input type="datetime-local" name="ends_at" required value="{{ old('ends_at') }}"></label>
            <label>Départ ICAO<input name="departure" maxlength="8" value="{{ old('departure') }}"></label>
            <label>Arrivée ICAO<input name="arrival" maxlength="8" value="{{ old('arrival') }}"></label>
            <label class="full">Description<textarea name="description" rows="4">{{ old('description') }}</textarea></label>
            <button class="full">Ajouter au calendrier</button>
        </form>
    @endif
</div>
@endsection
