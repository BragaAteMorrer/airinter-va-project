@extends('promethee::layout')
@section('title', $sections[$category][0])
@section('content')
<div class="ops-header compact">
    <div><span class="eyebrow">{{ $category === 'documents' ? 'COMPAGNIE · BIBLIOTHÈQUE INTERNE' : 'CENTRE PILOTE' }}</span><h1>{{ $category === 'documents' ? 'Documentation interne' : $sections[$category][0] }}.</h1><p>{{ $category === 'documents' ? 'Référentiel documentaire Air Inter VA : procédures, carrière, formation et documentation par type d’avion.' : $sections[$category][1] }}</p></div>
    <a class="button outline" href="{{ route('promethee.downloads') }}">Toutes les ressources</a>
</div>

@if($category === 'documents')
<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">RECHERCHE DOCUMENTAIRE</span><h2>Filtrer la bibliothèque</h2></div></div>
  <div class="form-grid">
    <label class="full">Recherche<input type="search" id="document-search" placeholder="Code, titre, description, type d’avion…"></label>
    <label>Catégorie<select id="document-section"><option value="">Toutes</option>@foreach($files->keys()->map(fn($key) => trim(explode('·', $key, 2)[0]))->unique()->sort() as $section)<option value="{{ strtolower($section) }}">{{ $section }}</option>@endforeach</select></label>
    <label>Type d’avion<select id="document-aircraft"><option value="">Tous</option>@foreach($files->keys()->map(fn($key) => trim(explode('·', $key, 2)[1] ?? ''))->filter()->unique()->sort() as $type)<option value="{{ strtolower($type) }}">{{ $type }}</option>@endforeach</select></label>
    <label>Format<select id="document-format"><option value="">Tous</option><option value="pdf">PDF</option><option value="ppt">PowerPoint</option><option value="doc">Word</option><option value="xls">Excel</option><option value="zip">ZIP</option></select></label>
    <div style="align-self:end"><button type="button" class="button outline" id="document-reset">Réinitialiser</button></div>
  </div>
</section>
@endif

@forelse($files as $subcategory => $entries)
<section class="panel">
    <div class="panel-heading">
        <div><span class="eyebrow">{{ strtoupper($category) }}</span><h2>{{ $subcategory }}</h2></div>
        <span class="tag">{{ $entries->count() }} ressource(s)</span>
    </div>
    <div @class(['document-library' => $category === 'documents', 'route-list' => $category !== 'documents'])>
        @foreach($entries as $file)
        @php
            $urlPath = parse_url((string) $file->path, PHP_URL_PATH) ?: '';
            $extension = strtoupper(pathinfo($urlPath, PATHINFO_EXTENSION));
            $updated = $file->updated_at ?: $file->created_at;
        @endphp
        @if($category === 'documents')
        <article class="document-card" data-document-card data-document-section="{{ strtolower(trim(explode('·', $subcategory, 2)[0])) }}" data-document-aircraft="{{ strtolower(trim(explode('·', $subcategory, 2)[1] ?? '')) }}" data-document-format="{{ strtolower($extension) }}" data-document-search="{{ strtolower($file->name.' '.$file->description.' '.$subcategory.' '.$extension) }}">
            <span class="file-mark">{{ $extension ?: 'DOC' }}</span>
            <div>
                <strong>{{ $file->name }}</strong>
                <p>{{ $file->description ?: 'Document de référence Air Inter.' }}</p>
                @if($updated)<small>Mis à jour le {{ $updated->setTimezone('Europe/Paris')->format('d/m/Y') }}</small>@endif
            </div>
            <a href="{{ route('promethee.documents.show', $file->id) }}">Consulter en ligne →</a>
        </article>
        @else
        <article>
            <strong>{{ $file->name }}</strong>
            @if($file->description)<span>{{ $file->description }}</span>@endif
            <small>{{ $extension ?: 'RESSOURCE' }}@if($updated) · Mis à jour le {{ $updated->setTimezone('Europe/Paris')->format('d/m/Y') }}@endif</small>
            <a class="button outline" href="{{ route('promethee.downloads.download', $file->id) }}">Télécharger</a>
        </article>
        @endif
        @endforeach
    </div>
</section>
@empty
<section class="panel"><p class="empty">Aucune ressource publiée dans cette catégorie.</p></section>
@endforelse
@endsection

@if($category === 'documents')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const search = document.getElementById('document-search');
  const section = document.getElementById('document-section');
  const aircraft = document.getElementById('document-aircraft');
  const format = document.getElementById('document-format');
  const reset = document.getElementById('document-reset');
  const cards = [...document.querySelectorAll('[data-document-card]')];
  const normalize = (value) => (value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const apply = () => {
    const q = normalize(search.value), s = normalize(section.value), a = normalize(aircraft.value), f = normalize(format.value);
    cards.forEach((card) => {
      const ext = normalize(card.dataset.documentFormat);
      const formatMatches = !f || (f === 'ppt' ? ['ppt','pptx'].includes(ext) : f === 'doc' ? ['doc','docx'].includes(ext) : f === 'xls' ? ['xls','xlsx'].includes(ext) : ext === f);
      card.hidden = !((!q || normalize(card.dataset.documentSearch).includes(q)) && (!s || normalize(card.dataset.documentSection) === s) && (!a || normalize(card.dataset.documentAircraft) === a) && formatMatches);
    });
    document.querySelectorAll('.document-library').forEach((library) => {
      const visible = [...library.querySelectorAll('[data-document-card]')].some((card) => !card.hidden);
      library.closest('section').hidden = !visible;
    });
  };
  [search, section, aircraft, format].forEach((field) => field.addEventListener(field === search ? 'input' : 'change', apply));
  reset.addEventListener('click', () => { search.value=''; section.value=''; aircraft.value=''; format.value=''; apply(); });
});
</script>
@endpush
@endif
