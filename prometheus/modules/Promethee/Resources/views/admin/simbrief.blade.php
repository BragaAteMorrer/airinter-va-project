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
        @if($errors->has('api_key'))
          <div class="alert alert-danger">{{ $errors->first('api_key') }}</div>
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

        <hr>
        <h5>Clé API compagnie</h5>
        <p class="text-muted">
          {{ $apiConfigured
              ? 'Une clé est déjà enregistrée. Laissez le champ vide pour la conserver, ou saisissez une nouvelle clé pour la remplacer.'
              : 'Saisissez la clé API SimBrief attribuée à Air Inter VA. Elle restera uniquement côté serveur.' }}
        </p>
        <form method="post" action="{{ route('admin.promethee.simbrief.settings') }}" autocomplete="off" style="margin-bottom:24px">
          @csrf
          <div class="form-group">
            <label for="simbrief-api-key">SimBrief Company API Key</label>
            <input
              id="simbrief-api-key"
              class="form-control"
              type="password"
              name="api_key"
              value=""
              maxlength="255"
              autocomplete="new-password"
              spellcheck="false"
              placeholder="{{ $apiConfigured ? '•••••••••••••••• · laisser vide pour conserver' : 'Coller la clé API SimBrief ici' }}"
            >
            <p class="help-block">La clé n’est jamais réaffichée, envoyée à Hermès, ni exposée dans le JavaScript.</p>
          </div>
          @if($apiConfigured)
            <div class="checkbox">
              <label>
                <input type="checkbox" name="clear_api_key" value="1">
                Supprimer la clé actuellement enregistrée
              </label>
            </div>
          @endif
          <button class="btn btn-primary" type="submit">
            {{ $apiConfigured ? 'Enregistrer / remplacer la clé' : 'Enregistrer la clé API' }}
          </button>
        </form>

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
          <strong>{{ $lastSync ? \Carbon\Carbon::parse($lastSync)->timezone('Europe/Paris')->format('d/m/Y H:i') : 'jamais' }}</strong>.
          La synchronisation hebdomadaire phpVMS reste active indépendamment de ce bouton.
        </p>

        <hr>
        <h5>Clé API compagnie</h5>
        <p class="text-muted">La valeur est chiffrée côté serveur et n'est jamais réaffichée ni envoyée à Hermès.</p>

        <form method="post" action="{{ route('admin.promethee.simbrief.api-key.save') }}" style="margin-bottom:12px">
          @csrf
          <div class="form-group">
            <label for="simbrief-api-key">SimBrief Company API Key</label>
            <input
              id="simbrief-api-key"
              class="form-control"
              type="password"
              name="api_key"
              value=""
              autocomplete="new-password"
              spellcheck="false"
              placeholder="{{ $apiConfigured ? 'Clé déjà configurée — saisir une nouvelle valeur pour la remplacer' : 'Collez la clé API compagnie SimBrief' }}"
              required
            >
          </div>
          <button class="btn btn-primary" type="submit">{{ $apiConfigured ? 'Remplacer la clé API' : 'Enregistrer la clé API' }}</button>
        </form>

        @if($apiConfigured)
        <form method="post" action="{{ route('admin.promethee.simbrief.api-key.delete') }}" style="display:inline-block;margin-right:8px" onsubmit="return confirm('Supprimer la clé API SimBrief de Prométhée ?');">
          @csrf
          @method('DELETE')
          <button class="btn btn-danger" type="submit">Supprimer la clé API</button>
        </form>
        @endif

        <form method="post" action="{{ route('admin.promethee.simbrief.sync') }}" style="display:inline-block">
          @csrf
          <button class="btn btn-info" type="submit">Synchroniser maintenant</button>
        </form>
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
