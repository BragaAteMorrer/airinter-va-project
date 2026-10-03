@extends('promethee::layout')
@section('title', 'Ma documentation Air Inter')

@section('content')
<div class="ops-header compact">
    <div>
        <span class="eyebrow">COMPAGNIE · ESPACE PILOTE</span>
        <h1>Ma documentation Air Inter.</h1>
        <p>Les procédures générales et les documents correspondant aux appareils auxquels votre profil donne accès.</p>
    </div>
    <div class="documentation-header-actions">
        <a class="button outline" href="{{ route('promethee.documents') }}">Bibliothèque complète</a>
        <a class="button outline" href="{{ route('promethee.downloads') }}">Ressources techniques</a>
    </div>
</div>
<nav class="pilot-hub-nav documentation-hub-nav" aria-label="Documentation Air Inter">
    <a href="#my-documentation">Pour mon profil <span>{{ $documents->count() }}</span></a>
    <a href="{{ route('promethee.documents') }}">Bibliothèque complète <span>{{ $allDocumentsCount }}</span></a>
    <a href="{{ route('promethee.downloads') }}">Ressources techniques <span>↗</span></a>
</nav>


<section class="control-strip">
    <article><span>Documents pour vous</span><strong>{{ $documents->count() }}</strong><small>sur {{ $allDocumentsCount }} document(s)</small></article>
    <article><span>Grade actuel</span><strong class="documentation-rank">{{ $pilot->rank?->name ?? 'Pilote' }}</strong><small>{{ $pilot->ident ?? $pilot->pilot_id }}</small></article>
    <article><span>Types accessibles</span><strong>{{ $aircraftTypes->count() }}</strong><small>{{ $aircraftTypes->take(3)->implode(' · ') ?: 'Aucun type spécifique' }}</small></article>
    <article><span>Bibliothèque</span><strong>{{ $sections->count() }}</strong><small>rubrique(s) utile(s)</small></article>
</section>

<section class="panel" id="my-documentation">
    <div class="panel-heading">
        <div>
            <span class="eyebrow">RECHERCHE</span>
            <h2>Vos documents</h2>
            <p>Les documents généraux sont toujours affichés. Les documents avion sont filtrés selon vos appareils accessibles.</p>
        </div>
    </div>
    <div class="form-grid">
        <label class="full">Rechercher
            <input type="search" id="my-doc-search" placeholder="Code, titre, procédure, appareil…">
        </label>
        <label>Rubrique
            <select id="my-doc-section">
                <option value="">Toutes</option>
                @foreach($sections->keys()->sort() as $section)
                    <option value="{{ strtolower($section) }}">{{ $section }}</option>
                @endforeach
            </select>
        </label>
        <label>Type d’avion
            <select id="my-doc-aircraft">
                <option value="">Tous mes types</option>
                <option value="general">Documents généraux</option>
                @foreach($aircraftTypes as $type)
                    <option value="{{ strtolower($type) }}">{{ $type }}</option>
                @endforeach
            </select>
        </label>
        <label>Format
            <select id="my-doc-format">
                <option value="">Tous</option>
                <option value="pdf">PDF</option>
                <option value="ppt">PowerPoint</option>
                <option value="doc">Word</option>
                <option value="xls">Excel</option>
                <option value="image">Image</option>
            </select>
        </label>
        <div class="documentation-filter-action"><button class="button outline" type="button" id="my-doc-reset">Réinitialiser</button></div>
    </div>
</section>

@forelse($sections as $section => $entries)
<section class="panel my-doc-section" data-section-group="{{ strtolower($section) }}">
    <div class="panel-heading">
        <div><span class="eyebrow">AIR INTER · {{ mb_strtoupper($section) }}</span><h2>{{ $section }}</h2></div>
        <span class="tag">{{ $entries->count() }} document(s)</span>
    </div>
    <div class="document-library">
        @foreach($entries as $file)
            @php
                $updated = $file->updated_at ?: $file->created_at;
                $type = $file->promethee_aircraft_type;
                $ext = $file->promethee_extension;
                $formatGroup = in_array(strtolower($ext), ['ppt','pptx']) ? 'ppt' : (in_array(strtolower($ext), ['doc','docx']) ? 'doc' : (in_array(strtolower($ext), ['xls','xlsx']) ? 'xls' : (in_array(strtolower($ext), ['png','jpg','jpeg','gif','webp']) ? 'image' : strtolower($ext))));
            @endphp
            <article class="document-card my-document-card"
                     data-search="{{ strtolower($file->name.' '.$file->description.' '.$section.' '.$type.' '.$ext) }}"
                     data-section="{{ strtolower($section) }}"
                     data-aircraft="{{ strtolower($type) }}"
                     data-format="{{ $formatGroup }}">
                <span class="file-mark">{{ $ext ?: 'DOC' }}</span>
                <div>
                    <strong>{{ $file->name }}</strong>
                    <p>{{ $file->description ?: 'Document de référence Air Inter.' }}</p>
                    <small>
                        {{ $type !== '' ? $type.' · ' : 'Général · ' }}
                        {{ $ext ?: 'DOCUMENT' }}
                        @if($updated) · Mis à jour le {{ $updated->setTimezone('Europe/Paris')->format('d/m/Y') }}@endif
                    </small>
                </div>
                <a href="{{ route('promethee.documents.show', $file->id) }}">Consulter →</a>
            </article>
        @endforeach
    </div>
</section>
@empty
<section class="panel"><p class="empty">Aucun document correspondant à votre profil n’est encore publié.</p></section>
@endforelse

<section class="panel" id="my-doc-empty" hidden>
    <p class="empty">Aucun document ne correspond à ces filtres.</p>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const search = document.getElementById('my-doc-search');
  const section = document.getElementById('my-doc-section');
  const aircraft = document.getElementById('my-doc-aircraft');
  const format = document.getElementById('my-doc-format');
  const reset = document.getElementById('my-doc-reset');
  const cards = [...document.querySelectorAll('.my-document-card')];
  const groups = [...document.querySelectorAll('.my-doc-section')];
  const empty = document.getElementById('my-doc-empty');

  const normalize = (value) => (value || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

  const apply = () => {
    const q = normalize(search.value);
    const wantedSection = normalize(section.value);
    const wantedAircraft = normalize(aircraft.value);
    const wantedFormat = normalize(format.value);

    cards.forEach((card) => {
      const cardAircraft = normalize(card.dataset.aircraft);
      const aircraftMatch = !wantedAircraft
        || (wantedAircraft === 'general' ? cardAircraft === '' : cardAircraft === wantedAircraft);

      card.hidden = !(
        (!q || normalize(card.dataset.search).includes(q))
        && (!wantedSection || normalize(card.dataset.section) === wantedSection)
        && aircraftMatch
        && (!wantedFormat || normalize(card.dataset.format) === wantedFormat)
      );
    });

    let visibleGroups = 0;
    groups.forEach((group) => {
      const visible = [...group.querySelectorAll('.my-document-card')].some((card) => !card.hidden);
      group.hidden = !visible;
      if (visible) visibleGroups++;
    });

    empty.hidden = visibleGroups > 0;
  };

  search.addEventListener('input', apply);
  [section, aircraft, format].forEach((field) => field.addEventListener('change', apply));
  reset.addEventListener('click', () => {
    search.value = '';
    section.value = '';
    aircraft.value = '';
    format.value = '';
    apply();
  });
});
</script>
@endpush
