<?php $__env->startSection('title', __('flights.mybid')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-address-book align-middle fs-20 me-1"></i>You have <?php echo e($flights->count()); ?> flight(s) booked!</h4>
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/20.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <?php if(!$flights->count()): ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.nobids'); ?></div>
            <?php else: ?>
            <?php echo $__env->make('flights.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php echo $__env->make('flights.bids_aircraft', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('flights.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flights/bids.blade.php ENDPATH**/ ?>