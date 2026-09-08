<?php $__env->startSection('title', $subfleet->type); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/24.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php echo $__env->make('DBasic::fleet.subfleet_details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php if($image): ?>
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-image align-middle fs-20 me-1"></i><?php echo e($image['title']); ?></h4>
            <img src="<?php echo e(public_asset($image['url'])); ?>" alt="<?php echo e($image['title']); ?>">
         </div>
      </div>
      <?php endif; ?>
      <?php if($specs): ?>
      <?php echo $__env->make('DBasic::specs.card', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>
   </div>
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php if($aircraft && $aircraft->count()): ?>
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-tilt align-middle fs-20 me-1"></i>Subfleet <?php echo app('translator')->get('DBasic::common.members'); ?></h4>
            <?php echo $__env->make('DBasic::fleet.table', ['compact_view' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
      </div>
      <?php endif; ?>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/fleet/subfleet.blade.php ENDPATH**/ ?>