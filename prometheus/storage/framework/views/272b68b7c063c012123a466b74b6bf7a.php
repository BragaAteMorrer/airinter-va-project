<?php $__env->startSection('title', 'Tour Details'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/39.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php echo $__env->make('DSpecial::tours.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <div class="card border mb-0">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-trophy align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.tawards'); ?>
               <span class="fw-normal small float-end"><?php echo app('translator')->get('sptheme.first10'); ?></span>
            </h4>
            <?php if(filled($tour_awards)): ?>
            <table class="table table-hover table-striped mb-0">
               <tr>
                  <th></th>
                  <th>Pilot</th>
                  <th class="text-end"><?php echo app('translator')->get('sptheme.finat'); ?></th>
               </tr>
               <?php $__currentLoopData = $tour_awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <?php if(filled($ta->user)): ?>
               <tr class="align-middle">
                  <td><?php echo e($loop->iteration); ?></td>
                  <td><a href="<?php echo e(route('frontend.profile.show', [$ta->user->id])); ?>"> <?php if(Theme::getSetting('roster_ident')): ?> <?php echo e($ta->user->ident.' - '); ?> <?php endif; ?> <?php echo e($ta->user->name_private); ?></a></td>
                  <td class="text-end"><?php echo e($ta->created_at->format('d F Y H:i')); ?></td>
               </tr>
               <?php endif; ?>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
            <?php else: ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.noaward'); ?></div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="tab-default">
         <div role="tabpanel">
            <div class="card border">
               <div class="card-body">
                  <ul class="nav nav-tabs nav-justified" role="tablist">
                     <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#legs" role="tab" aria-selected="true">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.legs'); ?>
                        </a>
                     </li>
                     <?php if(filled($tour->tour_rules)): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#trules" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.trules'); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                     <?php if($tour->legs_count > 0): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#tmap" role="tab" aria-selected="false" tabindex="-1" onclick="ExpandTourMap()">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.tmap'); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link me-0" data-bs-toggle="tab" href="#treport" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.treport'); ?>
                        </a>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="tab-content text-muted">
               <div class="tab-pane active show" id="legs" role="tabpanel">
                  <div class="card border mb-0">
                     <div class="card-body">
                        <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-line-segments align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.legs'); ?>
                           <span class="fw-normal small float-end"><?php echo app('translator')->get('DSpecial::tours.tlegs'); ?> : <?php echo e($tour->legs->count()); ?></span>
                        </h4>
                        <?php echo $__env->make('DSpecial::tours.legs_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     </div>
                  </div>
               </div>
               <?php if(filled($tour->tour_rules)): ?>
               <div class="tab-pane" id="trules" role="tabpanel">
                  <div class="card border mb-0">
                     <div class="card-body">
                        <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-book-bookmark align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.trules'); ?></h4>
                        <?php echo $tour->tour_rules; ?>

                     </div>
                  </div>
               </div>
               <?php endif; ?>
               <?php if($tour->legs_count > 0): ?>
               <div class="tab-pane" id="tmap" role="tabpanel">
                  <div class="card border mb-0">
                     <div class="card-body">
                        <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-map-trifold align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.tmap'); ?>
                           <span class="float-end fw-normal small"><?php echo app('translator')->get('sptheme.airports'); ?>: <?php echo e(count($mapAirports)); ?>, <?php echo app('translator')->get('DSpecial::tours.legs'); ?>: <?php echo e(count($mapFlights)); ?></span>
                        </h4>
                        <div id="tourmap" style="width: 100%; height: 600px"></div>
                     </div>
                  </div>
               </div>
               <?php endif; ?>
               <div class="tab-pane" id="treport" role="tabpanel">
                  <div class="card border mb-0">
                     <div class="card-body table-responsive">
                        <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-file-arrow-down align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.treport'); ?></h4>
                        <?php echo $__env->make('DSpecial::tours.report_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php if($tour->legs_count > 0): ?>
<script type="text/javascript">
  function ExpandTourMap() {
    // Define Icons
    var vmsIcon = new L.Icon(<?php echo $mapIcons['vmsIcon']; ?>);
    var RedIcon = new L.Icon(<?php echo $mapIcons['RedIcon']; ?>);
    var GreenIcon = new L.Icon(<?php echo $mapIcons['GreenIcon']; ?>);
    var BlueIcon = new L.Icon(<?php echo $mapIcons['BlueIcon']; ?>);
    var YellowIcon = new L.Icon(<?php echo $mapIcons['YellowIcon']; ?>);
    // Define Geodesic Line Colors
    var Flown = '#28C76F';
    var NotFlown = '#2980b9';
    var CheckDisabled = '#EA5455';
    // Build Airports Layer
    var mBoundary = L.featureGroup();
    var mAirports = L.layerGroup();
    <?php $__currentLoopData = $mapAirports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      var APT_<?php echo e($airport['id']); ?> = L.marker([<?php echo e($airport['loc']); ?>], {icon: <?php echo e($airport['icon']); ?> , opacity: 0.8}).bindPopup(<?php echo "'".$airport['pop']."'"; ?>).addTo(mAirports).addTo(mBoundary);
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    // Build Flights (Legs) Layer
    var mFlights = L.layerGroup();
    <?php $__currentLoopData = $mapFlights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      var FLT_<?php echo e($flight['id']); ?> = L.geodesic(<?php echo e($flight['geod']); ?>, {weight: 4, opacity: 0.8, steps: 5, color: <?php echo e($flight['geoc']); ?>}).bindPopup(<?php echo "'".$flight['pop']."'"; ?>).addTo(mFlights);
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    // Define Base Layers For Control Box
    var Default = L.tileLayer.provider('CartoDB.Positron');
    // Define Control Groups
    var BaseLayers = {'Default': Default};
    var Overlays = {"Tour Airports": mAirports, "Tour Legs": mFlights};
    // Define Map and Add Control Box
    var TourMap = L.map('tourmap', {center: <?php echo e($mapCenter); ?>, layers: [Default, mAirports, mFlights]}).fitBounds(mBoundary.getBounds().pad(0.2));
    L.control.layers(BaseLayers, Overlays).addTo(TourMap);
    setTimeout(function(){ TourMap.invalidateSize().fitBounds(mBoundary.getBounds().pad(0.2))}, 300);
  }
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/tours/show.blade.php ENDPATH**/ ?>