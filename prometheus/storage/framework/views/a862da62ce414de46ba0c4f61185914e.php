<?php $__env->startSection('title', __('pireps.fileflightreport')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/12.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-newspaper-clipping fs-20 me-1"></i><?php echo app('translator')->get('pireps.newflightreport'); ?></h4>
         </div>
      </div>
      <form method="post" action="<?php echo e(route('frontend.pireps.store')); ?>" class="form-horizontal">
         <?php echo csrf_field(); ?>
         <?php echo $__env->make('pireps.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </form>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('pireps.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/create.blade.php ENDPATH**/ ?>