<?php $__env->startSection('title', $airport->full_name); ?>
<?php $__env->startSection('content'); ?>
<?php
$currency = setting('units.currency');
?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/17.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo e($airport->full_name); ?><?php if(filled($airport->elevation)): ?> <span class="ml-2 text-muted"><?php echo e('| ' . $airport->elevation . 'ft'); ?></span><?php endif; ?></h4>
            <table class="table table-striped table-hover mb-0">
               <tbody>
                  <tr>
                     <td class="fw-bold"><?php echo app('translator')->get('sptheme.icao_iata_code'); ?></td>
                     <td class="text-end"><?php echo e($airport->icao.' / '.$airport->iata); ?></td>
                  </tr>
                  <tr>
                     <td class="fw-bold"><?php echo app('translator')->get('user.location'); ?> / <?php echo app('translator')->get('common.country'); ?></td>
                     <td class="text-end"><span class="fi fi-<?php echo e(strtolower($airport->country)); ?> shadow-img me-1"></span> <?php echo e($airport->location.' / '.$airport->country); ?></td>
                  </tr>
                  <?php if(filled($airport->timezone)): ?>
                  <tr>
                     <td class="fw-bold"><?php echo app('translator')->get('common.timezone'); ?></td>
                     <td class="text-end"><?php echo e($airport->timezone); ?></td>
                  </tr>
                  <?php endif; ?>
                  <?php if($airport->ground_handling_cost > 0): ?>
                  <tr>
                     <td class="fw-bold"><?php echo app('translator')->get('sptheme.gh_cost'); ?></td>
                     <td class="text-end"><?php echo e(number_format($airport->ground_handling_cost).' / '.trans_choice('common.flight',1)); ?></td>
                  </tr>
                  <?php endif; ?>
                  <?php if($airport->fuel_mogas_cost > 0): ?>
                  <tr>
                     <td class="fw-bold"><?php echo app('translator')->get('sptheme.mogas_cost'); ?></td>
                     <td class="text-end"><?php echo e($airport->fuel_mogas_cost . ' '. $currency); ?></td>
                  </tr>
                  <?php endif; ?>
                  <?php if($airport->fuel_100ll_cost > 0): ?>
                  <tr>
                     <td class="fw-bold"><?php echo app('translator')->get('sptheme.100ll_cost'); ?></td>
                     <td class="text-end"><?php echo e($airport->fuel_100ll_cost . ' '. $currency); ?></td>
                  </tr>
                  <?php endif; ?>
                  <?php if($airport->fuel_jeta_cost > 0): ?>
                  <tr>
                     <td class="fw-bold"><?php echo app('translator')->get('sptheme.jeta1_cost'); ?></td>
                     <td class="text-end"><?php echo e($airport->fuel_jeta_cost . ' '. $currency); ?></td>
                  </tr>
                  <?php endif; ?>
               </tbody>
            </table>
            <?php if(filled($airport->notes)): ?>
            <div class="alert alert-info mt-3"><?php echo $airport->notes; ?></div>
            <?php else: ?>
            <div class="alert alert-info mt-3"><?php echo e(trans_choice('common.note', 1)); ?>: -</div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <div class="tab-default">
               <div role="tabpanel">
                  <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                     <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#map" role="tab" aria-selected="true"><i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo e(__('common.map')); ?></a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#wxmap" role="tab" aria-selected="false" tabindex="-1"><i class="ph-fill ph-list align-middle fs-20 me-1"></i>WX <?php echo e(__('common.map')); ?></a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#weather" role="tab" aria-selected="false" tabindex="-1"><i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.weather'); ?></a>
                     </li>
                     <?php if(count($airport->files) > 0 && Auth::check()): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#downloads" role="tab" aria-selected="false" tabindex="-1"><i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo e(trans_choice('common.download', 2)); ?></a>
                     </li>
                     <?php endif; ?>
                  </ul>
                  <div class="tab-content">
                     <div class="tab-pane active show" id="map" role="tabpanel">
                        <?php echo e(Widget::AirspaceMap(['width' => '100%', 'height' => '400px', 'lat' => $airport->lat, 'lon' => $airport->lon])); ?>

                     </div>
                     <div class="tab-pane" id="wxmap" role="tabpanel">
                        <iframe
                           id="windyframe" height="400px" width="100%" src="https://embed.windy.com/embed2.html?lat=<?php echo e($airport->lat); ?>&lon=<?php echo e($airport->lon); ?>&detailLat=<?php echo e($airport->lat); ?>&detailLon=<?php echo e($airport->lon); ?>&zoom=5&marker=1&level=surface&overlay=thunder&product=ecmwf&calendar=now&message=true&pressure=true&type=map&location=coordinates&metricWind=kt&metricTemp=%C2%B0C&radarRange=-1"
                           frameborder="0">
                        </iframe>
                     </div>
                     <div class="tab-pane" id="weather" role="tabpanel">
                        <?php echo e(Widget::Weather(['icao' => $airport->icao])); ?>

                     </div>
                     <?php if(count($airport->files) > 0 && Auth::check()): ?>
                     <div class="tab-pane" id="downloads" role="tabpanel">
                        <?php echo $__env->make('downloads.table', ['files' => $airport->files], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     </div>
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.inbound'); ?></h4>
            <?php if(!$inbound_flights): ?>
            <div class="alert alert-danger"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('flights.none'); ?></div>
            <?php else: ?>
            <div class="dz-scroll" style="max-height:400px;">
               <table class="table table-striped table-hover mb-0">
                  <thead>
                     <tr>
                        <th class="text-left"><?php echo app('translator')->get('airports.ident'); ?></th>
                        <th class="text-left"><?php echo app('translator')->get('airports.departure'); ?></th>
                        <th><?php echo app('translator')->get('flights.dep'); ?></th>
                        <th><?php echo app('translator')->get('flights.arr'); ?></th>
                     </tr>
                  </thead>
                  <?php $__currentLoopData = $inbound_flights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                     <td class="text-left">
                        <a href="<?php echo e(route('frontend.flights.show', [$flight->id])); ?>" title="<?php echo app('translator')->get('pireps.flightinformations'); ?>" class="tooltiptop">
                           <i class="ph-fill ph-hash align-middle fs-20 me-1"></i><?php echo e($flight->ident); ?>

                        </a>
                     </td>
                     <td class="text-left"><?php echo e(optional($flight->dpt_airport)->name); ?>

                        (<a href="<?php echo e(route('frontend.airports.show', ['id' => $flight->dpt_airport_id])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e($flight->dpt_airport_id); ?></a>)
                     </td>
                     <td><?php echo e($flight->dpt_time); ?></td>
                     <td><?php echo e($flight->arr_time); ?></td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </table>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-takeoff align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.outbound'); ?></h4>
            <?php if(!$outbound_flights): ?>
            <div class="alert alert-danger"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('flights.none'); ?></div>
            <?php else: ?>
            <div class="dz-scroll" style="max-height:400px;">
               <table class="table table-striped table-hover mb-0">
                  <thead>
                     <tr>
                        <th class="text-left"><?php echo app('translator')->get('airports.ident'); ?></th>
                        <th class="text-left"><?php echo app('translator')->get('airports.arrival'); ?></th>
                        <th><?php echo app('translator')->get('flights.dep'); ?></th>
                        <th><?php echo app('translator')->get('flights.arr'); ?></th>
                     </tr>
                  </thead>
                  <?php $__currentLoopData = $outbound_flights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                     <td class="text-left">
                        <a href="<?php echo e(route('frontend.flights.show', [$flight->id])); ?>" title="<?php echo app('translator')->get('pireps.flightinformations'); ?>" class="tooltiptop">
                           <i class="ph-fill ph-hash align-middle fs-20 me-1"></i><?php echo e($flight->ident); ?>

                        </a>
                     </td>
                     <td class="text-left"><?php echo e($flight->arr_airport->name); ?>

                        (<a href="<?php echo e(route('frontend.airports.show', ['id' => $flight->arr_airport->icao])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e($flight->arr_airport->icao); ?></a>)
                     </td>
                     <td><?php echo e($flight->dpt_time); ?></td>
                     <td><?php echo e($flight->arr_time); ?></td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </table>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/airports/show.blade.php ENDPATH**/ ?>