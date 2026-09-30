@extends('promethee::layout')
@section('title', $asset->name)

@section('content')
@php
    $updated = $asset->updated_at ?: $asset->created_at;
    $officeViewer = 'https://view.officeapps.live.com/op/embed.aspx?src='.rawurlencode($asset->url);
@endphp

<div class="ops-header compact">
    <div>
        <span class="eyebrow">COMPAGNIE · DOCUMENTATION INTERNE</span>
        <h1>{{ $asset->name }}</h1>
        <p>{{ $asset->description ?: 'Document interne Air Inter VA.' }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="button outline" href="{{ route('promethee.documents') }}">← Bibliothèque</a>
        <a class="button" href="{{ route('promethee.downloads.download', $asset->id) }}">Télécharger l’original</a>
    </div>
</div>

<section class="panel">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">{{ strtoupper($extension ?: 'DOCUMENT') }}</span>
            <h2>Consultation en ligne</h2>
        </div>
        @if($updated)<span class="tag">Mis à jour le {{ $updated->setTimezone('Europe/Paris')->format('d/m/Y') }}</span>@endif
    </div>

    @if($previewType === 'pdf')
        <iframe src="{{ route('promethee.documents.content', $asset->id) }}" title="{{ $asset->name }}" style="width:100%;height:78vh;border:0;border-radius:12px;background:#fff"></iframe>
    @elseif($previewType === 'image')
        <div style="text-align:center"><img src="{{ route('promethee.documents.content', $asset->id) }}" alt="{{ $asset->name }}" style="max-width:100%;max-height:78vh;border-radius:12px"></div>
    @elseif($previewType === 'office')
        <div class="notice" role="note">Ce document Office est affiché directement dans Prométhée via la visionneuse Microsoft Office Online. L’original reste téléchargeable avec le bouton ci-dessus.</div>
        <iframe src="{{ $officeViewer }}" title="{{ $asset->name }}" style="width:100%;height:78vh;border:0;border-radius:12px;background:#fff" allowfullscreen></iframe>
    @elseif($previewType === 'text')
        <iframe src="{{ route('promethee.documents.content', $asset->id) }}" title="{{ $asset->name }}" style="width:100%;height:70vh;border:1px solid rgba(127,127,127,.25);border-radius:12px;background:#fff"></iframe>
    @else
        <div class="notice">La prévisualisation intégrée n’est pas disponible pour ce format. L’original peut toujours être téléchargé.</div>
    @endif
</section>
@endsection
