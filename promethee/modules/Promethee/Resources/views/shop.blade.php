@extends('promethee::layout')
@section('title','Boutique virtuelle')
@section('content')
<div class="ops-header compact"><div><span class="eyebrow">BOUTIQUE PILOTE</span><h1>Boutique virtuelle.</h1><p>Chaque achat est débité du journal phpVMS du pilote.</p></div><span class="tag metric-tag">Solde : {{ $wallet }}</span></div>
<div class="flight-cards">@forelse($items as $item)<article class="panel line-card"><div class="line-card-head"><h2>{{ $item->name }}</h2><span class="tag">{{ new \App\Support\Money($item->price) }}</span></div><p>{{ $item->description }}</p><form method="post" action="{{ route('promethee.shop.buy',$item->id) }}">@csrf<button @disabled((int)$wallet->getAmount() < (int)$item->price)>Obtenir</button></form></article>@empty<p class="empty">Catalogue indisponible.</p>@endforelse</div>
<section class="panel table-wrap"><h2>Mon inventaire</h2><table><thead><tr><th>Article</th><th>Prix</th><th>Date</th></tr></thead><tbody>@forelse($orders as $order)<tr><td>{{ $order->name }}</td><td>{{ $order->price }}</td><td>{{ \Carbon\Carbon::parse($order->purchased_at)->format('d/m/Y') }}</td></tr>@empty<tr><td colspan="3">Aucun article.</td></tr>@endforelse</tbody></table></section>
@endsection
