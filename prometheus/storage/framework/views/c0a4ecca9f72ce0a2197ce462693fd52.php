<div id="map" style="width: <?php echo e($config['width']); ?>; height: <?php echo e($config['height']); ?>"></div>
<?php $__env->startSection('scripts'); ?>
<script>
   phpvms.map.render_airspace_map({
      lat: "<?php echo e($config['lat']); ?>",
      lon: "<?php echo e($config['lon']); ?>",
      metar_wms: <?php echo json_encode(config('map.metar_wms')); ?>,
      units: '<?php echo e(setting('units.distance')); ?>',
      leafletOptions: {
         scrollWheelZoom: true,
         providers: {'CartoDB.Positron': {},}
      }
   });
</script>
<?php $__env->stopSection(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/widgets/airspace_map.blade.php ENDPATH**/ ?>