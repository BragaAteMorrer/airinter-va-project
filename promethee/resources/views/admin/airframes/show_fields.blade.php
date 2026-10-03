<!-- Id Field -->
<div class="form-group">
  {{ Form::label('id', 'Id:') }}
  <p>{{ $airframe->id }}</p>
</div>

<!-- Type Code Field -->
<div class="form-group">
  {{ Form::label('type', 'ICAO Code:') }}
  <p>{{ $airframe->icao }}</p>
</div>

<!-- Name Field -->
<div class="form-group">
  {{ Form::label('name', 'Name:') }}
  <p>{{ $airframe->name }}</p>
</div>

<!-- Description Field -->
<div class="form-group">
  {{ Form::label('airframe_id', 'SB Airframe ID:') }}
  <p>{{ $airframe->airframe_id }}</p>
</div>

@php($sb = $simbrief_profile ?? $airframe->simbriefProfile())
<div class="form-group">
  <strong>Mode SimBrief</strong>
  <p>{{ $sb['strategy'] }}</p>
</div>
<div class="form-group">
  <strong>Type de calcul</strong>
  <p>
    @if($sb['strategy'] === 'proxy')
      {{ $sb['proxy_type'] }} — l’appareil reste {{ $airframe->icao }}
    @elseif($sb['strategy'] === 'custom_airframe')
      {{ $sb['internal_id'] ?: 'Internal ID manquant' }}
    @else
      {{ $airframe->icao }}
    @endif
  </p>
</div>

<!-- Created At Field -->
<div class="form-group">
  {{ Form::label('created_at', 'Created At:') }}
  <p>{{ show_datetime($airframe->created_at) }}</p>
</div>

<!-- Updated At Field -->
<div class="form-group">
  {{ Form::label('updated_at', 'Updated At:') }}
  <p>{{ show_datetime($airframe->updated_at) }}</p>
</div>

