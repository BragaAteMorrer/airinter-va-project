<?php $__env->startSection('title', __('errors.404.title')); ?>
<?php $__env->startSection('content'); ?>
<div class="row d-flex align-items-center justify-content-center">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-bug-beetle fs-20 me-1"></i>404 - <?php echo app('translator')->get('errors.404.title'); ?></h4>
            <p><?php echo str_replace(':link', config('app.url'), __('errors.404.message')); ?></p>
            <div class="alert alert-danger" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo e($exception->getMessage()); ?></div>
         </div>
         <div class="card-footer text-center">
            <a href="<?php echo e(route('frontend.home')); ?>" class="btn btn-primary">Go Home</a>
            <a href="<?php echo e(url()->previous()); ?>" class="btn btn-primary">Go Back</a>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/errors/404.blade.php ENDPATH**/ ?>