<?php if(isset($flights) && $flights > 0 || !isset($flights) && count($mapAirports) > 0 || !isset($flights) && count($mapHubs) > 0 || isset($sceneries)): ?>

<div class="row">
  <div class="col d-grid">
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="<?php echo e('#modal'.$mapsource); ?>" onclick="<?php echo e($mapsource); ?>ExpandMap()">
    <?php if($mapsource === 'user'): ?>
    <?php echo app('translator')->get('DBasic::widgets.personal_map'); ?>
    <?php elseif($mapsource === 'fleet'): ?>
    <?php echo app('translator')->get('DBasic::widgets.fleet_map'); ?>
    <?php elseif($mapsource === 'airline'): ?>
    <?php echo app('translator')->get('DBasic::widgets.airline_map'); ?>
    <?php elseif($mapsource === 'assignment'): ?>
    <?php echo app('translator')->get('DBasic::widgets.assignm_map'); ?>
    <?php elseif($mapsource === 'aerodromes'): ?>
    <?php echo app('translator')->get('DBasic::widgets.aerodr_map'); ?>
    <?php elseif($mapsource === 'scenery'): ?>
    My Sceneries
    <?php else: ?>
    <?php echo app('translator')->get('DBasic::widgets.flights_map'); ?>
    <?php endif; ?>
    </button>
  </div>
</div>
<div class="modal fade" id="modal<?php echo e($mapsource); ?>" tabindex="-1" aria-labelledby="modal<?php echo e($mapsource); ?>Label" aria-hidden="true">
  <div class="modal-dialog modal-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="<?php echo e($mapsource.'Title'); ?>"><i class="ph-fill ph-map-trifold"></i>
          <?php if($mapsource === 'user'): ?>
          <?php echo app('translator')->get('DBasic::widgets.personal_map'); ?>
          <?php elseif($mapsource === 'fleet'): ?>
          <?php echo app('translator')->get('DBasic::widgets.fleet_map'); ?>
          <?php elseif($mapsource === 'airline'): ?>
          <?php echo app('translator')->get('DBasic::widgets.airline_map'); ?>
          <?php elseif($mapsource === 'assignment'): ?>
          <?php echo app('translator')->get('DBasic::widgets.assignm_map'); ?>
          <?php elseif($mapsource === 'aerodromes'): ?>
          <?php echo app('translator')->get('DBasic::widgets.aerodr_map'); ?>
          <?php elseif($mapsource === 'scenery'): ?>
          My Sceneries
          <?php else: ?>
          <?php echo app('translator')->get('DBasic::widgets.flights_map'); ?>
          <?php endif; ?>
        </h1>
        <div class="text-white float-end">
          <?php if(isset($sceneries)): ?>
          Sceneries: <?php echo e($sceneries); ?>

          <?php endif; ?>
          <?php if(count($mapCityPairs) > 0): ?>
          <?php echo app('translator')->get('DBasic::widgets.citypairs'); ?>: <?php echo e(count($mapCityPairs)); ?> |
          <?php endif; ?>
          <?php echo app('translator')->get('DBasic::widgets.hubs'); ?>: <?php echo e(count($mapHubs)); ?> |
          <?php echo app('translator')->get('DBasic::widgets.airports'); ?>: <?php echo e(count($mapAirports)); ?>

          <?php if(count($mapHubs) > 0): ?>
          <?php echo app('translator')->get('DBasic::widgets.hubs'); ?>: <?php echo e(count($mapHubs)); ?> |
          <?php endif; ?>
          <?php if(count($mapAirports) > 0): ?>
          <?php echo app('translator')->get('DBasic::widgets.airports'); ?>: <?php echo e(count($mapAirports)); ?>

          <?php endif; ?>
          <?php if(isset($flights)): ?>
          | <?php echo e(trans_choice('common.flight', 2)); ?>: <?php echo e($flights); ?>

          <?php endif; ?>
          <?php if(isset($aircraft)): ?>
          | <?php echo app('translator')->get('common.aircraft'); ?>: <?php echo e($aircraft); ?>

          <?php endif; ?>
        </div>
      </div>
      <div class="modal-body custom_scroll">
        <div id="<?php echo e($mapsource); ?>" style="width: 100%; height: 80vh;"></div>
      </div>
    </div>
  </div>
</div>
<?php $__env->startSection('scripts'); ?>
<?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>

