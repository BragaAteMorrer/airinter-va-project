@extends('promethee::layout')
@section('title','CRM & communications')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">RELATION PILOTES</span>
    <h1>CRM & communications.</h1>
    <p>Composez des campagnes ciblées, choisissez l’adresse expéditrice Air Inter et suivez chaque envoi individuellement.</p>
  </div>
  <span class="tag">{{ $campaigns->count() }} campagne(s) récente(s)</span>
</div>

<div class="two-columns crm-top-grid">
  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">EXPÉDITEURS CPANEL</span><h2>Adresses d’envoi</h2><p>Ajoutez ici uniquement les adresses réellement créées/autorisé​es sur votre hébergement cPanel.</p></div></div>

    <form method="post" action="{{ route('admin.promethee.crm.senders.save') }}" class="form-grid">
      @csrf
      <label>Nom affiché<input name="name" required maxlength="120" placeholder="Air Inter · Direction des Opérations"></label>
      <label>Adresse e-mail<input name="email" type="email" required maxlength="191" placeholder="operations@airinter-va.org"></label>
      <label>Reply-To<input name="reply_to" type="email" maxlength="191" placeholder="operations@airinter-va.org"></label>
      <label><input type="checkbox" name="active" value="1" checked> Adresse active</label>
      <label><input type="checkbox" name="is_default" value="1"> Adresse par défaut</label>
      <button>Ajouter l’adresse</button>
    </form>

    <div class="crm-sender-list">
      @forelse($senders as $sender)
      <article class="crm-sender-card">
        <div>
          <strong>{{ $sender->name }}</strong>
          <span>{{ $sender->email }}</span>
          <small>{{ $sender->reply_to ? 'Réponse vers '.$sender->reply_to : 'Reply-To identique à l’expéditeur' }}</small>
        </div>
        <div class="crm-sender-actions">
          @if($sender->is_default)<span class="tag">PAR DÉFAUT</span>@endif
          @if(!$sender->active)<span class="tag">INACTIVE</span>@endif
          <details>
            <summary>Modifier</summary>
            <form method="post" action="{{ route('admin.promethee.crm.senders.save') }}" class="form-grid compact-form">
              @csrf
              <input type="hidden" name="id" value="{{ $sender->id }}">
              <label>Nom<input name="name" value="{{ $sender->name }}" required></label>
              <label>E-mail<input name="email" type="email" value="{{ $sender->email }}" required></label>
              <label>Reply-To<input name="reply_to" type="email" value="{{ $sender->reply_to }}"></label>
              <label><input type="checkbox" name="active" value="1" @checked($sender->active)> Active</label>
              <label><input type="checkbox" name="is_default" value="1" @checked($sender->is_default)> Par défaut</label>
              <button>Enregistrer</button>
            </form>
          </details>
          <form method="post" action="{{ route('admin.promethee.crm.senders.delete',$sender->id) }}" onsubmit="return confirm('Supprimer cette adresse expéditrice ?');">
            @csrf @method('DELETE')
            <button type="submit" class="outline danger">Supprimer</button>
          </form>
        </div>
      </article>
      @empty
      <p class="empty">Ajoutez d’abord une adresse expéditrice cPanel avant d’envoyer une campagne.</p>
      @endforelse
    </div>
  </section>

  <section class="panel crm-help">
    <div class="panel-heading"><div><span class="eyebrow">PERSONNALISATION</span><h2>Variables disponibles</h2></div></div>
    <div class="route-list">
      <code>@{{name}}</code><span>Nom du pilote</span>
      <code>@{{pilot_id}}</code><span>Matricule</span>
      <code>@{{rank}}</code><span>Grade</span>
      <code>@{{airline}}</code><span>Compagnie</span>
      <code>@{{base}}</code><span>Base d’attache</span>
      <code>@{{hours}}</code><span>Heures de vol arrondies</span>
    </div>
    <p class="hint">Les e-mails sont envoyés un par un : aucun pilote ne voit la liste des autres destinataires.</p>
  </section>
