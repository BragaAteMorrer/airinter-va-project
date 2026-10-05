@extends('promethee::layout')
@section('title','Administration boutique')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">ADMIN · ÉCONOMIE PILOTE</span><h1>Boutique Air Inter.</h1><p>Le catalogue utilise exclusivement le journal financier IG phpVMS.</p></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="two-columns"><section class="panel"><h2>Nouveau produit</h2>
<form method="post" action="{{ route('admin.promethee.shop.items.save') }}" class="form-grid">@csrf
<label>Nom<input name="name" required value="{{ old('name') }}"></label>
<label>Type<select name="type" id="shop-type" required>
<option value="aircraft_type">Déblocage type avion</option><option value="aircraft_variant">Déblocage variante</option><option value="aircraft_premium">Avion premium / historique</option><option value="aircraft_rental">Location temporaire</option>
<option value="reservation_extension">Réservation prolongée</option><option value="maintenance_priority">Priorité maintenance</option>
<option value="hermes_theme">Thème Hermès</option><option value="hermes_sound_pack">Pack sons Hermès</option><option value="hermes_efb_skin">Skin EFB Hermès</option><option value="livery">Livrée spéciale</option>
</select></label>
<label>Prix IG<input type="number" name="price" min="0" step="0.01" required></label>
<label>Type / variante avion<select name="target_id"><option value="">— Aucun —</option>@foreach($subfleets as $sf)<option value="{{ $sf->id }}">{{ $sf->name }} · {{ $sf->type }} ({{ $sf->aircraft->count() }} appareil(s))</option>@endforeach</select></label>
<label>Durée (heures)<input type="number" name="duration_hours" min="1" max="8760" placeholder="Vide = permanent"></label>
<label>Clé ressource Hermès / livrée<input name="asset_key" maxlength="120" placeholder="ex. theme.airinter-1978"></label>
<label class="full">Description<textarea name="description">{{ old('description') }}</textarea></label>
<label><input type="checkbox" name="active" value="1" checked> Actif</label><button>Ajouter au catalogue</button>
</form></section>
<section class="panel"><h2>Crédit / débit pilote</h2><form method="post" action="{{ route('admin.promethee.shop.wallets.credit') }}" class="form-grid">@csrf
<label>Pilote<select name="user_id">@foreach($pilots as $pilot)<option value="{{ $pilot->id }}">{{ $pilot->pilot_id }} · {{ $pilot->name }}</option>@endforeach</select></label>
<label>Montant IG (+/-)<input type="number" step="0.01" name="amount" required></label><button>Valider</button></form></section></div>
<section class="panel table-wrap"><h2>Catalogue</h2><table><thead><tr><th>Produit</th><th>Type</th><th>Prix IG</th><th>Cible</th><th>Durée</th><th>État</th></tr></thead><tbody>
@foreach($items as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->type }}</td><td>{{ new \App\Support\Money($item->price) }}</td><td>{{ $item->target_id ?: 'Dynamique' }}</td><td>{{ $item->duration_hours ? $item->duration_hours.' h' : 'Permanent' }}</td><td>{{ $item->active ? 'Actif' : 'Inactif' }}</td></tr>@endforeach
</tbody></table></section>
<section class="panel table-wrap"><h2>File priorité maintenance</h2><table><thead><tr><th>Pilote</th><th>Appareil</th><th>Demandé</th><th>État</th><th>Action</th></tr></thead><tbody>
@forelse($priorities as $p)<tr><td>{{ $p->pilot_id }} · {{ $p->pilot_name }}</td><td>{{ $p->registration }}</td><td>{{ \Carbon\Carbon::parse($p->requested_at)->format('d/m/Y H:i') }}</td><td>{{ $p->status }}</td><td><form method="post" action="{{ route('admin.promethee.shop.maintenance-priorities.update',$p->id) }}">@csrf @method('PUT')<select name="status"><option>queued</option><option>accepted</option><option>completed</option><option>rejected</option></select><button>OK</button></form></td></tr>@empty<tr><td colspan="5">Aucune demande.</td></tr>@endforelse
</tbody></table></section>
@endsection