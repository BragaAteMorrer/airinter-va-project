<?php $__currentLoopData = $flights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="card border" style="background-color: var(--bs-body-bg) !important">
   <div class="card-body">
      <div class="row align-items-center">
         <div class="col-md-3 text-center">
            <span class="fw-bold">
               <?php if($flight->airline->iata): ?>
                  <?php echo e($flight->airline->icao); ?><?php echo e($flight->flight_number); ?> |
               <?php endif; ?>
               <?php echo e($flight->ident); ?>

               <?php if(filled($flight->callsign) && !setting('simbrief.callsign', true)): ?>
                  <?php echo e('| ' . $flight->atc); ?>

               <?php endif; ?>
            </span>
            <div class="text-muted mb-1"><?php echo e(trans_choice('common.flight', 1)); ?></div>
            <span class="fw-bold"><?php if(count($flight->subfleets) !== 0): ?>
               <?php
               $arr = [];
               foreach ($flight->subfleets as $sf) {
                  $arr[] = "{$sf->type}";
               }
               $display = count($arr) > 2 ? implode(', ', array_slice($arr, 0, 2)) . '...' : implode(', ', $arr);
               $allSubfleets = implode(', ', $arr);
               ?>
                  <span class="tooltipbottom" title="<?php echo e($allSubfleets); ?>"><?php echo e($display); ?></span>
               <?php else: ?>
                  Any Subfleet
               <?php endif; ?>
            </span>
            <div class="text-muted"><?php echo app('translator')->get('common.aircraft'); ?></div>
         </div>
         <div class="col-md-6 text-center">
            <div class="flight-path">
               <div>
                  <span class="fw-bold"><i class="ph-fill ph-airplane-takeoff align-middle fs-20 me-1"></i><?php echo e($flight->dpt_airport->icao); ?><span class="fi fi-<?php echo e(strtolower(optional($flight->dpt_airport)->country)); ?> shadow-img ms-2"></span></span>
                  <div class="text-muted"><?php echo e($flight->dpt_time); ?> </div>
                  <a href="<?php echo e(route('frontend.airports.show', [$flight->dpt_airport_id])); ?>" target="_blank" class="btn btn-primary mt-2 tooltiptop" title="<?php echo app('translator')->get('sptheme.newwindow'); ?>"><?php echo e($flight->dpt_airport->name); ?></a>
               </div>
               <div class="dashed-line">
                  <i class="ph-fill ph-airplane-taxiing text-primary airplane-icon fs-1"></i>
               </div>
               <div>
                  <span class="fw-bold"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i><?php echo e($flight->arr_airport->icao); ?><span class="fi fi-<?php echo e(strtolower(optional($flight->arr_airport)->country)); ?> shadow-img ms-2"></span></span>
                  <div class="text-muted"><?php echo e($flight->arr_time); ?> </div>
                  <a href="<?php echo e(route('frontend.airports.show', [$flight->arr_airport_id])); ?>" target="_blank" class="btn btn-primary mt-2 tooltiptop" title="<?php echo app('translator')->get('sptheme.newwindow'); ?>"><?php echo e($flight->arr_airport->name); ?></a>
               </div>
            </div>
         </div>
         <div class="col-md-3 text-center">
            <span class="fw-bold"><i class="ph-fill ph-arrows-horizontal align-middle fs-20 me-1"></i><?php echo e($flight->distance ? $flight->distance . 'nm' : ''); ?></span>
            <div class="text-muted mb-1"><?php echo app('translator')->get('common.distance'); ?></div>
            <?php if($flight->flight_time): ?>
            <span class="fw-bold"><?php echo \App\Support\Units\Time::minutesToTimeString($flight->flight_time); ?></span>
            <?php endif; ?>
            <div class="text-muted"><i class="ph-fill ph-clock-clockwise align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.flighttime'); ?></div>
         </div>
      </div>
   </div>
   <div class="card-footer">
      <div class="row">
         <div class="col-md-3">
            <span class="badge badge-warning tooltiptop" title="<?php echo e(\App\Models\Enums\FlightType::label($flight->flight_type)); ?>"><?php echo e($flight->flight_type); ?></span>
            <?php if(optional($flight->airline)->logo): ?>
            <img src="<?php echo e($flight->airline->logo); ?>" alt="<?php echo e($flight->airline->name); ?>" width="90">
            <?php else: ?>
            <?php echo e($flight->airline->name); ?>

            <?php endif; ?>
         </div>
         <div class="col-md-9 text-end">
            <?php if(isset($saved[$flight->id])): ?>
            <?php if($acars_plugin): ?>
            <?php if(isset($saved[$flight->id])): ?>
            <a href="vmsacars:bid/<?php echo e($saved[$flight->id]); ?>" class="btn btn-secondary tooltiptop" title="<?php echo app('translator')->get('sptheme.loadacars'); ?>"><i class="ph-fill ph-upload"></i></a>
            <?php else: ?>
            <a href="vmsacars:flight/<?php echo e($flight->id); ?>" class="btn btn-secondary tooltiptop" title="<?php echo app('translator')->get('sptheme.loadacars'); ?>"><i class="ph-fill ph-upload"></i></a>
            <?php endif; ?>
            <?php endif; ?>
            <?php if($simbrief !== false): ?>
            <?php if($flight->simbrief && $flight->simbrief->user_id === $user->id): ?>
            <a href="<?php echo e(route('frontend.simbrief.briefing', $flight->simbrief->id)); ?>" class="btn btn-warning"><?php echo app('translator')->get('flights.viewsimbrief'); ?></a>
            <?php else: ?>
            <?php if($simbrief_bids === false || ($simbrief_bids === true && isset($saved[$flight->id]))): ?>
            <?php
            $aircraft_id = isset($saved[$flight->id]) ? App\Models\Bid::find($saved[$flight->id])->aircraft_id : null;
            ?>
            <a href="<?php echo e(route('frontend.simbrief.generate')); ?>?flight_id=<?php echo e($flight->id); ?><?php if($aircraft_id): ?>&aircraft_id=<?php echo e($aircraft_id); ?> <?php endif; ?>" class="btn btn-success"><?php echo app('translator')->get('flights.createsimbrief'); ?></a>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
            <?php endif; ?>
            <a href="<?php echo e(route('frontend.flights.show', [$flight->id])); ?>" class="btn btn-primary"><?php echo e(__('flights.viewflight')); ?></a>
         </div>
      </div>
   </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flights/table.blade.php ENDPATH**/ ?>