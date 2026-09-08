<?php $__env->startSection('title', 'phpVMS v7 Credits'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i> phpVMS v7</h4>
            <img src="<?php echo e(public_asset('/assets/img/logo_blue_bg.svg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" alt="phpVMS v7">
            <p>Open-Source Virtual Airline Management - <?php echo app('translator')->get('sptheme.phpvmsdesc'); ?></p>
         </div>
         <div class="card-footer">
            <a href="https://docs.phpvms.net" title="<?php echo app('translator')->get('sptheme.newwindow'); ?>" target="_blank" class="btn btn-primary tooltiptop"><?php echo app('translator')->get('sptheme.documents'); ?> &amp; <?php echo app('translator')->get('sptheme.guides'); ?></a>
            <a href="https://docs.phpvms.net/#license" title="<?php echo app('translator')->get('sptheme.newwindow'); ?>" target="_blank" class="btn btn-primary tooltiptop"><?php echo app('translator')->get('sptheme.license'); ?></a>
         </div>
      </div>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="card border mb-3">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i> <?php echo e($module->name); ?></h4>
            <p><?php echo e($module->description); ?></p>
            <?php if($module->version): ?>
            <p><?php echo app('translator')->get('sptheme.version'); ?>: <?php echo e($module->version); ?></p>
            <?php endif; ?>
         </div>
         <div class="card-footer">
            <?php if($module->active): ?>
            <span class="badge h4 m-0 px-3 badge-success tooltiptop" title="<?php echo app('translator')->get('common.active'); ?>"><i class="ph-fill ph-check-fat"></i></span>
            <?php else: ?>
            <span class="badge h4 m-0 px-3 badge-danger tooltiptop" title="<?php echo app('translator')->get('common.inactive'); ?>"><i class="ph-fill ph-first-aid"></i></span>
            <?php endif; ?>
            <span class="float-end">
               <?php if($module->attribution): ?>
               <a href="<?php echo e($module->attribution->url); ?>" title="<?php echo app('translator')->get('sptheme.newwindow'); ?>" target="_blank" class="btn btn-warning tooltiptop"><?php echo e($module->attribution->text); ?></a>
               <?php endif; ?>
               <?php if($module->readme_url): ?>
               <a href="<?php echo e($module->readme_url); ?>" title="<?php echo app('translator')->get('sptheme.newwindow'); ?>" target="_blank" class="btn btn-primary tooltiptop"><?php echo app('translator')->get('sptheme.documents'); ?></a>
               <?php endif; ?>
               <?php if($module->license_url): ?>
               <a href="<?php echo e($module->license_url); ?>" title="<?php echo app('translator')->get('sptheme.newwindow'); ?>" target="_blank" class="btn btn-primary tooltiptop"><?php echo app('translator')->get('sptheme.license'); ?></a>
               <?php endif; ?>
            </span>
         </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/credits.blade.php ENDPATH**/ ?>