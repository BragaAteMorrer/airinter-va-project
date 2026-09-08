<?php $__env->startSection('title', trans_choice('common.download', 2)); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/12.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-tray-arrow-down fs-20 me-1"></i><?php echo e(trans_choice('common.download', 2)); ?></h4>
            <?php if(!$grouped_files || \count($grouped_files) === 0): ?>
            <div class="alert alert-danger mt-3"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('downloads.none'); ?></div>
            <?php else: ?>
            <div class="row my-3">
               <?php $__currentLoopData = $grouped_files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $files): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <div class="col-md-4 text-center">
                  <div class="card border">
                     <div class="card-body">
                        <?php echo $__env->make('downloads.table', ['files' => $files], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     </div>
                     <div class="card-footer"><?php echo e($group); ?></div>
                  </div>
               </div>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/downloads/index.blade.php ENDPATH**/ ?>