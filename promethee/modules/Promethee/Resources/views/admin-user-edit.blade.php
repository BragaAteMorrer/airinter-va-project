@extends('promethee::layout')
@section('title','Fiche pilote')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
@php
  $stateLabels=$states;
  $totalMinutes=(int)$pilot->flight_time+(int)$pilot->transfer_time;
@endphp
<div class="admin-workspace-page pilot-record">
  <div class="ops-header compact">
    <div>
      <span class="eyebrow">PROMÉTHÉE · DOSSIER PILOTE</span>
      <h1>{{ $pilot->ident }} · {{ $pilot->name }}</h1>
      <p>{{ $pilot->airline?->icao ?: '—' }} · {{ $pilot->rank?->name ?: 'Sans grade' }} · {{ $pilot->home_airport_id ?: 'Sans base' }}</p>
    </div>
    <div class="admin-workspace-form-action">
      <a class="button outline" href="{{ route('admin.promethee.users') }}">← Effectif</a>
      <a class="button outline" href="{{ rtrim((string) config('services.airinter_id.base_url'), '/') }}/account">Ouvrir Argos</a>
    </div>
  </div>

  <section class="admin-kpi-grid">
    <article><span>État</span><strong>{{ $stateLabels[$pilot->state] ?? 'Inconnu' }}</strong><small>{{ $pilot->email_verified_at ? 'e-mail vérifié' : 'e-mail non vérifié' }}</small></article>
    <article><span>Temps total</span><strong>{{ number_format($totalMinutes/60,1,',',' ') }} h</strong><small>{{ number_format((int)$pilot->transfer_time/60,1,',',' ') }} h transférées</small></article>
    <article><span>Vols</span><strong>{{ $pilot->flights ?: $pilot->pireps()->count() }}</strong><small>@if($pilot->last_pirep?->submitted_at) dernier {{ $pilot->last_pirep->submitted_at->diffForHumans() }} @else aucun PIREP @endif</small></article>
    <article><span>Qualifications</span><strong>{{ $pilot->typeratings->count() }}</strong><small>{{ $pilot->awards->count() }} badge(s)</small></article>
    <article><span>Dernière connexion</span><strong>{{ $pilot->lastlogin_at?->format('d/m/Y') ?: '—' }}</strong><small>{{ $pilot->lastlogin_at?->format('H:i') ?: 'jamais enregistrée' }}</small></article>
    @if($hasArgosSubject)<article><span>Argos</span><strong>{{ $pilot->argos_subject ? 'Relié' : 'Non relié' }}</strong><small>identité centralisée</small></article>@endif
  </section>

  <div class="pilot-record-grid">
    <section class="panel pilot-record-main">
      <div class="panel-heading"><div><span class="eyebrow">AFFECTATION OPÉRATIONNELLE</span><h2>Paramètres du pilote</h2><p>Ces champs relèvent de Prométhée et de l’exploitation. Le nom, l’e-mail et les identifiants réseau restent sous autorité Argos.</p></div></div>
      <form method="post" action="{{ route('admin.promethee.users.update',$pilot) }}" class="admin-form-sections">
        @csrf @method('PUT')
        <fieldset>
          <legend>Carrière & statut</legend>
          <div class="form-grid">
            <label>ID pilote<input type="number" name="pilot_id" required value="{{ old('pilot_id',$pilot->pilot_id) }}"><small>Identifiant opérationnel phpVMS.</small></label>
            <label>Callsign<input maxlength="4" name="callsign" value="{{ old('callsign',$pilot->callsign) }}"><small>Suffixe court utilisé côté opérations.</small></label>
            <label>État<select name="state" required>@foreach($states as $value=>$label)<option value="{{ $value }}" @selected((string)old('state',$pilot->state)===(string)$value)>{{ $label }}</option>@endforeach</select><small>Un changement d’état déclenche le workflow phpVMS associé.</small></label>
            <label>Grade<select name="rank_id"><option value="">— Aucun —</option>@foreach($ranks as $rank)<option value="{{ $rank->id }}" @selected((string)old('rank_id',$pilot->rank_id)===(string)$rank->id)>{{ $rank->name }} · {{ $rank->hours }} h</option>@endforeach</select></label>
            <label>Heures transférées<input type="number" step="0.1" min="0" name="transfer_hours" value="{{ old('transfer_hours',round(((int)$pilot->transfer_time)/60,1)) }}"><small>Expérience reconnue hors vols enregistrés.</small></label>
          </div>
        </fieldset>

        <fieldset>
          <legend>Affectation compagnie</legend>
          <div class="form-grid">
            <label>Compagnie<select name="airline_id" required>@foreach($airlines as $airline)<option value="{{ $airline->id }}" @selected((string)old('airline_id',$pilot->airline_id)===(string)$airline->id)>{{ $airline->icao }} · {{ $airline->name }}</option>@endforeach</select></label>
            <label>Base<select name="home_airport_id"><option value="">— Sans base —</option>@foreach($airports as $airport)<option value="{{ $airport->id }}" @selected((string)old('home_airport_id',$pilot->home_airport_id)===(string)$airport->id)>{{ $airport->icao ?: $airport->iata ?: $airport->id }} · {{ $airport->name }}</option>@endforeach</select></label>
            <label>Position actuelle<select name="curr_airport_id"><option value="">— Position inconnue —</option>@foreach($airports as $airport)<option value="{{ $airport->id }}" @selected((string)old('curr_airport_id',$pilot->curr_airport_id)===(string)$airport->id)>{{ $airport->icao ?: $airport->iata ?: $airport->id }} · {{ $airport->name }}</option>@endforeach</select><small>Position opérationnelle utilisée pour les vols disponibles et les déplacements du pilote.</small></label>
            <label>Pays<select name="country"><option value="">—</option>@foreach($countries as $code=>$name)<option value="{{ $code }}" @selected(strtolower((string)old('country',$pilot->country))===strtolower((string)$code))>{{ strtoupper($code) }} · {{ $name }}</option>@endforeach</select></label>
          </div>
        </fieldset>

        <fieldset>
          <legend>Notes internes</legend>
          <label class="admin-textarea-field"><textarea name="notes" rows="6" placeholder="Informations internes staff, suivi, restrictions, contexte opérationnel…">{{ old('notes',$pilot->notes) }}</textarea><small>Visible uniquement par le staff habilité. Ne pas y stocker de secrets ou de données inutiles.</small></label>
        </fieldset>

        <div class="admin-form-footer"><button class="button">Enregistrer les données opérationnelles</button><a class="button outline" href="{{ route('admin.promethee.users') }}">Annuler</a></div>
      </form>
    </section>

    <aside class="pilot-record-side">
      <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">IDENTITÉ · ARGOS</span><h2>Lecture seule</h2></div></div>
        <dl class="admin-definition-list">
          <div><dt>Nom</dt><dd>{{ $pilot->name }}</dd></div>
          <div><dt>E-mail</dt><dd>{{ $pilot->email }}</dd></div>
          <div><dt>VATSIM</dt><dd>{{ $pilot->vatsim_id ?: '—' }}</dd></div>
          <div><dt>IVAO</dt><dd>{{ $pilot->ivao_id ?: '—' }}</dd></div>
          <div><dt>Fuseau</dt><dd>{{ $pilot->timezone ?: '—' }}</dd></div>
          <div><dt>Compte créé</dt><dd>{{ $pilot->created_at?->format('d/m/Y H:i') ?: '—' }}</dd></div>
        </dl>
      </section>

      <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">POSITION & ACTIVITÉ</span><h2>Situation actuelle</h2></div></div>
        <dl class="admin-definition-list">
          <div><dt>Base</dt><dd>{{ $pilot->home_airport?->icao ?: $pilot->home_airport_id ?: '—' }}</dd></div>
          <div><dt>Position</dt><dd>{{ $pilot->current_airport?->icao ?: $pilot->curr_airport_id ?: '—' }}</dd></div>
          <div><dt>Dernier PIREP</dt><dd>@if($pilot->last_pirep)<a href="{{ route('promethee.pireps.show',$pilot->last_pirep->id) }}">{{ $pilot->last_pirep->ident }}</a>@else — @endif</dd></div>
          <div><dt>Opt-in communications</dt><dd>{{ $pilot->opt_in ? 'Oui' : 'Non' }}</dd></div>
        </dl>
      </section>

      <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">QUALIFICATIONS</span><h2>Type ratings</h2></div></div>
        <div class="admin-chip-list">@forelse($pilot->typeratings as $rating)<span>{{ $rating->name }}</span>@empty<em>Aucune qualification enregistrée</em>@endforelse</div>
      </section>
    </aside>
  </div>
</div>
@endsection
