<?php $__env->startSection('title', __('DBasic::common.fleet')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/24.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <?php if(!$aircraft->count()): ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>No Aircraft.</div>
            <?php else: ?>
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php if(isset($subfleet)): ?> <?php echo e($subfleet->airline->name.' | '.$subfleet->name); ?> <?php else: ?> <?php echo e(config('app.name')); ?> <?php endif; ?> <?php echo app('translator')->get('DBasic::common.fleet'); ?>
               <span class="fw-normal float-end"><?php echo app('translator')->get('DBasic::common.paginate', ['first' => $aircraft->firstItem(), 'last' => $aircraft->lastItem(), 'total' => $aircraft->total()]); ?></span>
            </h4>
            <?php echo $__env->make('DBasic::fleet.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
         </div>
         <?php if($aircraft->count()): ?>
         <div class="card-footer">
            <?php echo e($aircraft->withQueryString()->links('pagination.default')); ?>

         </div>
         <?php endif; ?>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/fleet/index.blade.php ENDPATH**/ ?>