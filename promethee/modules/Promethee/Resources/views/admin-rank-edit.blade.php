@extends('promethee::layout')
@section('title','Configurer un grade')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page rank-editor">
  <div class="ops-header compact">
    <div><span class="eyebrow">PROMÉTHÉE · CARRIÈRES PILOTES</span><h1>{{ $rank->name }}</h1><p>Définissez le seuil de carrière, les règles d’approbation et la flotte accessible à ce grade.</p></div>
    <a class="button outline" href="{{ route('admin.promethee.ranks') }}">← Grille des grades</a>
  </div>

  <section class="admin-kpi-grid">
    <article><span>Seuil</span><strong>{{ number_format((float)$rank->hours,0,',',' ') }} h</strong><small>expérience minimale</small></article>
    <article><span>Pilotes</span><strong>{{ $rank->users()->count() }}</strong><small>actuellement affectés</small></article>
    <article><span>Sous-flottes</span><strong>{{ $rank->subfleets->count() }}</strong><small>autorisées</small></article>
    <article><span>Promotion</span><strong>{{ $rank->auto_promote ? 'Auto' : 'Manuelle' }}</strong><small>politique actuelle</small></article>
  </section>

  <form method="post" action="{{ route('admin.promethee.ranks.update',$rank) }}" class="admin-form-sections panel">
    @csrf @method('PUT')
    <fieldset>
      <legend>Identité & progression</legend>
      <div class="form-grid">
        <label>Nom du grade<input name="name" required value="{{ old('name',$rank->name) }}"></label>
        <label>Heures requises<input type="number" min="0" name="hours" required value="{{ old('hours',$rank->hours) }}"><small>Seuil utilisé par la progression automatique.</small></label>
        <label>Taux ACARS<input type="number" step="0.01" min="0" name="acars_base_pay_rate" value="{{ old('acars_base_pay_rate',$rank->acars_base_pay_rate) }}"></label>
        <label>Taux manuel<input type="number" step="0.01" min="0" name="manual_base_pay_rate" value="{{ old('manual_base_pay_rate',$rank->manual_base_pay_rate) }}"></label>
      </div>
    </fieldset>

    <fieldset>
      <legend>Automatisation</legend>
      <div class="admin-toggle-grid">
        <label><input type="checkbox" name="auto_promote" value="1" @checked(old('auto_promote',$rank->auto_promote))><span><strong>Promotion automatique</strong><small>Promouvoir quand le seuil d’heures est atteint.</small></span></label>
        <label><input type="checkbox" name="auto_approve_acars" value="1" @checked(old('auto_approve_acars',$rank->auto_approve_acars))><span><strong>PIREP ACARS auto</strong><small>Autoriser l’approbation automatique des rapports ACARS.</small></span></label>
        <label><input type="checkbox" name="auto_approve_manual" value="1" @checked(old('auto_approve_manual',$rank->auto_approve_manual))><span><strong>PIREP manuel auto</strong><small>Autoriser l’approbation automatique des rapports manuels.</small></span></label>
      </div>
    </fieldset>

    <fieldset>
      <legend>Couverture flotte</legend>
      <p class="admin-field-help">Sélectionnez les sous-flottes pilotables avec ce grade. Utilisez Ctrl/Cmd pour une sélection multiple.</p>
      <select class="admin-multiselect" name="subfleet_ids[]" multiple size="16">
        @foreach($subfleets as $subfleet)
          <option value="{{ $subfleet->id }}" @selected(in_array($subfleet->id,old('subfleet_ids',$rank->subfleets->pluck('id')->all())))>{{ $subfleet->airline?->icao ?: '---' }} · {{ $subfleet->name }}</option>
        @endforeach
      </select>
    </fieldset>

    <div class="admin-form-footer"><button class="button">Enregistrer le grade</button><a class="button outline" href="{{ route('admin.promethee.ranks') }}">Annuler</a></div>
  </form>
</div>
@endsection
