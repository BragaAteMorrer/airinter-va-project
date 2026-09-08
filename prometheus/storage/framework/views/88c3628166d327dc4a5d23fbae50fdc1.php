<?php $__env->startSection('title', __('DBasic::common.hdetails')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/25.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-5 col-xl-5 col-lg-6 col-md-6 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-house align-middle fs-20 me-1"></i><?php echo e($hub->name); ?></h4>
            <table class="table table-hover table-striped mb-0">
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.icao'); ?></th>
                  <td class="text-end"><?php echo e($hub->icao); ?></td>
               </tr>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.iata'); ?></th>
                  <td class="text-end"><?php echo e($hub->iata ?? '--'); ?></td>
               </tr>
               <tr>
                  <th><?php echo app('translator')->get('user.location'); ?> / <?php echo app('translator')->get('common.country'); ?></th>
                  <td class="text-end"><span class="fi fi-<?php echo e(strtolower($hub->country)); ?> shadow-img me-1"></span> <?php echo e($hub->location.' / '.$hub->country); ?></td>
               </tr>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.timezone'); ?></th>
                  <td class="text-end"><?php echo e($hub->timezone); ?></a></td>
               </tr>
               <?php if($hub->ground_handling_cost > 0): ?>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.groundhc'); ?></th>
                  <td class="text-end"><?php echo e(number_format($hub->ground_handling_cost).' '.$units['currency']); ?></td>
               </tr>
               <?php endif; ?>
               <?php if($hub->fuel_100ll_cost > 0): ?>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.fuelc'); ?> | 100LL</th>
                  <td class="text-end"><?php echo e(DB_FuelCost($hub->fuel_100ll_cost, $units['fuel'], $units['currency'])); ?>

               </tr>
               <?php endif; ?>
               <?php if($hub->fuel_mogas_cost > 0): ?>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.fuelc'); ?> | MOGAS</th>
                  <td class="text-end"><?php echo e(DB_FuelCost($hub->fuel_mogas_cost, $units['fuel'], $units['currency'])); ?></td>
               </tr>
               <?php endif; ?>
               <?php if($hub->fuel_jeta_cost > 0): ?>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.fuelc'); ?> | JETA1</th>
                  <td class="text-end"><?php echo e(DB_FuelCost($hub->fuel_jeta_cost, $units['fuel'], $units['currency'])); ?></td>
               </tr>
               <?php endif; ?>
            </table>
            <?php if(filled($hub->notes)): ?>
            <div class="alert alert-info mt-3"><?php echo $hub->notes; ?></div>
            <?php else: ?>
            <div class="alert alert-info mt-3"><?php echo e(trans_choice('common.note', 1)); ?>: -</div>
            <?php endif; ?>
         </div>
      </div>
      <?php if(filled($sundetails)): ?>
      <?php echo app('arrilot.widget')->run('DBasic::SunriseSunset', ['location' => $hub->id, 'card' => true]); ?>
      <?php endif; ?>
   </div>
   <div class="col-xxl-7 col-xl-7 col-lg-6 col-md-6 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <?php if($is_visible['flights']): ?>
            <?php echo app('arrilot.widget')->run('DBasic::Map', ['source' => $hub->id]); ?>
            <?php endif; ?>
            <div class="tab-default mt-3">
               <div role="tabpanel">
                  <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                     <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#map" role="tab" aria-selected="true">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.map'); ?>
                        </a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#wxmap" role="tab" aria-selected="false">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i>WX <?php echo e(__('common.map')); ?>

                        </a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#weather" role="tab" aria-selected="false">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.weather'); ?>
                        </a>
                     </li>
                  </ul>
                  <div class="tab-content text-muted">
                     <div class="tab-pane active show" id="map" role="tabpanel">
                        <?php echo e(Widget::AirspaceMap(['width' => '100%', 'height' => '670px', 'lat' => $hub->lat, 'lon' => $hub->lon,])); ?>

                     </div>
                     <div class="tab-pane" id="wxmap" role="tabpanel">
                        <iframe id="windyframe" height="664" width="100%" src="https://embed.windy.com/embed2.html?lat=<?php echo e($hub->lat); ?>&lon=<?php echo e($hub->lon); ?>&detailLat=<?php echo e($hub->lat); ?>&detailLon=<?php echo e($hub->lon); ?>&zoom=5&marker=1&level=surface&overlay=thunder&product=ecmwf&calendar=now&message=true&pressure=true&type=map&location=coordinates&metricWind=kt&metricTemp=%C2%B0C&radarRange=-1" frameborder="0">
                        </iframe>
                     </div>
                     <div class="tab-pane" id="weather" role="tabpanel">
                        <?php echo e(Widget::Weather(['icao' => $hub->icao])); ?>

                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-6 col-sm-12">
      <div class="card border">
         <div class="card-body">
            <div class="tab-default">
               <div role="tabpanel">
                  <ul class="nav nav-tabs nav-justified" role="tablist">
                     <?php if($is_visible['pilots']): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#pilots" role="tab" aria-selected="true">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.pilots'); ?>
                        </a>
                     </li>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#leaderboard" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::widgets.leader_board'); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                     <?php if($is_visible['aircraft']): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#aircraft" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.aircraft'); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                     <?php if($is_visible['flights']): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link me-0" data-bs-toggle="tab" href="#flights" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.flights'); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                     <?php if($is_visible['reports']): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link me-0" data-bs-toggle="tab" href="#reports" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.pireps'); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                  </ul>
               </div>
            </div>
         </div>
      </div>
      <div class="tab-content text-muted">
         <?php if($is_visible['pilots']): ?>
         <div class="tab-pane active show" id="pilots" role="tabpanel">
            <?php echo $__env->make('DBasic::hubs.show_pilots', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
         <div class="tab-pane" id="leaderboard" role="tabpanel">
            <div class="row">
               <div class="col">
                  <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['hub' => $hub->id, 'source' => 'pilot', 'count' => 5, 'type' => 'flights']); ?>
               </div>
               <div class="col">
                  <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['hub' => $hub->id, 'source' => 'pilot', 'count' => 5, 'type' => 'time']); ?>
               </div>
               <div class="col">
                  <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['hub' => $hub->id, 'source' => 'pilot', 'count' => 5, 'type' => 'lrate']); ?>
               </div>
            </div>
         </div>
         <?php endif; ?>
         <?php if($is_visible['aircraft']): ?>
         <div class="tab-pane" id="aircraft" role="tabpanel">
            <?php echo $__env->make('DBasic::hubs.show_fleet', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
         <?php endif; ?>
         <?php if($is_visible['flights']): ?>
         <div class="tab-pane" id="flights" role="tabpanel">
            <?php echo $__env->make('DBasic::hubs.show_flights', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
         <?php endif; ?>
         <?php if($is_visible['reports']): ?>
         <div class="tab-pane" id="reports" role="tabpanel">
            <?php echo $__env->make('DBasic::hubs.show_reports', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
         <?php endif; ?>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/hubs/show.blade.php ENDPATH**/ ?>