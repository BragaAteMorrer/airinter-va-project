<?php $__env->startSection('title', __('DBasic::common.awards')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/24.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row row-cols-md-2 row-cols-lg-4">
   <?php $__currentLoopData = $awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col mb-3">
      <div class="card border mb-0">
         <div class="card-body text-center">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-trophy align-middle fs-20 me-1"></i><?php echo e($award->name); ?></h4>
            <?php if($award->image_url): ?>
            <img src="<?php echo e($award->image_url); ?>" width="180" alt="<?php echo e($award->name); ?>" title="<?php echo e($award->description); ?>">
            <?php endif; ?>
            <p class="mt-3"><?php echo e($award->description); ?></p>
            <?php if($award->active == 1): ?> <span class="badge badge-success"><?php echo app('translator')->get('common.active'); ?></span> <?php else: ?> <span class="badge badge-danger"><?php echo app('translator')->get('common.inactive'); ?></span> <?php endif; ?>
         </div>
      </div>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/awards/index.blade.php ENDPATH**/ ?>