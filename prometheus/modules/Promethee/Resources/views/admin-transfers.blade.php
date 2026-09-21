@extends('promethee::layout')
@section('title','Demandes de transfert')
@section('content')
<div class="ops-header compact"><h1>Demandes de transfert.</h1></div><section class="panel table-wrap"><table><thead><tr><th>Pilote</th><th>Type</th><th>Cible</th><th>Motif</th><th>Décision</th></tr></thead><tbody>@foreach($requests as $request)<tr><td>{{ $request->pilot_id }} · {{ $request->user_name }}</td><td>{{ $request->type }}</td><td>{{ $request->airline_name ?: $request->airport_name }}</td><td>{{ $request->reason }}</td><td>@if($request->status==='pending')<form method="post" action="{{ route('admin.promethee.transfers.decide',$request->id) }}">@csrf<select name="status"><option value="approved">Approuver</option><option value="rejected">Refuser</option></select><input name="decision_note" placeholder="Note"><button>Décider</button></form>@else{{ $request->status }}@endif</td></tr>@endforeach</tbody></table></section>
@endsection
