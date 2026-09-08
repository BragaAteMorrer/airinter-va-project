<?php $__env->startSection('title', trans_choice('common.flight', 1) . ' ' . $flight->ident); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/21.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body pb-0">
            <h4 class="mt-0 header-title border-bottom">
               <span class="float-end">
                  <?php if(optional($flight->airline)->logo): ?>
                  <img src="<?php echo e($flight->airline->logo); ?>" alt="<?php echo e($flight->airline->name); ?>" width="90">
                  <?php else: ?>
                  <?php echo e($flight->airline->name); ?>:
                  <?php endif; ?>
               </span>
               <span class="badge text-bg-warning ms-1 tooltiptop" title="<?php echo e(\App\Models\Enums\FlightType::label($flight->flight_type)); ?>"><?php echo e($flight->flight_type); ?></span>
               <span class="badge text-bg-primary ms-1"><b><?php echo e(trans_choice('common.flight', 1)); ?>:</b> <?php echo e(optional($flight->airline)->code.' '.$flight->flight_number); ?></span>
               <span class="badge text-bg-primary ms-1"><b><?php echo app('translator')->get('flights.callsign'); ?>:</b> <?php echo e(optional($flight->airline)->icao .' '. $flight->callsign); ?></span>
               <span class="badge text-bg-light ms-1"><b><?php echo app('translator')->get('flights.alternateairport'); ?>:</b> <?php if(filled($flight->alt_airport_id)): ?> <a href="<?php echo e(route('frontend.airports.show', [$flight->alt_airport_id])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e(optional($flight->alt_airport)->icao); ?></a> <?php else: ?> ---- <?php endif; ?></span>
            </h4>
            <div class="row">
               <div class="col">
                  <div class="card border card-default text-center">
                     <div class="card-header fw-bold"><i class="ph-fill ph-airplane-takeoff align-text-bottom fs-20 me-1"></i><?php echo app('translator')->get('flights.departuretime'); ?>: <?php echo e($flight->dpt_time); ?> <span class="fi fi-<?php echo e(strtolower(optional($flight->dpt_airport)->country)); ?> shadow-img mx-3"></span></div>
                     <div class="card-body">
                        <a href="<?php echo e(route('frontend.airports.show', ['id' => $flight->dpt_airport_id])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e(optional($flight->dpt_airport)->full_name ?? $flight->dpt_airport_id); ?></a>
                     </div>
                  </div>
               </div>
               <div class="col">
                  <div class="card border card-default text-center">
                     <div class="card-header fw-bold"><i class="ph-fill ph-clock-countdown align-text-bottom fs-20 me-1"></i><?php echo app('translator')->get('flights.flighttime'); ?> <i class="ph-fill ph-dot-outline mx-1"></i> <i class="ph-fill ph-line-segments align-text-bottom fs-20 me-1"></i> <?php echo app('translator')->get('common.distance'); ?></div>
                     <div class="card-body">
                        <?php echo e(\Modules\SPTheme\Services\TimeService::convert($flight->flight_time)); ?> <i class="ph-fill ph-dot-outline mx-1"></i> <?php echo e($flight->distance); ?> nmi
                     </div>
                  </div>
               </div>
               <div class="col">
                  <div class="card border card-default text-center">
                     <div class="card-header fw-bold"><i class="ph-fill ph-airplane-landing align-text-bottom fs-20 me-1"></i><?php echo app('translator')->get('flights.arrivaltime'); ?>: <?php echo e($flight->arr_time); ?><span class="fi fi-<?php echo e(strtolower(optional($flight->arr_airport)->country)); ?> shadow-img mx-3"></span></div>
                     <div class="card-body">
                        <a href="<?php echo e(route('frontend.airports.show', ['id' => $flight->arr_airport_id])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e(optional($flight->arr_airport)->full_name ?? $flight->arr_airport_id); ?></a>
                     </div>
                  </div>
               </div>
            </div>
            <?php if($flight->subfleets->count() > 0): ?>
            <div class="text-center border" style="background-color: var(--bs-body-bg) !important;">
               <h4 class="mt-3 header-title border-bottom"><i class="ph-fill ph-airplane align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.aircraft'); ?> &amp; <?php echo app('translator')->get('common.subfleet'); ?></h4>
               <ul class="list-inline my-3">
                  <?php $__currentLoopData = $flight->subfleets->sortBy('name', SORT_NATURAL); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li class="list-inline-item pe-lg-4">
                     <p class="mb-0"><?php echo e($sf->type); ?></p>
                     <p class="text-muted mb-0"><?php echo e(optional($sf->airline)->name); ?></p>
                  </li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </ul>
               <h4 class="mt-4 header-title border-bottom"><i class="ph-fill ph-calendar-dot align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.daysofweek'); ?></h4>
               <p><?php echo e(decode_days($flight->days)); ?></p>
            </div>
            <?php endif; ?>
         </div>
         <div class="btn-group p-3">
            <?php if(!setting('pilots.only_flights_from_current') || $flight->dpt_airport_id === Auth::user()->curr_airport_id): ?>
            <a class="btn save_flight <?php echo e(isset($bid) ? 'btn-danger':'btn-success'); ?> " id="<?php echo e($flight->id); ?>" onclick="AddRemoveBid('<?php echo e(isset($bid) ? 'remove':'add'); ?>')"><?php echo e(isset($bid) ? __('flights.removebid'): __('flights.addbid')); ?></a>
            <?php endif; ?>
            <?php if(filled(setting('simbrief.api_key'))): ?>
            <?php if(!setting('simbrief.only_bids') || setting('simbrief.only_bids') && isset($bid)): ?>
            <?php if($flight->simbrief && $flight->simbrief->user_id == Auth::user()->id): ?>
            <a href="<?php echo e(route('frontend.simbrief.briefing', $flight->simbrief->id)); ?>" class="btn btn-warning"><?php echo app('translator')->get('flights.viewsimbrief'); ?></a>
            <?php else: ?>
            <?php
            $aircraft_id = isset($saved[$flight->id]) ? App\Models\Bid::find($saved[$flight->id])->aircraft_id : null;
            ?>
            <a href="<?php echo e(route('frontend.simbrief.generate')); ?>?flight_id=<?php echo e($flight->id); ?><?php if($aircraft_id): ?> &aircraft_id=<?php echo e($aircraft_id); ?> <?php endif; ?>" class="btn btn-success"><?php echo app('translator')->get('flights.createsimbrief'); ?></a>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
            <?php if($acars_plugin && isset($bid)): ?>
            <a href="vmsacars:bid/<?php echo e($bid->id); ?>" class="btn btn-secondary"><?php echo app('translator')->get('sptheme.loadacars'); ?></a>
            <?php endif; ?>
            <a href="<?php echo e(route('frontend.pireps.create')); ?>?flight_id=<?php echo e($flight->id); ?>" class="btn btn-info"><?php echo app('translator')->get('pireps.filenewpirep'); ?></a>
         </div>  
      </div>
      <?php echo $__env->make('flights.map', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-note align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.notes'); ?></h4>
            <div class="card-body p-0">
               <?php if(filled($flight->notes)): ?>
               <div class="alert alert-info mb-0" role="alert"><?php echo $flight->notes; ?></div>
               <?php else: ?>
               <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.nonote'); ?></div>
               <?php endif; ?>
            </div>
         </div>
      </div>
      <div class="card border mb-0">
         <div class="card-body">
            <div class="tab-default">
               <div role="tabpanel">
                  <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                     <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#dpt-tab" role="tab" aria-selected="true">
                           <i class="ph-fill ph-airplane-takeoff align-middle fs-18 me-1"></i><?php echo app('translator')->get('dashboard.weatherat', ['ICAO' => $flight->dpt_airport_id]); ?>
                        </a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#arr-tab" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-airplane-landing align-middle fs-18 me-1"></i><?php echo app('translator')->get('dashboard.weatherat', ['ICAO' => $flight->arr_airport_id]); ?>
                        </a>
                     </li>
                     <?php if($flight->alt_airport_id): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#alt-tab" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-airplane-in-flight align-middle fs-18 me-1"></i><?php echo app('translator')->get('dashboard.weatherat', ['ICAO' => $flight->alt_airport_id]); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                  </ul>
                  <div class="tab-content text-muted">
                     <div class="tab-pane active show" id="dpt-tab" role="tabpanel">
                        <div class="p-4">
                           <h5 class="m-0"><a href="https://metar-taf.com/<?php echo e($flight->dpt_airport_id); ?>" id="metartaf-R5OsjIjx" style="pointer-events: none">METAR <?php echo e($flight->dpt_airport_id); ?></a></h5>
                           <script async defer crossorigin="anonymous" src="https://metar-taf.com/embed-js/<?php echo e($flight->dpt_airport_id); ?>?layout=landscape&target=R5OsjIjx"></script>
                        </div>
                        <?php echo e(Widget::Weather(['icao' => $flight->dpt_airport_id])); ?>

                     </div>
                     <div class="tab-pane" id="arr-tab" role="tabpanel">
                        <div class="p-4">
                           <h5 class="m-0"><a href="https://metar-taf.com/<?php echo e($flight->arr_airport_id); ?>" id="metartaf-R4OsjIjx" style="pointer-events: none">METAR <?php echo e($flight->arr_airport_id); ?></a></h5>
                           <script async defer crossorigin="anonymous" src="https://metar-taf.com/embed-js/<?php echo e($flight->arr_airport_id); ?>?layout=landscape&target=R4OsjIjx"></script>
                        </div>
                        <?php echo e(Widget::Weather(['icao' => $flight->arr_airport_id])); ?>

                     </div>
                     <?php if($flight->alt_airport_id): ?>
                     <div class="tab-pane" id="alt-tab" role="tabpanel">
                        <?php echo e(Widget::Weather(['icao' => $flight->alt_airport_id])); ?>

                     </div>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<script>
async function AddRemoveBid(action) {
   const flight_id = "<?php echo e($flight->id); ?>";
   if (action === "add") {
      await phpvms.bids.addBid(flight_id);
      
      const Toast = Swal.mixin({
         toast: true,
         position: "top-end",
         showConfirmButton: false,
         timer: 3000,
         animation: true,
         iconColor: 'white',
         customClass: {
            popup: 'colored-toast',
         },
         timerProgressBar: true,
         didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
         }
      });
      
      Toast.fire({
         icon: "success",
         title: "<?php echo app('translator')->get('flights.bidadded'); ?>"
      });

      setTimeout(() => {
         location.reload();
      }, 3000);

   } else {
      await phpvms.bids.removeBid(flight_id);
      
      const Toast = Swal.mixin({
         toast: true,
         position: "top-end",
         showConfirmButton: false,
         timer: 3000,
         animation: true,
         iconColor: 'white',
         customClass: {
            popup: 'colored-toast',
         },
         timerProgressBar: true,
         didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
         }
      });
      
      Toast.fire({
         icon: "info",
         title: "<?php echo app('translator')->get('flights.bidremoved'); ?>"
      });

      setTimeout(() => {
         location.reload();
      }, 3000);
   }
}
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flights/show.blade.php ENDPATH**/ ?>