</div>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">NOUVELLE CAMPAGNE</span><h2>Composer et cibler</h2><p>Le serveur recalcule toujours la sélection au moment de l’envoi.</p></div><strong id="crmAudienceCount" class="metric-tag">0 destinataire</strong></div>

  @if($senders->where('active',true)->isEmpty())
    <div class="notice error">Ajoutez et activez au moins une adresse expéditrice avant l’envoi.</div>
  @endif

  <form method="post" action="{{ route('admin.promethee.crm.send') }}" id="crmCampaignForm">
    @csrf
    <div class="crm-compose-grid">
      <div class="crm-compose-main">
        <div class="form-grid">
          <label>Adresse expéditrice
            <select name="sender_id" required>
              <option value="">Choisir…</option>
              @foreach($senders->where('active',true) as $sender)
              <option value="{{ $sender->id }}" @selected($sender->is_default)>{{ $sender->name }} · {{ $sender->email }}</option>
              @endforeach
            </select>
          </label>
          <label>Objet
            <input name="subject" required maxlength="191" placeholder="Information importante pour @{{name}}">
          </label>
          <label class="full">Message
            <textarea name="body" rows="14" required maxlength="30000" placeholder="Bonjour @{{name}},&#10;&#10;..."></textarea>
          </label>
        </div>
      </div>

      <aside class="crm-audience-builder">
        <div class="crm-audience-head">
          <div>
            <span class="eyebrow">CIBLAGE</span>
            <h3>Destinataires</h3>
          </div>
          <button type="button" class="button outline crm-reset-filters" id="crmResetFilters">Réinitialiser</button>
        </div>

        <label class="crm-check crm-all-active">
          <input type="checkbox" name="all_active" value="1" data-crm-filter>
          <span><strong>Tous les pilotes actifs + en congé</strong><small>Sélection rapide de l'équipage actuellement inscrit.</small></span>
        </label>

        <details class="crm-filter-block" open>
          <summary><span>Statut</span><b class="crm-filter-count" data-count-for="states">0</b></summary>
          <div class="crm-filter-content">
            <div class="crm-filter-actions">
              <button type="button" class="crm-link-button" data-check-all="states">Tout cocher</button>
              <button type="button" class="crm-link-button" data-uncheck-all="states">Tout décocher</button>
            </div>
            <div class="crm-chip-grid" data-checkbox-group="states">
              <label class="crm-chip"><input type="checkbox" name="states[]" value="1" data-crm-filter><span>Actif</span></label>
              <label class="crm-chip"><input type="checkbox" name="states[]" value="3" data-crm-filter><span>En congé</span></label>
              <label class="crm-chip"><input type="checkbox" name="states[]" value="0" data-crm-filter><span>En attente</span></label>
              <label class="crm-chip"><input type="checkbox" name="states[]" value="2" data-crm-filter><span>Refusé</span></label>
              <label class="crm-chip"><input type="checkbox" name="states[]" value="4" data-crm-filter><span>Suspendu</span></label>
            </div>
          </div>
        </details>

        <details class="crm-filter-block" open>
          <summary><span>Grade</span><b class="crm-filter-count" data-count-for="ranks">0</b></summary>
          <div class="crm-filter-content">
            <div class="crm-filter-actions">
              <button type="button" class="crm-link-button" data-check-all="ranks">Tout cocher</button>
              <button type="button" class="crm-link-button" data-uncheck-all="ranks">Tout décocher</button>
            </div>
            <div class="crm-chip-grid" data-checkbox-group="ranks">
              @foreach($ranks as $rank)
                <label class="crm-chip">
                  <input type="checkbox" name="rank_ids[]" value="{{ $rank->id }}" data-crm-filter>
                  <span>{{ $rank->name }}</span>
                </label>
              @endforeach
            </div>
          </div>
        </details>

        <details class="crm-filter-block" open>
          <summary><span>Compagnie</span><b class="crm-filter-count" data-count-for="airlines">0</b></summary>
          <div class="crm-filter-content">
            <div class="crm-filter-actions">
              <button type="button" class="crm-link-button" data-check-all="airlines">Tout cocher</button>
              <button type="button" class="crm-link-button" data-uncheck-all="airlines">Tout décocher</button>
            </div>
            <div class="crm-chip-grid" data-checkbox-group="airlines">
              @foreach($airlines as $airline)
                <label class="crm-chip">
                  <input type="checkbox" name="airline_ids[]" value="{{ $airline->id }}" data-crm-filter>
                  <span>{{ $airline->icao }} · {{ $airline->name }}</span>
                </label>
              @endforeach
            </div>
          </div>
        </details>

        <details class="crm-filter-block" open>
          <summary><span>Base</span><b class="crm-filter-count" data-count-for="bases">0</b></summary>
          <div class="crm-filter-content">
            <div class="crm-filter-actions">
              <button type="button" class="crm-link-button" data-check-all="bases">Tout cocher</button>
              <button type="button" class="crm-link-button" data-uncheck-all="bases">Tout décocher</button>
            </div>
            <div class="crm-chip-grid" data-checkbox-group="bases">
              @foreach($bases as $base)
                <label class="crm-chip">
                  <input type="checkbox" name="bases[]" value="{{ $base }}" data-crm-filter>
                  <span>{{ $base }}</span>
                </label>
              @endforeach
            </div>
          </div>
        </details>

        <div class="crm-hours-grid">
          <label>Heures min.<input name="min_hours" type="number" min="0" step="1" data-crm-filter></label>
          <label>Heures max.<input name="max_hours" type="number" min="0" step="1" data-crm-filter></label>
        </div>

        <details class="crm-filter-block crm-pilot-block" open>
          <summary><span>Pilotes précis</span><b class="crm-filter-count" data-count-for="pilots">0</b></summary>
          <div class="crm-filter-content">
            <div class="crm-filter-actions crm-pilot-actions">
              <button type="button" class="crm-link-button" data-check-visible-pilots>Cocher les visibles</button>
              <button type="button" class="crm-link-button" data-uncheck-all="pilots">Tout décocher</button>
            </div>

            <input type="search" id="crmPilotSearch" class="crm-pilot-search" placeholder="Matricule, nom, grade, compagnie, base…">

            <div class="crm-pilot-picker" data-checkbox-group="pilots">
              @foreach($pilots as $pilot)
                @php($pilotSearchLabel = strtolower(trim(($pilot->pilot_id ?? '').' '.($pilot->name ?? '').' '.($pilot->rank?->name ?? '').' '.($pilot->airline?->icao ?? '').' '.($pilot->home_airport_id ?? ''))))
                <label class="crm-pilot-option" data-pilot-label="{{ $pilotSearchLabel }}">
                  <input type="checkbox" name="pilot_ids[]" value="{{ $pilot->id }}" data-crm-filter>
                  <span class="crm-pilot-option-main">
                    <strong>{{ $pilot->pilot_id ?: 'ITF---' }} · {{ $pilot->name }}</strong>
                    <small>
                      {{ $pilot->rank?->name ?? 'Sans grade' }}
                      @if($pilot->airline?->icao) · {{ $pilot->airline->icao }}@endif
                      @if($pilot->home_airport_id) · {{ $pilot->home_airport_id }}@endif
                    </small>
                  </span>
                </label>
              @endforeach
            </div>

            <small>Les pilotes cochés manuellement sont ajoutés au ciblage même s'ils ne correspondent pas aux autres filtres.</small>
          </div>
        </details>

        <div class="crm-audience-summary">
          <span>Sélection actuelle</span>
          <strong id="crmAudienceCountInline">0 destinataire</strong>
        </div>

        <button type="submit" class="primary crm-send-button" @disabled($senders->where('active',true)->isEmpty()) onclick="return confirm('Envoyer cette campagne aux destinataires sélectionnés ?');">Envoyer la campagne</button>
      </aside>
    </div>
  </form>
