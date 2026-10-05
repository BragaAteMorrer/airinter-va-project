@extends('promethee::layout')
@section('title','Boutique Air Inter')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">BOUTIQUE PILOTE</span><h1>Boutique Air Inter.</h1><p>Débloquez appareils, variantes et contenus Hermès avec votre solde pilote.</p></div><span class="tag metric-tag">Solde : {{ $wallet }}</span></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@php $labels=['aircraft_type'=>'Type avion','aircraft_variant'=>'Variante','aircraft_premium'=>'Historique / premium','aircraft_rental'=>'Location avion','reservation_extension'=>'Réservation +','maintenance_priority'=>'Priorité maintenance','hermes_theme'=>'Thème Hermès','hermes_sound_pack'=>'Sons Hermès','hermes_efb_skin'=>'Skin EFB','livery'=>'Livrée']; @endphp
<div class="flight-cards">
@forelse($items as $item)
<article class="panel line-card">
 <div class="line-card-head"><div><span class="eyebrow">{{ $labels[$item->type] ?? $item->type }}</span><h2>{{ $item->name }}</h2></div><span class="tag">{{ new \App\Support\Money($item->price) }}</span></div>
 <p>{{ $item->description }}</p>
 @if($item->duration_hours)<p class="muted">Durée : {{ $item->duration_hours }} h</p>@endif
 <form method="post" action="{{ route('promethee.shop.buy',$item->id) }}">@csrf
  @if($item->type==='reservation_extension')
   <label>Réservation<select name="target_id" required>@foreach($bids as $bid)<option value="{{ $bid->id }}">{{ optional($bid->flight)->ident ?? $bid->flight_id }} · {{ $bid->created_at?->format('d/m H:i') }}</option>@endforeach</select></label>
  @elseif($item->type==='maintenance_priority')
   <label>Appareil<select name="target_id" required>@foreach($aircraft as $plane)<option value="{{ $plane->id }}">{{ $plane->registration }} · {{ optional($plane->subfleet)->name }}</option>@endforeach</select></label>
  @endif
  <button @disabled((int)$wallet->getAmount() < (int)$item->price)>{{ $item->type==='aircraft_rental' ? 'Louer' : 'Obtenir' }}</button>
 </form>
</article>
@empty<p class="empty">Catalogue indisponible.</p>@endforelse
</div>
<section class="panel table-wrap"><h2>Mon inventaire</h2><table><thead><tr><th>Article</th><th>Type</th><th>Prix</th><th>Validité</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td>{{ $order->name }}</td><td>{{ $labels[$order->type] ?? $order->type }}</td><td>{{ new \App\Support\Money($order->price) }}</td><td>{{ $order->expires_at ? \Carbon\Carbon::parse($order->expires_at)->format('d/m/Y H:i') : 'Permanent' }}</td></tr>
@empty<tr><td colspan="4">Aucun article.</td></tr>@endforelse
</tbody></table></section>
@endsection