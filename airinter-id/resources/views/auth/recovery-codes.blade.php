@extends('layout')
@section('title', 'Codes de récupération · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">MFA</span>
    <h1>Codes de récupération</h1>
    <p>Conservez-les hors ligne. Chaque code ne peut être utilisé qu’une seule fois.</p>
    <div class="recovery-codes">
        @foreach($codes as $code)<code>{{ $code }}</code>@endforeach
    </div>
    <a class="button primary" href="{{ route('account') }}">J’ai sauvegardé mes codes</a>
</section>
@endsection
