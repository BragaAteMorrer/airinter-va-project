@php($adminBranding = app(\Modules\Promethee\Services\BrandingService::class)->active())
<div class="sidebar" data-background-color="white" data-active-color="info">

  <!--
      Tip 1: you can change the color of the sidebar's background using: data-background-color="white | black"
      Tip 2: you can change the color of the active button using the data-active-color="primary | info | success | warning | danger"
  -->


  <div class="sidebar-wrapper">
    <div class="logo">
      <a href="{{ url('/admin/dashboard') }}" class="admin-brand" aria-label="Air Inter Prométhée — administration">
        <img src="{{ $adminBranding['url'] }}" alt="{{ $adminBranding['label'] }}">
        <span>
          <b>Prométhée</b>
          <small>Centre d'opérations</small>
        </span>
      </a>
    </div>

    <ul class="nav">
      @include('admin.menu')
    </ul>

    <br/>

    <div class="row" style="margin-bottom: 20px;">
      <div class="col-xs-12 text-center">
        <a class="small admin-version"
           style="cursor: pointer"
           data-container="body"
           data-toggle="popover"
           data-placement="right"
           data-content="{{$version_full}}">
          version {{ $version }}
        </a>
      </div>
    </div>
  </div>
</div>
