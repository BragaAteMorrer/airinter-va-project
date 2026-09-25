@extends('layout')
@section('title', 'Mon compte · Air Inter ID')
@section('content')
<section class="account-grid">
    <article class="card">
        <span class="kicker">AIR INTER ID</span>
        <h1>{{ $user->display_name }}</h1>
        <p class="subject">SUB · {{ $user->subject }}</p>
        <dl>
            <div><dt>E-mail</dt><dd>{{ $user->email }}</dd></div>
            <div><dt>État</dt><dd>{{ strtoupper($user->state) }}</dd></div>
            <div><dt>Langue</dt><dd>{{ $user->preferred_locale }}</dd></div>
            <div><dt>Fuseau</dt><dd>{{ $user->timezone }}</dd></div>
        </dl>
        <form method="post" action="{{ route('logout') }}">@csrf<button class="button">Déconnexion</button></form>
    </article>
    <article class="card">
        <span class="kicker">COMPTES LIÉS</span>
        <h2>Votre galaxie Air Inter</h2>
        @forelse($user->identities as $identity)
            <div class="identity">
                <strong>{{ ucfirst($identity->provider) }}</strong>
                <span>{{ $identity->external_ident ?: '#'.$identity->external_user_id }}</span>
                <small>lié {{ optional($identity->linked_at)->format('d/m/Y') }}</small>
            </div>
        @empty
            <p>Aucun système historique n'est encore lié.</p>
        @endforelse
    </article>
</section>
@endsection
