@extends('promethee::layout')
@section('title', __('promethee.navigation_menu.downloads'))
@section('content')
@php
    $sections = [
        'acars' => ['ACARS & HERMÈS', 'Client de vol, installateur et documentation de connexion'],
        'fleet' => ['AVIONS ET FLOTTE', 'Livrées, appareils et documents associés'],
        'airports' => ['AÉROPORTS ET HUBS', 'Scènes, cartes et ressources réseau'],
        'documents' => ['DOCUMENTS', 'Manuels et documents opérationnels'],
    ];
    $resourceCount = $groups->sum(fn ($entries) => $entries->count());
@endphp
<div class="ops-header compact">
    <div>
        <span class="eyebrow">CENTRE PILOTE</span>
        <h1>{{ __('promethee.navigation_menu.downloads') }}</h1>
        <p>Les ressources approuvées pour préparer et réaliser vos vols Air Inter.</p>
    </div>
    <span class="tag">{{ $resourceCount }} ressource(s)</span>
</div>

<div class="notice" role="note">
    Installez uniquement les versions publiées ici. Pour Hermès, fermez l’application avant de remplacer une version existante.
</div>

@foreach($sections as $key => $section)
@php
    $title = $section[0];
    $description = $section[1];
    $entries = $groups->get($key, collect());
@endphp
<section class="panel">
    <div class="panel-heading">
        <div><span class="eyebrow">{{ $title }}</span><h2>{{ $entries->count() }} ressource(s)</h2></div>
        <p>{{ $description }}</p>
        <a class="button outline" href="{{ route('promethee.downloads.category',$key) }}">Voir la catégorie ↗</a>
    </div>
    <div class="route-list">
        @forelse($entries->take(3) as $file)
        @php
            $extension = strtoupper(pathinfo(parse_url((string) $file->path, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION));
            $updated = $file->updated_at ?: $file->created_at;
        @endphp
        <article>
            <strong>{{ $file->name }}</strong>
            @if($file->description)<span>{{ $file->description }}</span>@endif
            <small>{{ $extension ?: 'RESSOURCE' }}@if($updated) · Mis à jour le {{ $updated->setTimezone('Europe/Paris')->format('d/m/Y') }}@endif</small>
            <a class="button outline" href="{{ route('promethee.downloads.download', $file->id) }}">Télécharger</a>
        </article>
        @empty
        <p class="empty">Aucune ressource publiée dans cette catégorie.</p>
        @endforelse
    </div>
</section>
@endforeach
@endsection
