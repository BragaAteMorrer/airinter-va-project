<?php $__env->startSection('title', 'Stable Approach Reports'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/29.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <?php if(!$sap_reports->count()): ?>
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-in-flight align-middle fs-20 me-1"></i>Stable Approach Report</h4>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>No stable approach reports.</div>
         </div>
      </div>
   </div>
</div>
<?php else: ?>
<div class="row row-cols-4">
   <?php $__currentLoopData = $sap_reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col">
      <?php echo $__env->make('DBasic::sap.report', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php echo e($sap_reports->links('pagination.default')); ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/sap/index.blade.php ENDPATH**/ ?>