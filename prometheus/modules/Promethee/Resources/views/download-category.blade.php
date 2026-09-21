@extends('promethee::layout')
@section('title', $sections[$category][0])
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">CENTRE PILOTE</span><h1>{{ $sections[$category][0] }}.</h1><p>{{ $sections[$category][1] }}</p></div><a class="button outline" href="{{ route('promethee.downloads') }}">Toutes les ressources</a></div>
@forelse($files as $subcategory => $entries)
<section class="panel"><div class="panel-heading"><div><span class="eyebrow">{{ strtoupper($category) }}</span><h2>{{ $subcategory }}</h2></div><span class="tag">{{ $entries->count() }} ressource(s)</span></div>
<div @class(['document-library' => $category === 'documents', 'route-list' => $category !== 'documents'])>@foreach($entries as $file)
@if($category === 'documents')<article class="document-card"><span class="file-mark">DOC</span><div><strong>{{ $file->name }}</strong><p>{{ $file->description ?: 'Document de référence Air Inter.' }}</p></div><a href="{{ route('promethee.downloads.download', $file->id) }}">Consulter →</a></article>
@else<article><strong>{{ $file->name }}</strong>@if($file->description)<span>{{ $file->description }}</span>@endif<a class="button outline" href="{{ route('promethee.downloads.download', $file->id) }}">Télécharger</a></article>@endif
@endforeach</div></section>
@empty
<section class="panel"><p class="empty">Aucune ressource disponible dans cette catégorie.</p></section>
@endforelse
@endsection
