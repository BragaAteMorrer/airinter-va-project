<?php if($awards->count() > 0): ?>
<div class="card border mb-3">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-trophy align-middle fs-20 me-1"></i> <?php echo app('translator')->get('sptheme.latestawards'); ?></h4>
      <?php $__currentLoopData = $awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="card-body p-2 border-bottom">
         <div class="row">
            <div class="col-auto my-auto">
               <div class="me-2">
                  <img src="<?php echo e(optional($a->award)->image_url); ?>" class="rounded-circle bg-primary img-fluid" width="42" height="42" alt="<?php echo e(optional($a->award)->name); ?>">
               </div>
            </div>
            <div class="col">
               <?php if(Auth::check()): ?>
               <h5 class="my-1"><span class="fi fi-<?php echo e(optional($a->user)->country); ?> shadow-img me-1" title="<?php echo app('translator')->get('common.country'); ?>"></span> <a href="<?php echo e(route('frontend.profile.show', [$a->user->id])); ?>" class="tooltipright" title="<?php echo app('translator')->get('airports.home'); ?>: <?php echo e(optional($a->home_airport)->icao); ?>"><?php echo e(optional($a->user)->name); ?></a></h5>
               <?php else: ?>
               <h5 class="my-1"><span class="fi fi-<?php echo e(optional($a->user)->country); ?> shadow-img me-1" title="<?php echo app('translator')->get('common.country'); ?>"></span> <a href="<?php echo e(route('frontend.profile.show', [$a->user->id])); ?>" class="tooltipright" title="<?php echo app('translator')->get('airports.home'); ?>: <?php echo e(optional($a->home_airport)->icao); ?>"><?php echo e(optional($a->user)->name_private); ?></a></h5>
               <?php endif; ?>
               <p class="fs-14 mb-0"><?php echo e(optional($a->award)->name); ?></p>
               <span class="text-muted small"><?php echo app('translator')->get('sptheme.awarded'); ?> <?php echo e($a->created_at->diffForHumans()); ?>.</span>
            </div>
         </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
   </div>
</div>
<?php else: ?>
<div class="card border mb-3">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-trophy align-middle fs-20 me-1"></i> <?php echo app('translator')->get('sptheme.latestawards'); ?></h4>
      <div class="alert alert-info" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.allnoaward'); ?></div>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/widgets/latest_awards.blade.php ENDPATH**/ ?>