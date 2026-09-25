@component('mail::message')
# {{ $news->subject }}

{!! $news->body !!}

@component('mail::button', ['url' => url('/')])
OUVRIR AIR INTER VA
@endcomponent

**Direction de l’Exploitation Aérienne**  
Air Inter Virtual Airlines
@endcomponent
