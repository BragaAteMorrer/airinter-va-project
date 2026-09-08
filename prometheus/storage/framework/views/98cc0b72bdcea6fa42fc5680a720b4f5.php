<div class="col mb-3">
   <div class="card border <?php if($tour->tour_token > 0 && isset($user_tokens) && !in_array($tour->tour_token, $user_tokens)): ?> opacity-75 <?php endif; ?>">
      <div class="card-body">
         <h4 class="mt-0 header-title border-bottom">
            <?php if($carbon_now < $tour->start_date): ?>
               <i class="ph-duotone ph-warning mx-2 fs-4 text-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo app('translator')->get('DSpecial::tours.iconnoty'); ?>"></i>
               <?php endif; ?>
               <?php if($carbon_now > $tour->end_date): ?>
               <i class="ph-duotone ph-warning mx-2 fs-4 text-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo app('translator')->get('DSpecial::tours.iconend'); ?>"></i>
               <?php endif; ?>
               <?php if(Request::path() == 'dtours' && $carbon_now > $tour->start_date): ?>
               <a href="<?php echo e(route('DSpecial.tour', [$tour->tour_code])); ?>" data-bs-toggle="tooltip" data-bs-placement="top" title="More about this tour"><?php echo e($tour->tour_name); ?></a>
               <?php else: ?>
               <?php echo e($tour->tour_name); ?>

            <?php endif; ?>
            <p class="text-muted fw-normal small m-0"><?php echo e($tour->start_date->format('d. F Y')); ?> - <?php echo e($tour->end_date->format('d. F Y')); ?></p>
         </h4>
         <img class="card-img img-fluid" src="<?php echo e(public_asset('/SPTheme/images/tours')); ?>/<?php echo e(strtolower($tour->tour_code)); ?>.jpg" alt="<?php echo e($tour->tour_name); ?>">
         <p class="form-bg-grey rounded my-3 p-3"><?php echo e(strip_tags($tour->tour_desc)); ?></p>
         <div class="row g-3 text-center">
            <div class="col-4">
               <h5 class="mb-0 fw-bold"><?php if(!$tour->airline): ?> <?php echo app('translator')->get('DSpecial::tours.topen'); ?> <?php else: ?> <?php echo app('translator')->get('DSpecial::tours.tairline'); ?> <?php endif; ?></h5>
               <p class="text-muted"><?php echo app('translator')->get('DSpecial::tours.ttype'); ?></p>
            </div>
            <div class="col-4 border border-top-0 border-bottom-0">
               <h5 class="mb-0 fw-bold"><?php echo e($tour->tour_code); ?></h5>
               <p class="text-muted"><?php echo app('translator')->get('DSpecial::tours.tcode'); ?></p>
            </div>
            <div class="col-4">
               <h5 class="mb-0 fw-bold"><?php echo e($tour->legs_count); ?></h5>
               <p class="text-muted"><?php echo app('translator')->get('DSpecial::tours.tlegs'); ?></p>
            </div>
         </div>
         <?php if($tour->tour_token > 0 && isset($user_tokens) && !in_array($tour->tour_token, $user_tokens)): ?>
         <div class="d-flex mt-3">
            <a href="<?php echo e(route('DSpecial.tour', [$tour->tour_code])); ?>" class="btn btn-warning flex-fill mx-1"><?php echo app('translator')->get('sptheme.tourtoken'); ?></a>
         </div>
         <?php else: ?>
            <?php if($carbon_now > $tour->end_date || $carbon_now < $tour->start_date): ?>
               <?php if($carbon_now < $tour->start_date): ?>
                  <div class="d-flex mt-3">
                     <span class="btn btn-warning flex-fill mx-1"><?php echo app('translator')->get('DSpecial::tours.iconnoty'); ?></span>
                  </div>
               <?php endif; ?>
               <?php if($carbon_now > $tour->end_date): ?>
                  <div class="d-flex mt-3">
                     <span class="btn btn-danger flex-fill mx-1"><?php echo app('translator')->get('DSpecial::tours.iconend'); ?></span>
                  </div>
               <?php endif; ?>
            <?php else: ?>
               <?php if(Request::path() == 'dtours'): ?>
                  <div class="d-flex mt-3">
                     <a href="<?php echo e(route('DSpecial.tour', [$tour->tour_code])); ?>" class="btn btn-success flex-fill mx-1"><?php echo app('translator')->get('sptheme.jointour'); ?></a>
                  </div>
               <?php endif; ?>
            <?php endif; ?>
         <?php endif; ?>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/tours/table.blade.php ENDPATH**/ ?>