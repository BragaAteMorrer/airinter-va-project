<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom">
               <img src="<?php echo e(public_asset('/SPTheme/images/banner/10.jpg')); ?>" class="img-fluid card-img rounded" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            </h4>
            <div id="map" style="width: <?php echo e($config['width']); ?>; height: <?php echo e($config['height']); ?>">
               <div id="map-info-box" class="map-info-box" rv-show="pirep.id" style="width: <?php echo e($config['width']); ?>;">
                  <div style="float: left; width: 50%;">
                     <h4 style="margin: 0" id="map_flight_id">
                        <img rv-src="pirep.airline.logo" width="90" alt="<?php echo app('translator')->get('common.airline'); ?>" class="mb-1 me-2">
                        <?php if(Auth::check()): ?>
                        <a rv-href="pirep.id | prepend '<?php echo e(url('/pireps')); ?>/'" target="_blank" title="<?php echo app('translator')->get('sptheme.acarsdetail'); ?>" class="tooltiptop">{ pirep.airline.icao }{ pirep.flight_number }</a>
                        <?php else: ?>
                        <a href="<?php echo e(url('/login')); ?>" title="<?php echo app('translator')->get('sptheme.acarsdetail'); ?>" class="tooltiptop">{ pirep.airline.icao }{ pirep.flight_number }</a>
                        <?php endif; ?>
                     </h4>
                     <p id="map_flight_info">
                        { pirep.dpt_airport.name } ({ pirep.dpt_airport.icao }) <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i> { pirep.arr_airport.name } ({ pirep.arr_airport.icao })
                        <br>Pilot in Command: <span class="fw-bolder">{ pirep.user.name_private }.</span>
                     </p>
                  </div>
                  <div style="float: right; margin-left: 30px; margin-right: 30px;">
                     <p id="map_flight_stats_right">
                        <?php echo app('translator')->get('widgets.livemap.groundspeed'); ?>: <span style="font-weight: bold">{ pirep.position.gs }</span><br>
                        <?php echo app('translator')->get('widgets.livemap.altitude'); ?>: <span style="font-weight: bold">{ pirep.position.altitude }</span><br>
                        <?php echo app('translator')->get('widgets.livemap.heading'); ?>: <span style="font-weight: bold">{ pirep.position.heading }</span><br>
                     </p>
                  </div>
                  <div style="float: right; margin-left: 30px;">
                     <p id="map_flight_stats_middle">
                        <?php echo app('translator')->get('common.status'); ?>: <span style="font-weight: bold">{ pirep.status_text }</span><br>
                        <?php echo app('translator')->get('flights.flighttime'); ?>: <span style="font-weight: bold">{ pirep.flight_time | time_hm }</span><br>
                        <?php echo app('translator')->get('common.distance'); ?>: <span style="font-weight: bold">{ pirep.position.distance.<?php echo e(setting('units.distance')); ?> }</span> / <span style="font-weight: bold">{ pirep.planned_distance.<?php echo e(setting('units.distance')); ?> }</span>
                     </p>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php if($config['table'] === true): ?>
<div id="live_flights" class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-globe-simple-x align-middle fs-20 me-1"></i><?php echo $__env->yieldContent('title'); ?></h4>
            <div rv-hide="has_data" class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('widgets.livemap.noflights'); ?></div>
            <div class="table-responsive ">
               <table rv-show="has_data" id="live_flights_table" class="table table-striped table-hover">
                  <thead>
                     <tr>
                        <th></th>
                        <th class="text-center"><?php echo e(trans_choice('common.pilot', 1)); ?></th>
                        <th class="text-start"><?php echo e(trans_choice('common.flight', 2)); ?></th>
                        <th class="text-center"><?php echo app('translator')->get('common.departure'); ?> / <?php echo app('translator')->get('common.arrival'); ?></th>
                        <th class="text-center"><?php echo app('translator')->get('common.aircraft'); ?></th>
                        <th class="text-end"><?php echo app('translator')->get('widgets.livemap.altitude'); ?> AGL</th>
                        <th class="text-end"><?php echo app('translator')->get('widgets.livemap.gs'); ?></th>
                        <th class="text-end"><?php echo app('translator')->get('widgets.livemap.distance'); ?></th>
                        <th class="text-center"><?php echo app('translator')->get('common.status'); ?></th>
                     </tr>
                  </thead>
                  <tbody>
                     <tr rv-each-pirep="pireps" class="align-middle">
                        <td class="">
                           <img rv-src="pirep.airline.logo" width="90" data-bs-toggle="tooltip" data-bs-placement="top" rv-title="pirep.airline.name" alt="<?php echo app('translator')->get('common.airline'); ?>"></td>
                        </td>
                        <td class="text-start">
                           <img rv-src="pirep.user.avatar" width="42" height="42" class="rounded-circle bg-primary img-fluid me-3" alt="<?php echo app('translator')->get('profile.avatar'); ?>"><a rv-href="pirep.user.id | prepend '<?php echo e(url('/users/')); ?>/'">{ pirep.user.name_private }</a>
                        </td>
                        <td class="text-start"><a href="#top_anchor" rv-on-click="controller.focusMarker" class="tooltiptop" title="Open details"><i class="ph-fill ph-cell-signal-medium align-text-bottom fs-20 me-1"></i> { pirep.ident }</a></td>
                        <td class="text-center">
                           <a rv-href="pirep.dpt_airport.icao | prepend '<?php echo e(url('/airports/')); ?>/'" class="tooltiptop" data-bs-placement="top" rv-title="pirep.dpt_airport.name"><span class="badge badge-rounded badge-primary"><i class="ph-fill ph-airplane-takeoff mx-1"></i>{ pirep.dpt_airport.icao }</span></a>
                           <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                           <a rv-href="pirep.arr_airport.icao | prepend '<?php echo e(url('/airports/')); ?>/'" class="tooltiptop" data-bs-placement="top" rv-title="pirep.arr_airport.name"><span class="badge badge-rounded badge-primary"><i class="ph-fill ph-airplane-landing mx-1"></i>{ pirep.arr_airport.icao }</span></a>
                        </td>
                        <td class="text-center">{ pirep.aircraft.registration } ({ pirep.aircraft.icao })</td>
                        <td class="text-end">{ pirep.position.altitude_agl } ft</td>
                        <td class="text-end">{ pirep.position.gs } kt</td>
                        <td class="text-end">{ pirep.position.distance.<?php echo e(setting('units.distance')); ?> | fallback 0 } / { pirep.planned_distance.<?php echo e(setting('units.distance')); ?> | fallback 0 } nm</td>
                        <td class="text-end"><span class="badge badge-rounded badge-warning"><i class="ph-fill ph-circle mx-1"></i> { pirep.status_text }<span></td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<?php $__env->startSection('scripts'); ?>
<script>
   phpvms.map.render_live_map({
      center: ['<?php echo e($center[0]); ?>', '<?php echo e($center[1]); ?>'],
      zoom: '<?php echo e($zoom); ?>',
      aircraft_icon: '<?php echo public_asset('/SPTheme/images/aircraft.png '); ?>',
      refresh_interval: <?php echo e(setting('acars.update_interval', 60)); ?>,
      units: '<?php echo e(setting('units.distance ')); ?>',
      flown_route_color: '#067ec1',
      leafletOptions: {
         scrollWheelZoom: true,
         providers: {
            'CartoDB.Positron': {},
         }
      }
   });
</script>
<?php $__env->stopSection(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/widgets/live_map.blade.php ENDPATH**/ ?>