@extends('promethee::layout')
@section('title', 'Gestion des téléchargements')

@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush

@section('content')
@php($sections = ['acars' => 'ACARS & Hermès', 'fleet' => 'Avions et flotte', 'airports' => 'Aéroports et HUBs', 'documents' => 'Documents', 'uncategorized' => 'Sans catégorie / à classer'])

<div class="admin-workspace-page">
  @if ($errors->any())
    <div class="notice danger" role="alert">
      <strong>Impossible d’enregistrer le téléchargement.</strong>
      <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="ops-header compact">
    <div>
      <span class="eyebrow">ADMINISTRATION</span>
      <h1>Centre de téléchargements.</h1>
      <p>Créez des sous-catégories pour que chaque espace pilote ait sa page dédiée. Choisissez « Documents » pour alimenter la documentation interne.</p>
    </div>
    <a class="button outline" href="{{ route('promethee.downloads') }}">Voir le centre pilote</a>
  </div>

  <div class="admin-master-detail"
       id="downloads-workspace"
       data-admin-master-detail
       data-workspace-key="downloads"
       data-master-default="download-create">
    <aside class="admin-master-pane" aria-label="Ressources téléchargeables">
      <div class="admin-master-toolbar">
        <label>Rechercher une ressource
          <input type="search" data-master-filter placeholder="Hermès, manuel, A319…">
        </label>
      </div>

      <div class="admin-master-list" role="tablist" aria-orientation="vertical">
        <button type="button" class="admin-master-row" data-master-target="download-create" data-master-search="nouvelle ressource publier téléchargement document">
          <span class="admin-master-row-main">
            <strong>Publier une ressource</strong>
            <small>Fichier ou URL externe</small>
          </span>
          <span class="tag">+</span>
        </button>

        @foreach($sections as $key => $title)
          <div class="admin-master-section-label">{{ $title }}</div>
          @forelse($groups->get($key, collect())->sortBy('name') as $file)
            <button type="button"
                    class="admin-master-row"
                    data-master-target="download-{{ $file->id }}"
                    data-master-search="{{ $file->name }} {{ $file->description }} {{ $file->ref_model_id }} {{ $key }}">
              <span class="admin-master-row-main">
                <strong>{{ $file->name }}</strong>
                <small>{{ trim((string) $file->ref_model_id) ?: 'Général' }}</small>
              </span>
              @if($file->isExternalFile)
                <span class="tag">URL</span>
              @else
                <span class="tag">FICHIER</span>
              @endif
            </button>
          @empty
            <div class="admin-master-empty">Aucune ressource.</div>
          @endforelse
        @endforeach
        <div class="admin-master-empty" data-master-empty hidden>Aucune ressource ne correspond.</div>
      </div>
    </aside>

    <div class="admin-detail-pane">
      <button type="button" class="button outline admin-master-back" data-master-back>← Retour à la liste</button>

      <section class="admin-detail-panel" data-detail-panel="download-create">
        <section class="panel">
          <div class="panel-heading">
            <div>
              <span class="eyebrow">NOUVELLE RESSOURCE</span>
              <h2 data-detail-focus>Publier un téléchargement</h2>
              <p>Les documents internes restent réservés aux pilotes connectés sauf si vous cochez explicitement l’accès public.</p>
            </div>
          </div>

          <form method="post" enctype="multipart/form-data" action="{{ route('admin.promethee.downloads.store') }}" class="form-grid" id="download-create-form">
            @csrf
            <label class="full">Nom
              <input name="name" value="{{ old('name') }}" required placeholder="Ex. ITF-003b · Réaliser un vol Air Inter VA">
            </label>
            <label>Catégorie
              <select name="category" id="download-category">
                @foreach(collect($sections)->except('uncategorized') as $key=>$title)
                  <option value="{{ $key }}" @selected(old('category')===$key)>{{ $title }}</option>
                @endforeach
              </select>
            </label>
            <label data-generic-subcategory>Sous-catégorie
              <input name="subcategory" maxlength="80" value="{{ old('subcategory') }}" placeholder="Ex. MSFS, Livrées, Manuels">
            </label>
            <label data-document-field>Famille documentaire
              <select name="document_section">
                <option value="general" @selected(old('document_section')==='general')>Général</option>
                <option value="operations" @selected(old('document_section')==='operations')>Opérations</option>
                <option value="career" @selected(old('document_section')==='career')>Carrière</option>
                <option value="training" @selected(old('document_section')==='training')>Formation</option>
                <option value="aircraft" @selected(old('document_section')==='aircraft')>Documentation avion</option>
                <option value="regulations" @selected(old('document_section')==='regulations')>Réglementation</option>
                <option value="forms" @selected(old('document_section')==='forms')>Formulaires</option>
              </select>
            </label>
            <label data-document-field>Type d’avion
              <input name="aircraft_type" list="document-aircraft-types" value="{{ old('aircraft_type') }}" placeholder="Tous / ex. A319">
              <datalist id="document-aircraft-types">@foreach($aircraftTypes as $type)<option value="{{ $type }}"></option>@endforeach</datalist>
            </label>
            <label>Fichier<input name="file" type="file" accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx,.txt,.zip,.png,.jpg,.jpeg"></label>
            <label>ou URL<input name="url" type="url" value="{{ old('url') }}" placeholder="https://…"></label>
            <label class="full">Description<textarea name="description" rows="4" placeholder="Résumé, version, usage du document…">{{ old('description') }}</textarea></label>
            <label><input name="public" value="1" type="checkbox" @checked(old('public'))> Accessible sans connexion</label>
            <button>Publier</button>
          </form>
        </section>
      </section>

      @foreach($sections as $key => $title)
        @foreach($groups->get($key, collect()) as $file)
          <section class="admin-detail-panel" data-detail-panel="download-{{ $file->id }}" hidden>
            <article class="panel">
              <div class="admin-detail-heading">
                <div>
                  <span class="eyebrow">{{ strtoupper($key) }}</span>
                  <h2 data-detail-focus>{{ $file->name }}</h2>
                  <p>{{ trim((string) $file->ref_model_id) ?: 'Général' }}</p>
                </div>
                <span class="tag">{{ $file->isExternalFile ? 'URL EXTERNE' : 'FICHIER' }}</span>
              </div>

              <div class="admin-detail-metrics admin-workspace-spaced">
                <article><span>Téléchargements</span><strong>{{ $file->download_count }}</strong></article>
                <article><span>Dernière modification</span><strong>{{ optional($file->updated_at)->setTimezone('Europe/Paris')?->format('d/m/Y') ?: '—' }}</strong></article>
                <article><span>Accès</span><strong>{{ $file->public ? 'Public' : 'Pilotes' }}</strong></article>
              </div>

              @if($file->description)
                <div class="admin-workspace-spaced">
                  <span class="eyebrow">DESCRIPTION</span>
                  <p>{{ $file->description }}</p>
                </div>
              @endif

              <div class="admin-workspace-spaced">
                <span class="eyebrow">SOURCE</span>
                <p><strong>{{ $file->isExternalFile ? 'URL externe' : $file->filename }}</strong></p>
                @if($file->isExternalFile)
                  <a class="button outline" href="{{ $file->url }}" target="_blank" rel="noopener noreferrer">Tester le lien ↗</a>
                @endif
              </div>

              <div class="admin-workspace-form-action admin-workspace-spaced">
                <a class="button" href="{{ route('admin.promethee.downloads.edit', $file->id) }}">Modifier</a>
                @if($key !== 'uncategorized')
                  <a class="button outline" href="{{ route('promethee.downloads.category', $key) }}">Voir la catégorie</a>
                @endif
                <form method="post" action="{{ route('admin.promethee.downloads.delete', $file->id) }}" onsubmit="return confirm('Supprimer définitivement ce téléchargement ?')">
                  @csrf
                  @method('delete')
                  <button class="button outline" type="submit">Supprimer</button>
                </form>
              </div>
            </article>
          </section>
        @endforeach
      @endforeach
    </div>
  </div>
</div>
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
    documentFields.forEach(field => field.hidden = !isDocuments);
    if (generic) generic.hidden = isDocuments;
  };
  category.addEventListener('change', sync);
  sync();
});
</script>
<script src="{{ asset('promethee-assets/promethee-admin-workspaces.js') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.js')) }}"></script>
@endpush
