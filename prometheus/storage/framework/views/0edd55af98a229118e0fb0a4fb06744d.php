<div class="row">
  <div class="col-12">
    <div class="box-body">
      <div id="map" style="width: 100%; height: 800px"></div>
    </div>
  </div>
</div>
<?php $__env->startSection('scripts'); ?>
  <script type="text/javascript">
    phpvms.map.render_route_map({
      pirep_uri: '<?php echo url('/api/pireps/'.$pirep->id.'/acars/geojson'); ?>',
      route_points: <?php echo json_encode($map_features['planned_rte_points']); ?>,
      planned_route_line: <?php echo json_encode($map_features['planned_rte_line']); ?>,
      actual_route_line: <?php echo json_encode($map_features['actual_route_line']); ?>,
      actual_route_points: <?php echo json_encode($map_features['actual_route_points']); ?>,
      aircraft_icon: '<?php echo public_asset('/assets/img/acars/aircraft.png'); ?>',
      flown_route_color: '#f7b84b',
      circle_color: '#f7b84b',
      flightplan_route_color: '#518ce5',
      leafletOptions: {
        scrollWheelZoom: true,
        providers: {'CartoDB.Positron': {},}
      },
    });
  </script>
<?php $__env->stopSection(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/map.blade.php ENDPATH**/ ?>