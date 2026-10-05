@extends('promethee::layout')
@section('title','Certifications simulateur réel')
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">FLIGHT STANDARDS · FORMATION</span>
    <h1>Certifications sur simulateurs réels</h1>
    <p>Enregistrez les sessions et qualifications vérifiées réalisées sur FFS, FTD ou FNPT.</p>
  </div>
  <div class="ops-clock">
    <span>Registre</span>
    <strong>{{ $certifications->total() }}</strong>
    <small>certification(s)</small>
  </div>
</div>

<section class="panel">
  <div class="panel-heading">
    <div>
      <span class="eyebrow">PORTÉE</span>
      <h2>Attestation Air Inter VA</h2>
    </div>
    <span class="tag">VÉRIFIÉ ADMIN</span>
  </div>
  <p class="muted">Ce registre documente une expérience ou une certification déclarée et contrôlée par Air Inter VA. Il ne constitue pas une licence, une qualification de type ou une attestation réglementaire EASA/ATO.</p>
</section>

<div class="two-columns">
  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">NOUVELLE ENTRÉE</span><h2>Ajouter une certification</h2></div></div>
    <form method="post" action="{{ route('admin.promethee.real-simulator-certifications.store') }}" class="form-grid">
      @csrf
      <label>Pilote
        <select name="user_id" required>
          <option value="">Sélectionner…</option>
          @foreach($pilots as $pilot)
            <option value="{{ $pilot->id }}" @selected((string) old('user_id') === (string) $pilot->id)>{{ $pilot->pilot_id ?: '—' }} · {{ $pilot->name }}</option>
          @endforeach
        </select>
      </label>
      <label>Intitulé
        <input name="certificate_name" value="{{ old('certificate_name') }}" maxlength="160" required placeholder="Ex. A320 · séance FFS">
      </label>
      <label>Type avion
        <input name="aircraft_type" value="{{ old('aircraft_type') }}" maxlength="32" placeholder="A320">
      </label>
      <label>Niveau du dispositif
        <select name="simulator_level" required>
          @foreach($levels as $value => $label)
            <option value="{{ $value }}" @selected(old('simulator_level','FFS_D') === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </label>
      <label>Simulateur / dispositif
        <input name="device_name" value="{{ old('device_name') }}" maxlength="160" required placeholder="Ex. Airbus A320 FFS">
      </label>
      <label>Centre / organisme
        <input name="organisation" value="{{ old('organisation') }}" maxlength="160" required>
      </label>
      <label>Lieu
        <input name="location" value="{{ old('location') }}" maxlength="160" placeholder="Ville, pays">
      </label>
      <label>Date de réalisation
        <input type="date" name="completed_on" value="{{ old('completed_on') }}" required>
      </label>
      <label>Valide jusqu’au
        <input type="date" name="valid_until" value="{{ old('valid_until') }}">
      </label>
      <label>Référence
        <input name="reference" value="{{ old('reference') }}" maxlength="120" placeholder="N° certificat / dossier">
      </label>
      <label>Justificatif (URL privée)
        <input type="url" name="evidence_url" value="{{ old('evidence_url') }}" maxlength="1000" placeholder="https://…">
      </label>
      <label>Statut
        <select name="status" required>
          <option value="verified" @selected(old('status','verified') === 'verified')>Vérifiée</option>
          <option value="revoked" @selected(old('status') === 'revoked')>Révoquée</option>
        </select>
      </label>
      <label class="full">Notes internes
        <textarea name="notes" rows="4" maxlength="4000">{{ old('notes') }}</textarea>
      </label>
      <div class="full"><button class="button" type="submit">Enregistrer la certification</button></div>
    </form>
  </section>

  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">RECHERCHE</span><h2>Registre</h2></div></div>
    <form method="get" class="form-grid">
      <label>Recherche
        <input name="q" value="{{ request('q') }}" placeholder="Pilote, avion, centre, référence…">
      </label>
      <label>Statut
        <select name="status">
          <option value="">Tous</option>
          <option value="verified" @selected(request('status') === 'verified')>Vérifiées</option>
          <option value="revoked" @selected(request('status') === 'revoked')>Révoquées</option>
        </select>
      </label>
      <div class="full"><button class="button outline" type="submit">Filtrer</button></div>
    </form>
  </section>
</div>

<section class="panel">
  <div class="panel-heading"><div><span class="eyebrow">HISTORIQUE</span><h2>Certifications enregistrées</h2></div><span class="tag">{{ $certifications->total() }}</span></div>
  <div class="route-list">
    @forelse($certifications as $certification)
      <details class="panel">
        <summary>
          <strong>{{ $certification->pilot?->pilot_id ?: '—' }} · {{ $certification->pilot?->name ?: 'Pilote supprimé' }}</strong>
          · {{ $certification->certificate_name }}
          · {{ $levels[$certification->simulator_level] ?? $certification->simulator_level }}
          @if($certification->aircraft_type) · {{ $certification->aircraft_type }} @endif
          · {{ optional($certification->completed_on)->format('d/m/Y') }}
          @if($certification->status === 'revoked') · RÉVOQUÉE @elseif($certification->isExpired()) · ÉCHUE @else · VÉRIFIÉE @endif
        </summary>
        <form method="post" action="{{ route('admin.promethee.real-simulator-certifications.update', $certification) }}" class="form-grid">
          @csrf
          @method('PUT')
          <label>Pilote
            <select name="user_id" required>
              @foreach($pilots as $pilot)
                <option value="{{ $pilot->id }}" @selected((int) $certification->user_id === (int) $pilot->id)>{{ $pilot->pilot_id ?: '—' }} · {{ $pilot->name }}</option>
              @endforeach
            </select>
          </label>
          <label>Intitulé <input name="certificate_name" value="{{ $certification->certificate_name }}" maxlength="160" required></label>
          <label>Type avion <input name="aircraft_type" value="{{ $certification->aircraft_type }}" maxlength="32"></label>
          <label>Niveau
            <select name="simulator_level" required>
              @foreach($levels as $value => $label)
                <option value="{{ $value }}" @selected($certification->simulator_level === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </label>
          <label>Dispositif <input name="device_name" value="{{ $certification->device_name }}" maxlength="160" required></label>
          <label>Centre / organisme <input name="organisation" value="{{ $certification->organisation }}" maxlength="160" required></label>
          <label>Lieu <input name="location" value="{{ $certification->location }}" maxlength="160"></label>
          <label>Date <input type="date" name="completed_on" value="{{ optional($certification->completed_on)->format('Y-m-d') }}" required></label>
          <label>Valide jusqu’au <input type="date" name="valid_until" value="{{ optional($certification->valid_until)->format('Y-m-d') }}"></label>
          <label>Référence <input name="reference" value="{{ $certification->reference }}" maxlength="120"></label>
          <label>Justificatif <input type="url" name="evidence_url" value="{{ $certification->evidence_url }}" maxlength="1000"></label>
          <label>Statut
            <select name="status" required>
              <option value="verified" @selected($certification->status === 'verified')>Vérifiée</option>
              <option value="revoked" @selected($certification->status === 'revoked')>Révoquée</option>
            </select>
          </label>
          <label class="full">Notes internes <textarea name="notes" rows="3" maxlength="4000">{{ $certification->notes }}</textarea></label>
          <div class="full">
            <button class="button" type="submit">Mettre à jour</button>
            @if($certification->evidence_url)<a class="button outline" href="{{ $certification->evidence_url }}" target="_blank" rel="noopener noreferrer">Ouvrir le justificatif</a>@endif
          </div>
        </form>
        <p class="muted">Vérifiée par {{ $certification->verifier?->name ?? '—' }} · {{ $certification->verified_at?->setTimezone('Europe/Paris')->format('d/m/Y H:i') ?? '—' }}</p>
      </details>
    @empty
      <p class="empty">Aucune certification enregistrée.</p>
    @endforelse
  </div>
  {{ $certifications->links() }}
</section>
@endsection
