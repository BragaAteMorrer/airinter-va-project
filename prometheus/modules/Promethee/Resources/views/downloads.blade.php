@extends('promethee::layout')
@section('title', __('promethee.navigation_menu.downloads'))
@section('content')
@php($sections = ['acars' => ['ACARS', 'Clients et documentation de connexion'], 'fleet' => ['Avions et flotte', 'Livrées, appareils et documents associés'], 'airports' => ['Aéroports et HUBs', 'Scènes, cartes et ressources réseau'], 'documents' => ['Documents', 'Manuels et documents opérationnels']])
<div class="ops-header compact"><div><span class="eyebrow">CENTRE PILOTE</span><h1>{{ __('promethee.navigation_menu.downloads') }}</h1><p>Ressources de vol classées par usage, gérées depuis le panneau d’administration.</p></div></div>
@foreach($sections as $key => [$title, $description])
<section class="panel"><div class="panel-heading"><div><span class="eyebrow">{{ strtoupper($key) }}</span><h2>{{ $title }}</h2></div><p>{{ $description }}</p></div><div class="route-list">@forelse($groups->get($key, collect()) as $file)<article><strong>{{ $file->name }}</strong>@if($file->description)<span>{{ $file->description }}</span>@endif<a class="button outline" href="{{ route('promethee.downloads.download', $file->id) }}">Télécharger</a></article>@empty<p class="empty">Aucune ressource disponible dans cette catégorie.</p>@endforelse</div></section>
@endforeach
@endsection
