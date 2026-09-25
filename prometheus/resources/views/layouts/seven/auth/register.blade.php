@extends('auth.onboarding_layout')
@section('title', 'Rejoindre Air Inter VA')
@section('hero-title', 'Rejoignez Air Inter.')
@section('hero-copy', 'Votre candidature pilote commence ici. Une seule compagnie d’entrée : Air Inter. Votre profil, votre hub et votre fuseau seront ensuite utilisés dans Prométhée et Hermès.')

@section('content')
  <div class="airinter-form-heading">
    <span class="eyebrow">DOSSIER PILOTE</span>
    <h2>Créer votre compte</h2>
    <p>Renseignez vos informations opérationnelles. La compagnie Air Inter est imposée à l’inscription.</p>
  </div>

  <div class="airinter-company-lock">
    <img src="{{ public_asset('/promethee-assets/logos/air-inter-compact.png') }}" alt="Air Inter">
    <span>
      <strong>{{ $airline->name }} · {{ $airline->icao }}</strong>
      <small>Compagnie d’entrée verrouillée par Prométhée</small>
    </span>
  </div>

  <form method="post" action="{{ url('/register') }}">
    @csrf
    <div class="airinter-form-grid">
      <div class="airinter-field">
        <label for="name">@lang('auth.fullname')</label>
        <input type="text" name="name" id="name" value="{{ old('name') }}" autocomplete="name" required class="@error('name') is-invalid @enderror">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="airinter-field">
        <label for="email">@lang('auth.emailaddress')</label>
        <input type="email" name="email" id="email" value="{{ old('email') }}" autocomplete="email" required class="@error('email') is-invalid @enderror">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="airinter-field">
        <label for="home_airport_id">@lang('airports.home')</label>
        <select name="home_airport_id" id="home_airport_id" required
          class="airport_search @if ($hubs_only) hubs_only @endif @error('home_airport_id') is-invalid @enderror">
          @foreach ($airports as $airport_id => $airport_label)
            <option value="{{ $airport_id }}" @selected($airport_id === old('home_airport_id'))>{{ $airport_label }}</option>
          @endforeach
        </select>
        @error('home_airport_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="airinter-field">
        <label for="country">@lang('common.country')</label>
        <select name="country" id="country" class="@error('country') is-invalid @enderror">
          @foreach ($countries as $country_id => $country_label)
            <option value="{{ $country_id }}" @selected($country_id === old('country'))>{{ $country_label }}</option>
          @endforeach
        </select>
        @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="airinter-field full">
        <label for="timezone">@lang('common.timezone')</label>
        <select name="timezone" id="timezone" required class="@error('timezone') is-invalid @enderror">
          @foreach ($timezones as $group_name => $group_timezones)
            <optgroup label="{{ $group_name }}">
              @foreach ($group_timezones as $timezone_id => $timezone_label)
                <option value="{{ $timezone_id }}" @selected($timezone_id === old('timezone', $defaultTimezone))>{{ $timezone_label }}</option>
              @endforeach
            </optgroup>
          @endforeach
        </select>
        @error('timezone')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      @if (setting('pilots.allow_transfer_hours') === true)
        <div class="airinter-field full">
          <label for="transfer_time">@lang('auth.transferhours')</label>
          <input type="number" min="0" name="transfer_time" id="transfer_time" value="{{ old('transfer_time') }}" class="@error('transfer_time') is-invalid @enderror">
          @error('transfer_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      @endif

      <div class="airinter-field">
        <label for="password">@lang('auth.password')</label>
        <input type="password" name="password" id="password" autocomplete="new-password" required class="@error('password') is-invalid @enderror">
        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>

      <div class="airinter-field">
        <label for="password_confirmation">@lang('passwords.confirm')</label>
        <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" required>
      </div>

      @foreach ($userFields ?? [] as $field)
        <div class="airinter-field {{ $loop->last && $loop->count % 2 ? 'full' : '' }}">
          <label for="field_{{ $field->slug }}">{{ $field->name }}</label>
          <input type="text" name="field_{{ $field->slug }}" id="field_{{ $field->slug }}"
            value="{{ old('field_'.$field->slug) }}" class="@error('field_'.$field->slug) is-invalid @enderror">
          @error('field_'.$field->slug)<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      @endforeach
    </div>

    @if ($captcha['enabled'] === true)
      <div class="airinter-field full mt-4">
        <label>@lang('auth.fillcaptcha')</label>
        <div class="h-captcha" data-sitekey="{{ $captcha['site_key'] }}"></div>
        @error('h-captcha-response')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
    @endif

    @if ($invite)
      <input type="hidden" name="invite" value="{{ $invite->id }}">
      <input type="hidden" name="invite_token" value="{{ base64_encode($invite->token) }}">
    @endif

    <div class="airinter-consents">
      <div class="mb-3">@include('auth.toc')</div>
      <div class="form-check">
        <input class="form-check-input @error('toc_accepted') is-invalid @enderror" type="checkbox" name="toc_accepted" id="toc_accepted" {{ old('toc_accepted') ? 'checked' : '' }}>
        <label class="form-check-label" for="toc_accepted">@lang('auth.tocaccept')</label>
        @error('toc_accepted')<div class="invalid-feedback">{{ $message }}</div>@enderror
      </div>
      <div class="form-check">
        <input type="hidden" name="opt_in" value="0">
        <input class="form-check-input" type="checkbox" name="opt_in" id="opt_in" value="1" {{ old('opt_in') ? 'checked' : '' }}>
        <label class="form-check-label" for="opt_in">@lang('profile.opt-in-descrip')</label>
      </div>
      <button type="submit" class="airinter-submit" id="register_button" {{ old('toc_accepted') ? '' : 'disabled' }}>ENVOYER MA CANDIDATURE</button>
    </div>
  </form>
@endsection

@section('scripts')
  @if ($captcha['enabled'])<script src="https://hcaptcha.com/1/api.js" async defer></script>@endif
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      new TomSelect('#country');
      new TomSelect('#timezone', { searchField: ['text', 'value'] });
      const toc = document.getElementById('toc_accepted');
      const submit = document.getElementById('register_button');
      const sync = () => submit.disabled = !toc.checked;
      toc.addEventListener('change', sync);
      sync();
    });
  </script>
  @include('scripts.airport_search')
@endsection
