@php
  $connections = $onlineNetworks['connections'] ?? [];
@endphp
<div class="card mt-4">
  <div class="card-body p-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
      <div>
        <span class="text-uppercase small text-muted fw-bold">Réseaux de vol</span>
        <h4 class="mb-1">IVAO & VATSIM</h4>
        <p class="text-muted mb-0">Liez vos comptes officiels. Prométhée vérifie ensuite votre présence réseau automatiquement.</p>
      </div>
      <span class="badge {{ ($onlineNetworks['online'] ?? false) ? 'bg-success' : 'bg-secondary' }}">
        {{ ($onlineNetworks['online'] ?? false) ? 'EN LIGNE' : 'HORS LIGNE' }}
      </span>
    </div>

    <div class="row g-3">
      @foreach(['vatsim' => ['label' => 'VATSIM', 'color' => '#29B473'], 'ivao' => ['label' => 'IVAO', 'color' => '#0d2c99']] as $provider => $meta)
        @php
          $connection = $connections[$provider] ?? null;
          $linkedId = $provider === 'vatsim' ? $user->vatsim_id : $user->ivao_id;
        @endphp
        <div class="col-md-6">
          <div class="border rounded p-3 h-100" style="border-left:4px solid {{ $meta['color'] }} !important">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <strong>{{ $meta['label'] }}</strong>
              @if($connection && ($connection['online'] ?? false))
                <span class="badge bg-success">ONLINE</span>
              @elseif($linkedId)
                <span class="badge bg-light text-dark">LIÉ</span>
              @else
                <span class="badge bg-secondary">NON LIÉ</span>
              @endif
            </div>

            @if($linkedId)
              <p class="mb-2"><small>ID réseau</small><br><strong>{{ $linkedId }}</strong></p>
              @if($connection && ($connection['online'] ?? false))
                <p class="mb-3">
                  <small>Connexion détectée</small><br>
                  <strong>{{ $connection['callsign'] ?? '—' }}</strong>
                  @if(data_get($connection, 'flight_plan.departure') || data_get($connection, 'flight_plan.arrival'))
                    · {{ data_get($connection, 'flight_plan.departure', '—') }} → {{ data_get($connection, 'flight_plan.arrival', '—') }}
                  @endif
                </p>
              @else
                <p class="text-muted small mb-3">Compte vérifié, aucune connexion pilote détectée actuellement.</p>
              @endif

              @if(config('services.'.$provider.'.enabled'))
                <a href="{{ route('oauth.logout', ['provider' => $provider]) }}" class="btn btn-outline-secondary btn-sm"
                   onclick="return confirm('Délier votre compte {{ $meta['label'] }} ?')">Délier {{ $meta['label'] }}</a>
              @endif
            @else
              <p class="text-muted small mb-3">Aucun compte {{ $meta['label'] }} vérifié sur votre dossier pilote.</p>
              @if(config('services.'.$provider.'.enabled'))
                <a href="{{ route('oauth.redirect', ['provider' => $provider]) }}" class="btn btn-sm text-white"
                   style="background:{{ $meta['color'] }}">Lier mon compte {{ $meta['label'] }}</a>
              @else
                <span class="text-muted small">OAuth {{ $meta['label'] }} non configuré côté serveur.</span>
              @endif
            @endif
          </div>
        </div>
      @endforeach
    </div>

    <p class="small text-muted mt-3 mb-0">Les identifiants réseau ne sont pas modifiables manuellement : ils proviennent uniquement de l’authentification officielle IVAO/VATSIM.</p>
  </div>
</div>
