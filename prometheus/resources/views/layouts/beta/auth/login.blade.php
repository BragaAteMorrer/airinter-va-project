@extends('auth.login_layout')
@section('title', __('common.login'))
@section('content')
  <div class="login-form-heading"><h2>Connexion pilote</h2><p>Accédez à votre carnet de vol et aux opérations.</p></div>
  <div id="minitel-access" class="minitel-access" hidden><p><strong>ACCÈS MINITEL :</strong> composez le service avant de vous identifier.</p><label for="minitel-access-code">CODE DU SERVICE</label><input id="minitel-access-code" type="text" inputmode="text" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="3615 AIR INTER"><div id="minitel-access-status" class="minitel-access-status" aria-live="polite"></div></div>
  <form method="post" action="{{ url('/login') }}" class="login-form">
    @csrf
    <div class="field"><label for="email">@lang('common.email') @lang('common.or') @lang('common.pilot_id')</label><input data-auth-control type="text" name="email" id="email" value="{{ old('email') }}" required autofocus>@if($errors->has('email'))<div class="text-danger small mt-1">{{ $errors->first('email') }}</div>@endif</div>
    <div class="field"><label for="password">@lang('auth.password')</label><input data-auth-control type="password" name="password" id="password" required>@if($errors->has('password'))<div class="text-danger small mt-1">{{ $errors->first('password') }}</div>@endif</div>
    <button data-auth-control type="submit" class="login-submit">@lang('common.login')</button>
    <div class="oauth-login">@if(config('services.discord.enabled'))<a data-auth-external href="{{ route('oauth.redirect', ['provider' => 'discord']) }}" style="background:#738ADB">@lang('auth.loginwith', ['provider' => 'Discord'])</a>@endif @if(config('services.ivao.enabled'))<a data-auth-external href="{{ route('oauth.redirect', ['provider' => 'ivao']) }}" style="background:#0d2c99">@lang('auth.loginwith', ['provider' => 'IVAO'])</a>@endif @if(config('services.vatsim.enabled'))<a data-auth-external href="{{ route('oauth.redirect', ['provider' => 'vatsim']) }}" style="background:#29B473">@lang('auth.loginwith', ['provider' => 'VATSIM'])</a>@endif</div>
    <div class="login-links"><a href="{{ url('/register') }}">@lang('auth.createaccount')</a><a href="{{ url('/password/reset') }}">@lang('auth.forgotpassword') ?</a></div>
  </form>
@endsection