<script type="text/javascript">
      function <?php echo e($mapsource); ?>ExpandMap() {
        // Icons
        var BlueIcon = new L.Icon(<?php echo $mapIcons['BlueIcon']; ?>);
        var GoldIcon = new L.Icon(<?php echo $mapIcons['GoldIcon']; ?>);
        var GreenIcon = new L.Icon(<?php echo $mapIcons['GreenIcon']; ?>);
        var GreyIcon = new L.Icon(<?php echo $mapIcons['GreyIcon']; ?>);
        var OrangeIcon = new L.Icon(<?php echo $mapIcons['OrangeIcon']; ?>);
        var RedIcon = new L.Icon(<?php echo $mapIcons['RedIcon']; ?>);
        var VioletIcon = new L.Icon(<?php echo $mapIcons['VioletIcon']; ?>);
        var YellowIcon = new L.Icon(<?php echo $mapIcons['YellowIcon']; ?>);
        // Map Boundary
        var mBoundary = L.featureGroup();
        // Hubs
        <?php if(count($mapHubs) > 0): ?>
          var mHubs = L.layerGroup();
          <?php $__currentLoopData = $mapHubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var HUB_<?php echo e($hub['id']); ?> = L.marker([<?php echo e($hub['loc']); ?>], {icon: GreenIcon , opacity: 0.8}).bindPopup(<?php echo "'".$hub['pop']."'"; ?>).addTo(mHubs).addTo(mBoundary);
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // Airports
        <?php if(count($mapAirports) > 0): ?>
          var mAirports = L.layerGroup();
          <?php $__currentLoopData = $mapAirports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var APT_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: BlueIcon , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mAirports)<?php if($mapsource === 'aerodromes' && $loop->first || $mapsource === 'aerodromes' && $loop->last): ?>.addTo(mBoundary)<?php elseif($mapsource != 'aerodromes'): ?>.addTo(mBoundary)<?php endif; ?>;
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // FS9 Sceneries
        <?php if(count($mapFS9) > 0): ?>
          var mFS9 = L.layerGroup();
          <?php $__currentLoopData = $mapFS9; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var FS9_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: RedIcon , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mFS9).addTo(mBoundary);
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // FSX Sceneries
        <?php if(count($mapFSX) > 0): ?>
          var mFSX = L.layerGroup();
          <?php $__currentLoopData = $mapFSX; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var FSX_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: BlueIcon , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mFSX).addTo(mBoundary);
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // P3D Sceneries
        <?php if(count($mapP3D) > 0): ?>
          var mP3D = L.layerGroup();
          <?php $__currentLoopData = $mapP3D; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var P3D_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: GoldIcon , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mP3D).addTo(mBoundary);
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // XP Sceneries
        <?php if(count($mapXP) > 0): ?>
          var mXP = L.layerGroup();
          <?php $__currentLoopData = $mapXP; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var XP_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: VioletIcon , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mXP).addTo(mBoundary);
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // MSFS Sceneries
        <?php if(count($mapMSFS) > 0): ?>
          var mMSFS = L.layerGroup();
          <?php $__currentLoopData = $mapMSFS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var MSFS_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: OrangeIcon , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mMSFS).addTo(mBoundary);
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // OTHER Sceneries
        <?php if(count($mapOTHER) > 0): ?>
          var mOTHER = L.layerGroup();
          <?php $__currentLoopData = $mapOTHER; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            var OTHER_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: GreyIcon , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mOTHER).addTo(mBoundary);
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // City Pairs / Flights Layer Group
        <?php if(count($mapCityPairs) > 0): ?>
          var mFlights = L.layerGroup();
          <?php $__currentLoopData = $mapCityPairs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $citypair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($citypair['pop']): ?>
              var FLT_<?php echo e($citypair['name']); ?> = L.geodesic([<?php echo e($citypair['geod']); ?>], {weight: 2, opacity: 0.8, steps: 5, color: '<?php echo e($citypair['geoc']); ?>'}).bindPopup(<?php echo "'".$citypair['pop']."'"; ?>).addTo(mFlights);
            <?php else: ?>
              var FLT_<?php echo e($citypair['name']); ?> = L.geodesic([<?php echo e($citypair['geod']); ?>], {weight: 2, opacity: 0.8, steps: 5, color: '<?php echo e($citypair['geoc']); ?>'}).addTo(mFlights);
            <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        // Define Base Layers For Control Box
        var SPTheme = L.tileLayer.provider('CartoDB.Positron');
        // Define Additional Overlay Layers
        var OpenAIP = L.
          tileLayer('http://{s}.tile.maps.openaip.net/geowebcache/service/tms/1.0.0/openaip_basemap@EPSG%3A900913@png/{z}/{x}/{y}.{ext}', {
          attribution: '<a href="https://www.openaip.net/">openAIP Data</a> (<a href="https://creativecommons.org/licenses/by-sa/3.0/">CC-BY-NC-SA</a>)',
          ext: 'png',
          minZoom: 4,
          maxZoom: 14,
          tms: true,
          detectRetina: true,
          subdomains: '12'
        });
        // Define Control Groups
        var BaseLayers = {'SPTheme': SPTheme};
        var Overlays = <?php echo $mapOverlays; ?>;
        // Define Map and Add Control Box
        var <?php echo e($mapsource); ?> = L.map('<?php echo e($mapsource); ?>', {center: [<?php echo e($mapcenter); ?>], layers: [SPTheme, <?php echo $mapLayers; ?>], scrollWheelZoom: false}).fitBounds(mBoundary.getBounds().pad(0.2));;
        L.control.layers(BaseLayers, Overlays).addTo(<?php echo e($mapsource); ?>);
        // TimeOut to ReDraw The Map in Modal
        setTimeout(function(){ <?php echo e($mapsource); ?>.invalidateSize().fitBounds(mBoundary.getBounds().pad(0.2))}, 300);
      }
    </script>

<?php $__env->stopSection(); ?>
<?php endif; ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/map.blade.php ENDPATH**/ ?>