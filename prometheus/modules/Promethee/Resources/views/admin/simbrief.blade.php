@extends('promethee::layout')

@section('title', 'Prométhée · SimBrief')

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="header">
        <h4 class="title">Intégration SimBrief</h4>
        <p class="category">État de l’API compagnie, imports OFP et données de planification synchronisées.</p>
      </div>
      <div class="content">
        @if(session('success'))
          <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->has('simbrief'))
          <div class="alert alert-danger">{{ $errors->first('simbrief') }}</div>
        @endif

        <div class="row">
          <div class="col-sm-4">
            <div class="well">
              <small>API COMPAGNIE</small>
              <h4 style="margin:8px 0">{{ $apiConfigured ? 'CONFIGURÉE' : 'À CONFIGURER' }}</h4>
              <span class="text-muted">{{ $apiConfigured ? 'Clé présente côté serveur' : 'Mode API indisponible dans Hermès' }}</span>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="well">
              <small>AVIONS / AIRFRAMES</small>
              <h4 style="margin:8px 0">{{ number_format($aircraftCount, 0, ',', ' ') }} / {{ number_format($airframeCount, 0, ',', ' ') }}</h4>
              <span class="text-muted">Catalogue SimBrief local</span>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="well">
              <small>LAYOUTS OFP</small>
              <h4 style="margin:8px 0">{{ number_format($layoutCount, 0, ',', ' ') }}</h4>
              <span class="text-muted">Formats disponibles</span>
            </div>
          </div>
        </div>

        <h5>Fonctions actives</h5>
        <ul>
          <li>Génération OFP via l’API officielle avec signature calculée uniquement côté Prométhée.</li>
          <li>Retour SimBrief corrélé par une session éphémère et import du véritable <code>ofp_id</code>.</li>
          <li>Redirection Dispatch préremplie pour personnaliser le vol sur SimBrief.</li>
          <li>Import explicite du dernier OFP d’un compte Navigraph/SimBrief, sans polling.</li>
          <li>Synchronisation périodique des avions, airframes et layouts via le cron phpVMS.</li>
        </ul>

        <p class="text-muted">
          Dernière synchronisation manuelle :
          <strong>{{ $lastSync ? CarbonCarbon::parse($lastSync)->timezone('Europe/Paris')->format('d/m/Y H:i') : 'jamais' }}</strong>.
          La synchronisation hebdomadaire phpVMS reste active indépendamment de ce bouton.
        </p>

        <form method="post" action="{{ route('admin.promethee.simbrief.sync') }}" style="display:inline-block;margin-right:8px">
          @csrf
          <button class="btn btn-info" type="submit">Synchroniser maintenant</button>
        </form>
        <a class="btn btn-default" href="{{ url('/admin/settings') }}">Gérer la clé API</a>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card">
      <div class="header">
        <h4 class="title">Sécurité de la clé</h4>
        <p class="category">La clé n’est jamais envoyée à Hermès.</p>
      </div>
      <div class="content">
        <p>Le réglage <strong>SimBrief Company API Key</strong> est write-only dans l’administration : une valeur déjà enregistrée n’est pas réaffichée.</p>
        <p>Hermès reçoit seulement l’état <code>company_api_available</code>. La signature <code>apicode</code> est calculée sur le serveur pour chaque génération.</p>
        <p class="text-muted">Ne placez jamais la clé dans le dépôt, les fichiers JavaScript, les diagnostics ou le stockage local du client.</p>
      </div>
    </div>
  </div>
</div>
@endsection
