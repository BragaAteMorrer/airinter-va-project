@extends('admin.app')

@section('title', 'Prométhée · Identité visuelle')

@section('content')
  <div class="card">
    <div class="header">
      <h4 class="title">Logo Air Inter</h4>
      <p class="category">Choisissez le logo utilisé sur le portail Prométhée. Les fichiers fournis sont tous détourés et conservés localement.</p>
    </div>
    <div class="content">
      <form method="post" action="{{ route('admin.identity.branding.save') }}">
        @csrf
        <div class="row">
          @foreach($logos as $key => $logo)
            <div class="col-md-6" style="margin-bottom: 24px">
              <label style="display:block; cursor:pointer; min-height:205px; padding:16px; border:2px solid {{ $branding['key'] === $key ? '#2f80ed' : '#e3e3e3' }}; border-radius:4px">
                <input type="radio" name="logo" value="{{ $key }}" @checked($branding['key'] === $key)>
                <strong>{{ $logo['label'] }}</strong>
                <span class="text-muted"> — {{ $logo['description'] }}</span>
                <span style="display:flex; align-items:center; justify-content:center; height:135px; margin-top:12px; background:linear-gradient(135deg,#f8fafc,#eef2f6)">
                  <img src="{{ asset('promethee-assets/logos/'.$logo['file']) }}" alt="Aperçu : {{ $logo['label'] }}" style="max-width:90%; max-height:115px; object-fit:contain">
                </span>
              </label>
            </div>
          @endforeach
        </div>
        <button class="btn btn-info" type="submit">Enregistrer le logo</button>
        <p class="text-muted" style="margin-top:16px">Thème Minitel : le logo sélectionné est automatiquement rendu en phosphore vert, contrasté et pixellisé pour rester cohérent avec l’écran.</p>
      </form>
    </div>
  </div>
@endsection
