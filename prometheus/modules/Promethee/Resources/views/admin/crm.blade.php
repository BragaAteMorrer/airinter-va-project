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
        <h3>Ciblage</h3>
        <label class="crm-check"><input type="checkbox" name="all_active" value="1" data-crm-filter> Tous les pilotes actifs + en congé</label>

        <label>Statut
          <select name="states[]" multiple size="5" data-crm-filter>
            <option value="1">Actif</option>
            <option value="3">En congé</option>
            <option value="0">En attente</option>
            <option value="2">Refusé</option>
            <option value="4">Suspendu</option>
          </select>
        </label>

        <label>Grade
          <select name="rank_ids[]" multiple size="6" data-crm-filter>
            @foreach($ranks as $rank)<option value="{{ $rank->id }}">{{ $rank->name }}</option>@endforeach
          </select>
        </label>

        <label>Compagnie
          <select name="airline_ids[]" multiple size="5" data-crm-filter>
            @foreach($airlines as $airline)<option value="{{ $airline->id }}">{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach
          </select>
        </label>

        <label>Base
          <select name="bases[]" multiple size="5" data-crm-filter>
            @foreach($bases as $base)<option value="{{ $base }}">{{ $base }}</option>@endforeach
          </select>
        </label>

        <div class="two-columns compact">
          <label>Heures min.<input name="min_hours" type="number" min="0" step="1" data-crm-filter></label>
          <label>Heures max.<input name="max_hours" type="number" min="0" step="1" data-crm-filter></label>
        </div>

        <label>Pilotes précis
          <select name="pilot_ids[]" multiple size="10" data-crm-filter>
            @foreach($pilots as $pilot)
            <option value="{{ $pilot->id }}">{{ $pilot->pilot_id }} · {{ $pilot->name }} · {{ $pilot->rank?->name ?? 'Sans grade' }}</option>
            @endforeach
          </select>
          <small>Ces pilotes sont ajoutés à la sélection, même s’ils ne correspondent pas aux autres filtres.</small>
        </label>

        <button type="submit" class="primary" @disabled($senders->where('active',true)->isEmpty()) onclick="return confirm('Envoyer cette campagne aux destinataires sélectionnés ?');">Envoyer la campagne</button>
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
<script>
(() => {
  const pilots = @json($pilots->map(fn($pilot) => [
    'id' => (int)$pilot->id,
    'state' => (int)$pilot->state,
    'rank_id' => $pilot->rank_id ? (int)$pilot->rank_id : null,
    'airline_id' => $pilot->airline_id ? (int)$pilot->airline_id : null,
    'base' => $pilot->home_airport_id,
    'hours' => round(($pilot->flight_time ?? 0) / 60, 1),
    'email' => $pilot->email,
  ])->values());
  const form = document.getElementById('crmCampaignForm');
  const count = document.getElementById('crmAudienceCount');
  if (!form || !count) return;

  const vals = name => [...form.querySelectorAll(`[name="${name}"] option:checked`)].map(o => o.value);
  const refresh = () => {
    const allActive = form.querySelector('[name="all_active"]').checked;
    const states = vals('states[]').map(Number);
    const ranks = vals('rank_ids[]').map(Number);
    const airlines = vals('airline_ids[]').map(Number);
    const bases = vals('bases[]');
    const manual = new Set(vals('pilot_ids[]').map(Number));
    const minHours = form.querySelector('[name="min_hours"]').value;
    const maxHours = form.querySelector('[name="max_hours"]').value;

    const selected = pilots.filter(p => {
      if (!p.email) return false;
      if (manual.has(p.id)) return true;
      if (allActive && ![1,3].includes(p.state)) return false;
      if (!allActive && states.length && !states.includes(p.state)) return false;
      if (ranks.length && !ranks.includes(p.rank_id)) return false;
      if (airlines.length && !airlines.includes(p.airline_id)) return false;
      if (bases.length && !bases.includes(p.base)) return false;
      if (minHours !== '' && p.hours < Number(minHours)) return false;
      if (maxHours !== '' && p.hours > Number(maxHours)) return false;

      const hasCriteria = allActive || states.length || ranks.length || airlines.length || bases.length || minHours !== '' || maxHours !== '';
      return hasCriteria;
    });

    count.textContent = selected.length + ' destinataire' + (selected.length > 1 ? 's' : '');
  };

  form.querySelectorAll('[data-crm-filter]').forEach(node => {
    node.addEventListener(node.tagName === 'INPUT' && node.type === 'number' ? 'input' : 'change', refresh);
  });
  refresh();
})();
</script>
@endpush
@endsection
