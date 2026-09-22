@extends('promethee::layout')
@section('title', 'Gestion des téléchargements')
@section('content')
@php($sections = ['acars' => 'ACARS', 'fleet' => 'Avions et flotte', 'airports' => 'Aéroports et HUBs', 'documents' => 'Documents'])
@if ($errors->any())
<div class="notice danger" role="alert">
    <strong>Impossible d’enregistrer le téléchargement.</strong>
    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
@if (session('success'))
<div class="notice success" role="status">{{ session('success') }}</div>
@endif
<div class="ops-header compact"><div><span class="eyebrow">ADMINISTRATION</span><h1>Centre de téléchargements.</h1><p>Créez des sous-catégories pour que chaque espace pilote ait sa page dédiée. Choisissez « Documents » pour alimenter la bibliothèque de la communauté.</p></div><a class="button outline" href="{{ route('promethee.downloads') }}">Voir le centre pilote</a></div>
<section class="panel"><div class="panel-heading"><div><span class="eyebrow">NOUVELLE RESSOURCE</span><h2>Publier un téléchargement</h2></div></div><form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.downloads.store') }}" class="flight-filter">@csrf<label class="filter-wide">Nom<input name="name" value="{{ old('name') }}" required></label><label>Catégorie<select name="category">@foreach($sections as $key=>$title)<option value="{{ $key }}" @selected(old('category')===$key)>{{ $title }}</option>@endforeach</select></label><label>Sous-catégorie<input name="subcategory" maxlength="80" value="{{ old('subcategory') }}" placeholder="Ex. MSFS, Airbus, Manuels"></label><label>Fichier<input name="file" type="file"></label><label>ou URL<input name="url" type="url" value="{{ old('url') }}" placeholder="https://…"></label><label>Description<textarea name="description">{{ old('description') }}</textarea></label><label><input name="public" value="1" type="checkbox"> accessible sans connexion</label><button>Publier</button></form></section>
@foreach($sections as $key => $title)
<section class="panel table-wrap">
    <div class="panel-heading">
        <div><span class="eyebrow">{{ strtoupper($key) }}</span><h2>{{ $title }}</h2></div>
        <a class="button outline" href="{{ route('promethee.downloads.category', $key) }}">Page dédiée</a>
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
                            @if (str_starts_with((string) $file->ref_model, 'Modules\\Promethee\\Download\\'))
                                <a class="button outline" href="{{ route('admin.promethee.downloads.edit', $file->id) }}">Modifier</a>
                                <form method="post" action="{{ route('admin.promethee.downloads.delete', $file->id) }}">
                                    @csrf
                                    @method('delete')
                                    <button class="button outline">Supprimer</button>
                                </form>
                            @else
                                <span class="muted">Ressource liée au catalogue phpVMS</span>
                            @endif
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
