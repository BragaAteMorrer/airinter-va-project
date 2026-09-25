@component('mail::layout')
  @slot('header')
    @component('mail::header', ['url' => config('app.url')])
      Air Inter
    @endcomponent
  @endslot

  {{ $slot }}

  @isset($subcopy)
    @slot('subcopy')
      @component('mail::subcopy')
        {{ $subcopy }}
      @endcomponent
    @endslot
  @endisset

  @slot('footer')
    @component('mail::footer')
      © {{ date('Y') }} Air Inter Virtual Airlines. Tous droits réservés.
    @endcomponent
  @endslot
@endcomponent
