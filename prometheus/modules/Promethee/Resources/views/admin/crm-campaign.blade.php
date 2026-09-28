@extends('promethee::layout')
@section('title','CRM · Campagne #'.$campaign->id)
@section('content')
<div class="ops-header compact">
  <div>
    <span class="eyebrow">CRM · CAMPAGNE #{{ $campaign->id }}</span>
    <h1>{{ $campaign->subject }}</h1>
    <p>{{ $campaign->sender_name ?: 'Air Inter' }} · {{ $campaign->sender_email ?: '—' }}</p>
  </div>
  <span class="tag">{{ strtoupper($campaign->status) }}</span>
</div>

<section class="control-strip">
  <article><span>Destinataires</span><strong>{{ $campaign->recipient_count }}</strong><small>sélectionnés</small></article>
  <article><span>Envoyés</span><strong>{{ $campaign->sent_count }}</strong><small>succès SMTP</small></article>
  <article><span>Échecs</span><strong>{{ $campaign->failed_count }}</strong><small>à contrôler</small></article>
  <article><span>Créée par</span><strong>{{ $campaign->creator_name ?: '—' }}</strong><small>{{ \Carbon\Carbon::parse($campaign->created_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i') }}</small></article>
</section>

<div class="two-columns">
  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">CONTENU</span><h2>Message envoyé</h2></div></div>
    <pre class="crm-message-preview">{{ $campaign->body }}</pre>
  </section>
  <section class="panel">
    <div class="panel-heading"><div><span class="eyebrow">CIBLAGE</span><h2>Critères enregistrés</h2></div></div>
    <pre class="crm-json-preview">{{ json_encode(json_decode($campaign->audience,true), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
  </section>
</div>

<section class="panel table-wrap">
  <div class="panel-heading"><div><span class="eyebrow">JOURNAL D’ENVOI</span><h2>Destinataires</h2></div><a class="button outline" href="{{ route('admin.promethee.crm') }}">Retour CRM</a></div>
  <table>
    <thead><tr><th>Pilote</th><th>E-mail</th><th>État</th><th>Envoyé à</th><th>Erreur</th></tr></thead>
    <tbody>
      @foreach($recipients as $recipient)
      <tr>
        <td>{{ $recipient->name ?: '—' }}</td>
        <td>{{ $recipient->email }}</td>
        <td><span class="tag">{{ strtoupper($recipient->status) }}</span></td>
        <td>{{ $recipient->sent_at ? \Carbon\Carbon::parse($recipient->sent_at)->setTimezone('Europe/Paris')->format('d/m/Y H:i:s') : '—' }}</td>
        <td>{{ $recipient->error ?: '—' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>
</section>
@endsection
