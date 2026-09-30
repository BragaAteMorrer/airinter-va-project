@extends('promethee::layout')

@section('title', 'Modifier un téléchargement')

@section('content')
@php
    $sections = [
        'acars' => 'ACARS & Hermès',
        'fleet' => 'Avions et flotte',
        'airports' => 'Aéroports et HUBs',
        'documents' => 'Documents',
    ];
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
    $documentParts = array_map('trim', explode('·', $subcategory, 2));
    $documentSectionLabel = $documentParts[0] ?? 'Général';
    $documentAircraftType = $documentParts[1] ?? '';
    $documentSections = [
        'general' => 'Général', 'operations' => 'Opérations', 'career' => 'Carrière',
        'training' => 'Formation', 'aircraft' => 'Documentation avion',
        'regulations' => 'Réglementation', 'forms' => 'Formulaires',
    ];
    $documentSection = array_search($documentSectionLabel, $documentSections, true) ?: 'general';
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
            <select name="category" id="download-category">
                @foreach ($sections as $key => $title)
                    <option value="{{ $key }}" @selected(old('category', $category) === $key)>{{ $title }}</option>
                @endforeach
            </select>
        </label>
        <label data-generic-subcategory>Sous-catégorie<input name="subcategory" maxlength="80" value="{{ old('subcategory', $subcategory === $category ? '' : $subcategory) }}" placeholder="Ex. MSFS, Livrées, Manuels"></label>
        <label data-document-field>Famille documentaire
            <select name="document_section">
                @foreach($documentSections as $key => $label)<option value="{{ $key }}" @selected(old('document_section', $documentSection) === $key)>{{ $label }}</option>@endforeach
            </select>
        </label>
        <label data-document-field>Type d’avion
            <input name="aircraft_type" list="edit-document-aircraft-types" value="{{ old('aircraft_type', $documentAircraftType) }}" placeholder="Tous / ex. A319">
            <datalist id="edit-document-aircraft-types">@foreach($aircraftTypes as $type)<option value="{{ $type }}"></option>@endforeach</datalist>
        </label>
        <label>Remplacer le fichier<input name="file" type="file"></label>
        <label>ou remplacer par une URL<input name="url" type="url" value="{{ old('url', $isExternal ? $asset->path : '') }}" placeholder="https://…"></label>
        <label>Description<textarea name="description">{{ old('description', $asset->description) }}</textarea></label>
        <label><input name="public" value="1" type="checkbox" @checked(old('public', $asset->public))> accessible sans connexion</label>
        <button>Enregistrer les modifications</button>
    </form>
    <p class="muted">Laissez le fichier et l’URL vides pour conserver la source actuelle. L’envoi d’un fichier remplace définitivement la source précédente.</p>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const category = document.getElementById('download-category');
  const documentFields = [...document.querySelectorAll('[data-document-field]')];
  const generic = document.querySelector('[data-generic-subcategory]');
  if (!category) return;
  const sync = () => {
    const isDocuments = category.value === 'documents';
    documentFields.forEach((field) => field.hidden = !isDocuments);
    if (generic) generic.hidden = isDocuments;
  };
  category.addEventListener('change', sync);
  sync();
});
</script>
@endpush
