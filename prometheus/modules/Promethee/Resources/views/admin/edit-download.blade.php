@extends('promethee::layout')

@section('title', 'Modifier un téléchargement')

@section('content')
@php($sections = ['acars' => 'ACARS', 'fleet' => 'Avions et flotte', 'airports' => 'Aéroports et HUBs', 'documents' => 'Documents'])
@php
    $reference = (string) $asset->ref_model;
    if (str_starts_with($reference, 'Modules\\Promethee\\Download\\')) {
        $category = strtolower(str_replace('Modules\\Promethee\\Download\\', '', $reference));
    } elseif (str_contains(strtolower($reference), 'aircraft') || str_contains(strtolower($reference), 'subfleet')) {
        $category = 'fleet';
    } elseif (str_contains(strtolower($reference), 'airport')) {
        $category = 'airports';
    } elseif (str_contains(strtolower($asset->name.' '.$asset->path), 'acars')) {
        $category = 'acars';
    } else {
        $category = 'documents';
    }
    $subcategory = trim((string) $asset->ref_model_id);
    $isExternal = $asset->isExternalFile;
@endphp

<div class="ops-header compact">
    <div>
        <span class="eyebrow">ADMINISTRATION</span>
        <h1>Modifier un téléchargement.</h1>
        <p>Modifiez les informations de la ressource ou remplacez sa source.</p>
    </div>
    <a class="button outline" href="{{ route('admin.promethee.downloads') }}">Retour à la liste</a>
</div>

<section class="panel">
    <div class="panel-heading">
        <div><span class="eyebrow">{{ $asset->name }}</span><h2>Détails de la ressource</h2></div>
    </div>
    <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.downloads.update', $asset->id) }}" class="flight-filter">
        @csrf
        @method('put')
        <label class="filter-wide">Nom<input name="name" value="{{ old('name', $asset->name) }}" required></label>
        <label>Catégorie
            <select name="category">
                @foreach ($sections as $key => $title)
                    <option value="{{ $key }}" @selected(old('category', $category) === $key)>{{ $title }}</option>
                @endforeach
            </select>
        </label>
        <label>Sous-catégorie<input name="subcategory" maxlength="80" value="{{ old('subcategory', $subcategory === $category ? '' : $subcategory) }}" placeholder="Ex. MSFS, Airbus, Manuels"></label>
        <label>Remplacer le fichier<input name="file" type="file"></label>
        <label>ou remplacer par une URL<input name="url" type="url" value="{{ old('url', $isExternal ? $asset->path : '') }}" placeholder="https://…"></label>
        <label>Description<textarea name="description">{{ old('description', $asset->description) }}</textarea></label>
        <label><input name="public" value="1" type="checkbox" @checked(old('public', $asset->public))> accessible sans connexion</label>
        <button>Enregistrer les modifications</button>
    </form>
    <p class="muted">Laissez le fichier et l’URL vides pour conserver la source actuelle. L’envoi d’un fichier remplace définitivement la source précédente.</p>
</section>
@endsection