</section>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">HISTORIQUE</span><h2>Campagnes récentes</h2></div></div>
  <table>
    <thead><tr><th>Date</th><th>Objet</th><th>Expéditeur</th><th>Créée par</th><th>Dest.</th><th>Envoyés</th><th>Échecs</th><th>État</th><th></th></tr></thead>
    <tbody>
    @forelse($campaigns as $campaign)
      <tr>
        <td>{{ CarbonCarbon::parse($campaign->created_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</td>
        <td><strong>{{ $campaign->subject }}</strong></td>
        <td>{{ $campaign->sender_email ?: '—' }}</td>
        <td>{{ $campaign->creator_name ?: '—' }}</td>
        <td>{{ $campaign->recipient_count }}</td>
        <td>{{ $campaign->sent_count }}</td>
        <td>{{ $campaign->failed_count }}</td>
        <td><span class="tag">{{ strtoupper($campaign->status) }}</span></td>
        <td><a href="{{ route('admin.promethee.crm.campaigns.show',$campaign->id) }}">Détails →</a></td>
      </tr>
    @empty
      <tr><td colspan="9">Aucune campagne pour le moment.</td></tr>
    @endforelse
    </tbody>
  </table>
</section>

@push('scripts')
@php
  $crmPilotData = $pilots->map(function ($pilot) {
    return [
      'id' => (int) $pilot->id,
      'state' => (int) $pilot->state,
      'rank_id' => $pilot->rank_id ? (int) $pilot->rank_id : null,
      'airline_id' => $pilot->airline_id ? (int) $pilot->airline_id : null,
      'base' => $pilot->home_airport_id,
      'hours' => round(($pilot->flight_time ?? 0) / 60, 1),
      'email' => $pilot->email,
    ];
  })->values();
@endphp
<script>
(() => {
  const pilots = @json($crmPilotData);
  const form = document.getElementById('crmCampaignForm');
  const count = document.getElementById('crmAudienceCount');
  const inlineCount = document.getElementById('crmAudienceCountInline');
  const search = document.getElementById('crmPilotSearch');
  const reset = document.getElementById('crmResetFilters');

  if (!form || !count) return;

  const checkedValues = name =>
    [...form.querySelectorAll(`[name="${name}"]:checked`)].map(input => input.value);

  const updateGroupCounts = () => {
    const groups = {
      states: 'states[]',
      ranks: 'rank_ids[]',
      airlines: 'airline_ids[]',
      bases: 'bases[]',
      pilots: 'pilot_ids[]',
    };

    Object.entries(groups).forEach(([key, name]) => {
      const badge = document.querySelector(`[data-count-for="${key}"]`);
      if (badge) badge.textContent = checkedValues(name).length;
    });
  };

  const refresh = () => {
    const allActive = !!form.querySelector('[name="all_active"]')?.checked;
    const states = checkedValues('states[]').map(Number);
    const ranks = checkedValues('rank_ids[]').map(Number);
    const airlines = checkedValues('airline_ids[]').map(Number);
    const bases = checkedValues('bases[]');
    const manual = new Set(checkedValues('pilot_ids[]').map(Number));
    const minHoursRaw = form.querySelector('[name="min_hours"]')?.value ?? '';
    const maxHoursRaw = form.querySelector('[name="max_hours"]')?.value ?? '';

    const selected = pilots.filter(p => {
      if (!p.email) return false;
      if (manual.has(p.id)) return true;
      if (allActive && ![1,3].includes(p.state)) return false;
      if (!allActive && states.length && !states.includes(p.state)) return false;
      if (ranks.length && !ranks.includes(p.rank_id)) return false;
      if (airlines.length && !airlines.includes(p.airline_id)) return false;
      if (bases.length && !bases.includes(p.base)) return false;
      if (minHoursRaw !== '' && p.hours < Number(minHoursRaw)) return false;
      if (maxHoursRaw !== '' && p.hours > Number(maxHoursRaw)) return false;

      const hasCriteria =
        allActive ||
        states.length ||
        ranks.length ||
        airlines.length ||
        bases.length ||
        minHoursRaw !== '' ||
        maxHoursRaw !== '';

      return hasCriteria;
    });

    const text = selected.length + ' destinataire' + (selected.length > 1 ? 's' : '');
    count.textContent = text;
    if (inlineCount) inlineCount.textContent = text;
    updateGroupCounts();
  };

  form.querySelectorAll('[data-crm-filter]').forEach(node => {
    node.addEventListener(node.type === 'number' ? 'input' : 'change', refresh);
  });

  document.querySelectorAll('[data-check-all]').forEach(button => {
    button.addEventListener('click', () => {
      const group = document.querySelector(`[data-checkbox-group="${button.dataset.checkAll}"]`);
      if (!group) return;
      group.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        if (!cb.closest('[hidden]')) cb.checked = true;
      });
      refresh();
    });
  });

  document.querySelectorAll('[data-uncheck-all]').forEach(button => {
    button.addEventListener('click', () => {
      const group = document.querySelector(`[data-checkbox-group="${button.dataset.uncheckAll}"]`);
      if (!group) return;
      group.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
      refresh();
    });
  });

  document.querySelector('[data-check-visible-pilots]')?.addEventListener('click', () => {
    document.querySelectorAll('.crm-pilot-option:not([hidden]) input[type="checkbox"]').forEach(cb => cb.checked = true);
    refresh();
  });

  search?.addEventListener('input', () => {
    const term = search.value.trim().toLocaleLowerCase('fr-FR');
    document.querySelectorAll('.crm-pilot-option').forEach(row => {
      const label = (row.dataset.pilotLabel || '').toLocaleLowerCase('fr-FR');
      row.hidden = term !== '' && !label.includes(term);
    });
  });

  reset?.addEventListener('click', () => {
    form.querySelectorAll('[data-crm-filter]').forEach(node => {
      if (node.type === 'checkbox') node.checked = false;
      if (node.type === 'number') node.value = '';
    });
    if (search) {
      search.value = '';
      document.querySelectorAll('.crm-pilot-option').forEach(row => row.hidden = false);
    }
    refresh();
  });

  refresh();
})();
</script>
@endpush
@endsection
