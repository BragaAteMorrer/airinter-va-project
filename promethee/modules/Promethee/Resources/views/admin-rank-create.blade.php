@extends('promethee::layout')
@section('title','Créer un grade')
@push('styles')
<link rel="stylesheet" href="{{ asset('promethee-assets/promethee-admin-workspaces.css') }}?v={{ filemtime(public_path('promethee-assets/promethee-admin-workspaces.css')) }}">
@endpush
@section('content')
<div class="admin-workspace-page rank-editor">
  <div class="ops-header compact">
    <div><span class="eyebrow">PROMÉTHÉE · CARRIÈRES PILOTES</span><h1>Nouveau grade pilote.</h1><p>Ajoutez un niveau à la grille de progression et définissez immédiatement ses droits opérationnels.</p></div>
    <a class="button outline" href="{{ route('admin.promethee.ranks') }}">← Grille des grades</a>
  </div>

  <form method="post" action="{{ route('admin.promethee.ranks.store') }}" class="admin-form-sections panel">
    @csrf
    <fieldset>
      <legend>Identité & progression</legend>
      <div class="form-grid">
        <label>Nom du grade<input name="name" required value="{{ old('name') }}" placeholder="Ex. Captain"><small>Nom visible dans Prométhée et phpVMS.</small></label>
        <label>Heures requises<input type="number" min="0" name="hours" required value="{{ old('hours',0) }}"><small>Seuil minimal dans la grille de carrière.</small></label>
        <label>Taux ACARS<input type="number" step="0.01" min="0" name="acars_base_pay_rate" value="{{ old('acars_base_pay_rate') }}"></label>
        <label>Taux manuel<input type="number" step="0.01" min="0" name="manual_base_pay_rate" value="{{ old('manual_base_pay_rate') }}"></label>
      </div>
    </fieldset>

    <fieldset>
      <legend>Automatisation</legend>
      <div class="admin-toggle-grid">
        <label><input type="checkbox" name="auto_promote" value="1" @checked(old('auto_promote'))><span><strong>Promotion automatique</strong><small>Le système pourra promouvoir les pilotes éligibles.</small></span></label>
        <label><input type="checkbox" name="auto_approve_acars" value="1" @checked(old('auto_approve_acars'))><span><strong>PIREP ACARS auto</strong><small>Approbation automatique selon la politique phpVMS.</small></span></label>
        <label><input type="checkbox" name="auto_approve_manual" value="1" @checked(old('auto_approve_manual'))><span><strong>PIREP manuel auto</strong><small>À activer seulement si votre procédure l’autorise.</small></span></label>
      </div>
    </fieldset>

    <fieldset>
      <legend>Couverture flotte</legend>
      <p class="admin-field-help">Vous pourrez modifier cette liste à tout moment. Une liste vide signifie qu’aucune sous-flotte n’est explicitement liée à ce grade.</p>
      <select class="admin-multiselect" name="subfleet_ids[]" multiple size="16">
        @foreach($subfleets as $subfleet)<option value="{{ $subfleet->id }}" @selected(in_array($subfleet->id,old('subfleet_ids',[])))>{{ $subfleet->airline?->icao ?: '---' }} · {{ $subfleet->name }}</option>@endforeach
      </select>
    </fieldset>

    <div class="admin-form-footer"><button class="button">Créer le grade</button><a class="button outline" href="{{ route('admin.promethee.ranks') }}">Annuler</a></div>
  </form>
</div>
@endsection
