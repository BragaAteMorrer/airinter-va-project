@extends('promethee::layout')

@section('title', 'Prométhée · Identité visuelle')

@section('content')
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="header"><h4 class="title">Identité visuelle</h4><p class="category">Sélectionnez un logo fourni ou importez le vôtre. Le choix est appliqué au portail pilote et à l’administration.</p></div>
        <div class="content">
          <div class="well" style="display:flex;align-items:center;gap:20px;margin-bottom:28px">
            <img src="{{ $branding['url'] }}" alt="{{ $branding['label'] }}" style="width:120px;height:80px;object-fit:contain">
            <div><strong>Logo actif : {{ $branding['label'] }}</strong><br><span class="text-muted">{{ $branding['description'] }}</span></div>
          </div>
          <form method="post" action="{{ route('admin.promethee.branding.save') }}">
            @csrf
            <div class="row">
              @foreach($logos as $key => $logo)
                <div class="col-md-6" style="margin-bottom:20px">
                  <label style="display:block;cursor:pointer;min-height:190px;padding:14px;border:2px solid {{ $branding['key'] === $key ? '#2f80ed' : '#e3e3e3' }};border-radius:5px">
                    <input type="radio" name="logo" value="{{ $key }}" @checked($branding['key'] === $key)>
                    <strong>{{ $logo['label'] }}</strong><br><span class="text-muted">{{ $logo['description'] }}</span>
                    <span style="display:flex;align-items:center;justify-content:center;height:105px;margin-top:10px;background:#f5f7fa"><img src="{{ asset('promethee-assets/logos/'.$logo['file']) }}" alt="Aperçu : {{ $logo['label'] }}" style="max-width:90%;max-height:90px;object-fit:contain"></span>
                  </label>
                </div>
              @endforeach
            </div>
            <button class="btn btn-info" type="submit">Utiliser ce logo</button>
          </form>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card">
        <div class="header"><h4 class="title">Importer un logo</h4><p class="category">Ajoutez votre propre visuel.</p></div>
        <div class="content">
          <form method="post" action="{{ route('admin.promethee.branding.import') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group"><label for="logo_file">Fichier image</label><input id="logo_file" class="form-control" type="file" name="logo_file" accept="image/png,image/jpeg,image/webp" required>@error('logo_file')<p class="text-danger">{{ $message }}</p>@enderror</div>
            <p class="text-muted">PNG, JPEG ou WebP · 5 Mo maximum. Préférez un logo sur fond transparent.</p>
            <button class="btn btn-info" type="submit">Importer et appliquer</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
