AIR INTER · PROMÉTHÉE
======================

@if(!empty($greeting))
{{ $greeting }}
@elseif($level === 'error')
Information importante,
@else
Bonjour,
@endif

@foreach($introLines as $line)
{{ $line }}
@endforeach

@if(isset($actionText))
{{ $actionText }} : {{ $actionUrl }}
@endif

@foreach($outroLines as $line)
{{ $line }}
@endforeach

Direction de l’Exploitation Aérienne
Air Inter Virtual Airlines
Pourquoi vivre sans ailes !
