@extends('sptransfer::layouts.frontend')
@section('title', 'Airline Transfer')
@section('content')
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="{{ public_asset('/SPTheme/images/banner/11.jpg') }}" class="img-fluid card-img-top rounded rounded" width="1920" height="200" alt="@lang('sptheme.banner')">
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-3 header-title border-bottom"><i class="ph-fill ph-list-magnifying-glass fs-20 me-1"></i>@lang('SPTransfer::common.title-a')</h4>
            @include('flash::message')
            @if($state === 0)
            <h5 class="my-3">@lang('SPTransfer::common.reqform')</h5>
            <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i>@lang('SPTransfer::common.reqis') {{ $status }}.</div>
            @else
            @if($limit)
            <div class="alert alert-info" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>@lang('SPTransfer::common.limited-a') {{ $daysLimit }} @lang('SPTransfer::common.days').</div>
            @else
            <form method="post" action="{{ route('sptransfer.airline.store') }}" class="form-horizontal">
               @csrf
               <h5 class="my-3">@lang('SPTransfer::common.based-a') {{ $current_airline_name }} ({{ strtoupper($current_airline) }})</h5>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-airplane-takeoff align-middle fs-20 me-1"></i>@lang('SPTransfer::common.desired-a')</label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="airline_request_id" id="airline_request_id" class="form-select select2" placeholder="@lang('SPTransfer::common.selectplace')" required>
                            @foreach($airlines as $airline_request)
                              <option value="{{ $airline_request->id }}">{{ $airline_request->icao }} - {{ $airline_request->name }}</option>
                            @endforeach   
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label for="email" class="col-5 control-label mt-3"><i class="ph-fill ph-text-indent align-middle fs-20 me-1"></i>@lang('SPTransfer::common.reason-a')</label>
                     <div class="col-7">
                        <textarea name="reason" id="reason" class="form-control bg-white border" maxlength="100" placeholder="@lang('SPTransfer::common.chars')" required></textarea>
                     </div>
                  </div>
               </div>
               @if($spfinance)
               @if($charge_type === 0)
               <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i>@lang('SPTransfer::common.charged') {{ $spvalue }} @lang('SPTransfer::common.onrequest').</div>
               @else
               <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i>@lang('SPTransfer::common.charged') {{ $spvalue }} @lang('SPTransfer::common.ontransfer').</div>
               @endif
               @endif
               <button type="submit" class="btn btn-primary">@lang('SPTransfer::common.request')</button>
            </form>
            @endif
            @endif
         </div>
      </div>
   </div>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-3 header-title border-bottom"><i class="ph-fill ph-info fs-20 me-1"></i>@lang('SPTransfer::common.lasttitle')</h4>
            @if(empty($lasttransfer))
            <div class="alert alert-info" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>@lang('SPTransfer::common.didnot')</div>
            @else
            <div class="table-responsive">
               <table class="table table-striped table-hover mb-0">
                  <tbody>
                     <tr>
                        <td class="fw-bold">@lang('SPTransfer::common.transferid')</td>
                        <td>{{ $lasttransfer->id }}</td>
                     </tr>
                     <tr>
                        <td class="fw-bold">@lang('SPTransfer::common.reqhub-a')</td>
                        <td>{{ $request_airline->icao }} - {{ $request_airline->name }}</td>
                     </tr>
                     <tr>
                        <td class="fw-bold">@lang('SPTransfer::common.reqdate')</td>
                        <td>{{ $lasttransfer->created_at->format('d. F Y - H:i') }} UTC</td>
                     </tr>
                     <tr>
                        <td class="fw-bold">@lang('SPTransfer::common.reqstatus')</td>
                        <td>{{ $status }}@if($status == 'Rejected'): {{ $lasttransfer->reject_reason ?? '-' }} @endif </td>
                     </tr>
                     <tr>
                        <td class="fw-bold">@lang('SPTransfer::common.reqreason')</td>
                        <td>{{ $lasttransfer->reason }}</td>
                     </tr>
                  </tbody>
               </table>
            </div>
            @endif
         </div>
      </div>
   </div>
</div>
@endsection