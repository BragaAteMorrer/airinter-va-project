<?php $__env->startSection('title', trans_choice('common.pilot', 2)); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/11.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-users align-middle fs-20 me-1"></i><?php echo e(trans_choice('common.pilot', 2)); ?></h4>
            <?php echo $__env->make('users.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
         <div class="card-footer text-center">
            <?php echo e($users->withQueryString()->links('pagination.bootstrap-5')); ?>

         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/users/index.blade.php ENDPATH**/ ?>