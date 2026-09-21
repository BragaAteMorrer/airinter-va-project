@extends('promethee::layout')
@section('title','Hermès ACARS')
@section('content')
<div class="page-heading">
    <div>
        <span class="eyebrow">LIAISON SOL / BORD</span>
        <h1>Hermès vous accompagne.</h1>
        <p>Le client ACARS officiel de Prométhée, de la préparation du vol au dépôt du PIREP.</p>
    </div>
    <span class="tag">{{ $bids }} réservation(s)</span>
</div>

<section class="hero acars-hero">
    <div>
        <span class="tag light">HERMÈS · AIR INTER</span>
        <h2>Un seul parcours,<br>du briefing au parking.</h2>
        <p>Hermès récupère vos réservations, prépare SimBrief, suit le simulateur et conserve les données à transmettre en cas de coupure.</p>
        <div class="toolbar">
            <a class="button" href="{{ route('promethee.downloads.category', 'acars') }}">Télécharger Hermès ↗</a>
            <a class="button outline" href="{{ route('promethee.bookings') }}">Mes réservations</a>
        </div>
    </div>
    <div class="terminal-preview" aria-label="État du parcours Hermès">
        <p>──── HERMÈS / AIR INTER ────</p>
        <p>COMPTE PROMÉTHÉE <span>PRÊT</span></p>
        <p>RÉSERVATIONS <span>{{ $bids }}</span></p>
        <p>SIMBRIEF <span>INTÉGRÉ</span></p>
        <p>REPRISE HORS LIGNE <span>ACTIVE</span></p>
        <p>SIMULATEUR <span class="cursor">_</span></p>
    </div>
</section>

<div class="two-columns">
    <section class="panel">
        <span class="eyebrow">AVANT LE DÉPART</span>
        <h2>Préparer votre premier vol</h2>
        <ol class="steps">
            <li>Réservez une ligne dans le programme Prométhée.</li>
            <li>Téléchargez puis lancez Hermès sur le PC du simulateur.</li>
            <li>Connectez-vous avec votre identifiant pilote ou votre e-mail et votre mot de passe Prométhée.</li>
            <li>Sélectionnez la réservation et l’appareil autorisé.</li>
            <li>Créez ou importez l’OFP SimBrief, puis pré-déposez le PIREP.</li>
            <li>Démarrez l’enregistrement une fois le simulateur connecté.</li>
        </ol>
        <p class="hint">Aucune clé API n’est nécessaire pour la connexion normale. La connexion avancée est réservée aux installations administrées.</p>
        <a class="button" href="{{ route('promethee.flights') }}">Préparer un vol ↗</a>
    </section>

    <section class="panel">
        <span class="eyebrow">PARCOURS OPÉRATIONNEL</span>
        <h2>Ce qu’Hermès enregistre</h2>
        <div class="route-list">
            <article><strong>OUT</strong><span>Départ du parking et début du temps bloc.</span></article>
            <article><strong>OFF</strong><span>Décollage, suivi de la route et de la télémétrie.</span></article>
            <article><strong>ON</strong><span>Atterrissage et mesure du taux de toucher.</span></article>
            <article><strong>IN</strong><span>Arrivée au parking et autorisation de déposer le PIREP.</span></article>
        </div>
    </section>
</div>

<section class="panel table-wrap">
    <div class="panel-heading">
        <div><span class="eyebrow">CARNET RÉCENT</span><h2>Vos derniers rapports</h2></div>
        <a href="{{ route('promethee.public.pireps') }}">Tous les rapports ↗</a>
    </div>
    <table>
        <thead><tr><th>Vol</th><th>Départ</th><th>Arrivée</th><th>État</th><th></th></tr></thead>
        <tbody>
        @forelse($recent as $p)
            <tr>
                <td>{{ $p->ident }}</td><td>{{ $p->dpt_airport_id }}</td><td>{{ $p->arr_airport_id }}</td>
                <td>{{ \App\Models\Enums\PirepState::label($p->state) }}</td>
                <td><a href="{{ route('promethee.pireps.show',$p->id) }}">Rapport ↗</a></td>
            </tr>
        @empty
            <tr><td colspan="5">Vos rapports apparaîtront après votre premier vol réalisé avec Hermès.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
@endsection
