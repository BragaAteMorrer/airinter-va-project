@extends('promethee::layout')

@section('title', 'Modifier un téléchargement')

@section('content')
@php($sections = ['acars' => 'ACARS', 'fleet' => 'Avions et flotte', 'airports' => 'Aéroports et HUBs', 'documents' => 'Documents'])
@php($category = strtolower(str_replace('Modules\\Promethee\\Download\\', '', (string) $asset->ref_model)))
@php($subcategory = trim((string) $asset->ref_model_id))
@php($isExternal = $asset->isExternalFile)
@if ($errors->any())
<div class="notice danger" role="alert">
    <strong>Impossible d’enregistrer les modifications.</strong>
    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
@if (session('success'))
<div class="notice success" role="status">{{ session('success') }}</div>
@endif

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
