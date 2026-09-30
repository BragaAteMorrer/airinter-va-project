@extends('promethee::layout')

@section('title', 'Saisons & périodes')

@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">ADMINISTRATION · ÉCONOMIE</span>
        <h1>Saisons & périodes.</h1>
        <p>Définissez les périodes commerciales utilisées par Prométhée pour organiser les règles tarifaires et les opérations saisonnières.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.economy') }}">Retour à l’économie</a>
</div>

@if($errors->any())
<div class="notice danger" role="alert">
    <strong>Impossible d’enregistrer la saison.</strong>
    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">NOUVELLE PÉRIODE</span>
            <h2>Créer une saison</h2>
            <p>Une saison active peut servir de période de référence pour les fonctions tarifaires de Prométhée.</p>
        </div>
    </div>

    <form method="post" action="{{ route('admin.promethee.seasons.save') }}" class="form-grid">
        @csrf
        <label class="full">Nom
            <input name="name" maxlength="80" value="{{ old('name') }}" required placeholder="Ex. Hiver 2026-2027">
        </label>
        <label>Début
            <input type="date" name="starts_on" value="{{ old('starts_on') }}" required>
        </label>
        <label>Fin
            <input type="date" name="ends_on" value="{{ old('ends_on') }}" required>
        </label>
        <label class="full">Notes
            <textarea name="notes" maxlength="2000" placeholder="Contexte, lignes concernées, règles prévues…">{{ old('notes') }}</textarea>
        </label>
        <label>
            <input type="checkbox" name="active" value="1" @checked(old('active'))>
            Définir comme saison active
        </label>
        <div style="align-self:end">
            <button type="submit">Enregistrer la saison</button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">CALENDRIER COMMERCIAL</span>
            <h2>Périodes enregistrées</h2>
        </div>
        <span class="tag">{{ $seasons->count() }} saison(s)</span>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Statut</th>
                    <th>Nom</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Durée</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
            @forelse($seasons as $season)
                @php
                    $start = IlluminateSupportCarbon::parse($season->starts_on);
                    $end = IlluminateSupportCarbon::parse($season->ends_on);
                @endphp
                <tr>
                    <td>
                        @if($season->active)
                            <span class="tag">ACTIVE</span>
                        @else
                            <span class="muted">Inactive</span>
                        @endif
                    </td>
                    <td><strong>{{ $season->name }}</strong></td>
                    <td>{{ $start->format('d/m/Y') }}</td>
                    <td>{{ $end->format('d/m/Y') }}</td>
                    <td>{{ $start->diffInDays($end) + 1 }} j</td>
                    <td>{{ $season->notes ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Aucune saison configurée.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">IMPORT PROGRAMME</span>
            <h2>Importer un programme de vols CSV</h2>
            <p>Importe ou met à jour des lignes depuis un fichier CSV séparé par des points-virgules.</p>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.seasons.import') }}" class="form-grid">
        @csrf
        <label class="full">Fichier CSV
            <input type="file" name="schedule" accept=".csv,.txt,text/csv,text/plain" required>
        </label>
        <div class="notice" role="note">
            Colonnes obligatoires : <code>airline_id;flight_number;departure;arrival</code>. Colonnes facultatives : <code>dpt_time;arr_time;route;flight_type</code>.
        </div>
        <div><button type="submit">Importer le programme</button></div>
    </form>
</section>
@endsection
