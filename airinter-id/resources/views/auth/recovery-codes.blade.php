@extends('layout')
@section('title', 'Codes de récupération · Argos')
@section('content')
<section class="card auth-card">
    <span class="kicker">MFA</span>
    <h1>Codes de récupération</h1>
    <p>Conservez-les hors ligne. Chaque code ne peut être utilisé qu’une seule fois.</p>
    <div class="recovery-codes" id="argos-recovery-codes">
        @foreach($codes as $code)<code>{{ $code }}</code>@endforeach
    </div>
    <div class="tools">
        <button type="button" class="button secondary" data-copy-recovery-codes>Copier tous les codes</button>
        <button type="button" class="button secondary" data-export-recovery-codes>Exporter en .txt</button>
    </div>
    <p class="muted" role="status" id="argos-recovery-feedback">Ce fichier contient des secrets d'accès : conservez-le dans un coffre-fort chiffré, jamais dans un espace partagé.</p>
    <a class="button primary" href="{{ route('account') }}">J’ai sauvegardé mes codes</a>
</section>
@endsection
