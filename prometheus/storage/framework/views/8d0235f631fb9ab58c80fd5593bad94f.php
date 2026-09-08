<?php $__env->startSection('title', trans_choice('common.pirep', 2)); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-3">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom">
               <i class="ph-fill ph-books align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.reports'); ?>
            </h4>
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/19.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <?php if($pireps->count()): ?>
            <?php echo $__env->make('DBasic::pireps.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('widgets.livemap.noflights'); ?></div>
            <?php endif; ?>
         </div>
         <div class="card-footer text-center">
            <?php echo e($pireps->withQueryString()->links('pagination.bootstrap-5')); ?>

         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/pireps/index.blade.php ENDPATH**/ ?>