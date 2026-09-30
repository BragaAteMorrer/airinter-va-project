@extends('promethee::layout')
@section('title', 'Gestion des téléchargements')
@section('content')
@php($sections = ['acars' => 'ACARS & Hermès', 'fleet' => 'Avions et flotte', 'airports' => 'Aéroports et HUBs', 'documents' => 'Documents', 'uncategorized' => 'Sans catégorie / à classer'])
@if ($errors->any())
<div class="notice danger" role="alert">
    <strong>Impossible d’enregistrer le téléchargement.</strong>
    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
<div class="ops-header compact"><div><span class="eyebrow">ADMINISTRATION</span><h1>Centre de téléchargements.</h1><p>Créez des sous-catégories pour que chaque espace pilote ait sa page dédiée. Choisissez « Documents » pour alimenter la documentation interne : général, opérations, carrière, formation ou documentation par type d’avion.</p></div><a class="button outline" href="{{ route('promethee.downloads') }}">Voir le centre pilote</a></div>
<section class="panel">
<div class="panel-heading"><div><span class="eyebrow">NOUVELLE RESSOURCE</span><h2>Publier un téléchargement</h2><p>Les documents internes restent réservés aux pilotes connectés sauf si vous cochez explicitement l’accès public.</p></div></div>
<form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.downloads.store') }}" class="flight-filter" id="download-create-form">
@csrf
<label class="filter-wide">Nom<input name="name" value="{{ old('name') }}" required placeholder="Ex. ITF-003b · Réaliser un vol Air Inter VA"></label>
<label>Catégorie<select name="category" id="download-category">@foreach(collect($sections)->except('uncategorized') as $key=>$title)<option value="{{ $key }}" @selected(old('category')===$key)>{{ $title }}</option>@endforeach</select></label>
<label data-generic-subcategory>Sous-catégorie<input name="subcategory" maxlength="80" value="{{ old('subcategory') }}" placeholder="Ex. MSFS, Livrées, Manuels"></label>
<label data-document-field>Famille documentaire
<select name="document_section">
<option value="general" @selected(old('document_section')==='general')>Général</option>
<option value="operations" @selected(old('document_section')==='operations')>Opérations</option>
<option value="career" @selected(old('document_section')==='career')>Carrière</option>
<option value="training" @selected(old('document_section')==='training')>Formation</option>
<option value="aircraft" @selected(old('document_section')==='aircraft')>Documentation avion</option>
<option value="regulations" @selected(old('document_section')==='regulations')>Réglementation</option>
<option value="forms" @selected(old('document_section')==='forms')>Formulaires</option>
</select></label>
<label data-document-field>Type d’avion
<input name="aircraft_type" list="document-aircraft-types" value="{{ old('aircraft_type') }}" placeholder="Tous / ex. A319">
<datalist id="document-aircraft-types">@foreach($aircraftTypes as $type)<option value="{{ $type }}"></option>@endforeach</datalist>
</label>
<label>Fichier<input name="file" type="file" accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.txt,.zip,.png,.jpg,.jpeg"></label>
<label>ou URL<input name="url" type="url" value="{{ old('url') }}" placeholder="https://…"></label>
<label class="filter-wide">Description<textarea name="description" placeholder="Résumé, version, usage du document…">{{ old('description') }}</textarea></label>
<label><input name="public" value="1" type="checkbox" @checked(old('public'))> accessible sans connexion</label>
<button>Publier</button>
</form>
</section>
@foreach($sections as $key => $title)
<section class="panel table-wrap">
    <div class="panel-heading">
        <div><span class="eyebrow">{{ strtoupper($key) }}</span><h2>{{ $title }}</h2></div>
        @if($key !== 'uncategorized')<a class="button outline" href="{{ route('promethee.downloads.category', $key) }}">Page dédiée</a>@else<span class="tag">ADMIN UNIQUEMENT</span>@endif
    </div>
    <table>
        <thead><tr><th>Sous-catégorie</th><th>Nom</th><th>Description</th><th>Source</th><th></th></tr></thead>
        <tbody>
            @forelse ($groups->get($key, collect())->groupBy(fn ($file) => trim((string) $file->ref_model_id) ?: 'Général') as $subcategory => $files)
                @foreach ($files as $file)
                    <tr>
                        <td>{{ strtolower($subcategory) === $key ? 'Général' : $subcategory }}</td>
                        <td>{{ $file->name }}</td>
                        <td>{{ $file->description }}</td>
                        <td>
    <strong>{{ $file->isExternalFile ? 'URL externe' : $file->filename }}</strong>
    @if ($file->isExternalFile)
        <br><a href="{{ $file->url }}" target="_blank" rel="noopener noreferrer">Tester le lien ↗</a>
    @endif
    <br><small class="muted">{{ $file->download_count }} téléchargement(s) · modifié le {{ optional($file->updated_at)->setTimezone('Europe/Paris')?->format('d/m/Y H:i') ?: '—' }}</small>
</td>
                        <td>
                            <a class="button outline" href="{{ route('admin.promethee.downloads.edit', $file->id) }}">Modifier</a>
                            <form method="post" action="{{ route('admin.promethee.downloads.delete', $file->id) }}" onsubmit="return confirm('Supprimer définitivement ce téléchargement ?')">
                                @csrf
                                @method('delete')
                                <button class="button outline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="5">Aucune ressource.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endforeach
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
