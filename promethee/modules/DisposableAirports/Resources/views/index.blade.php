@extends('admin.app')
@section('title', 'Disposable Airports')

@section('content')
  <div class="card border-blue-bottom" style="margin-bottom: 10px;">
    <div class="content">
      <p>This module is designed to automate Airport Imports and Updates via Open Sources</p>
      <p>
        Documentation about this module can be found in the <b>README.md</b> file or at GitHub via this link
        <a href="https://github.com/FatihKoz/DisposableAirports#readme" target="_blank" title="Online Readme">Online Readme</a>
      </p>
      <hr>
      <p>@if(filled($details->version)) Version: {{ $details->version }} @endif <a href="https://github.com/FatihKoz" target="_blank">&copy; B.Fatih KOZ</a></p>
    </div>
  </div>
  {{-- Module Features & Settings --}}
  <div class="row text-center" style="margin-left:5px; margin-right:5px;">
    <div class="col-sm-12">
      {{-- Deleted Airports Management --}}
      <div class="col-sm-8">
        <div class="card border-blue-bottom" style="padding:10px;">
          @include('DAirports::airports_table')
        </div>
      </div>
      {{-- Module Settings and Features --}}
      <div class="col-sm-4">
        <div class="card border-blue-bottom" style="padding:5px;">
          <br>
          <a href="{{ route('DAirports.update_all') }}" class="btn btn-primary btn-sm" style="margin-top:5px;">
            Download & Update @if (DA_Setting('dairports.update_only', true) === false) and Create @endif</a>
          <br><br>
          <span class="text-info">Download latest airport data and process (be patient)</span>
        </div>
        <div class="card border-blue-bottom" style="padding:5px;">
          <br>
          <a href="{{ route('DAirports.fix_uzbekistan') }}" class="btn btn-warning btn-sm" style="margin-top:5px;">Fix Uzbekistan Codes</a>
          <br><br>
          <span class="text-info">Airports, Flights and Pireps will be checked & updated with new codes</span>
        </div>
        <div class="card border-blue-bottom" style="padding:5px;">
          <br>
          <a href="{{ route('DAirports.cleanup_airports') }}" class="btn btn-danger btn-sm" style="margin-top:5px;" onclick="return confirm('This will delete airport records !!!\n\n Are you sure ?')">Cleanup Airports</a>
          <br><br>
          <span class="text-info">Keep only scheduled and flown airports (including alternates)</span>
        </div>
        <div class="card border-blue-bottom" style="padding:5px;">
          <b>Module Settings</b>
          <br>
          @include('DAirports::settings_table', ['group' => 'General'])
          <span class="text-info">CRON is needed for automation, manual imports are always possible</span>
        </div>
      </div>
    </div>
  </div>
@endsection
