<div class="card border">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-map-trifold align-middle fs-20 me-1"></i><?php echo e(trans_choice('common.flight', 2)); ?> <?php echo app('translator')->get('common.map'); ?>
         <?php if(filled($flight->route)): ?>
            <a href="http://skyvector.com/?chart=304&fpl=<?php echo e($flight->dpt_airport_id); ?> <?php echo e($flight->route); ?> <?php echo e($flight->arr_airport_id); ?>" class="btn btn-light float-end" target="_blank">View at SkyVector</a>
         <?php endif; ?>
      </h4>
      <div id="map" style="height: 600px"></div>
   </div>
   <?php if(filled($flight->route)): ?>
   <div class="card-footer" style="background-color: var(--bs-body-bg) !important;">
      <b><i class="ph-fill ph-line-segments me-1"></i><?php echo app('translator')->get('flights.route'); ?>:</b> <?php echo e($flight->route); ?>

   </div>
   <?php endif; ?>
</div>
<?php $__env->startSection('scripts'); ?>
<script type="text/javascript">
   phpvms.map.render_route_map({
      route_points: <?php echo json_encode($map_features['route_points']); ?>,
      planned_route_line: <?php echo json_encode($map_features['planned_route_line']); ?>,
      circle_color: '#067ec1',
      flightplan_route_color: '#067ec1',
      leafletOptions: {
         scrollWheelZoom: true,
         providers: {
            'CartoDB.Positron': {},
         }
      }
   });
</script>
<?php $__env->stopSection(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flights/map.blade.php ENDPATH**/ ?>