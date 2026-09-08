<?php $__env->startSection('title', 'Personal Items'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/37.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-shopping-cart align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.pilotshop'); ?>
               <span class="float-end"><a class="btn btn-primary" href="<?php echo e(route('DSpecial.market')); ?>"><?php echo app('translator')->get('DSpecial::common.market'); ?></a></span>
            </h4>
            <?php if(!$items->count()): ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.notbought'); ?></div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php if($items->count()): ?>
<div class="row">
   <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-package align-middle fs-20 me-1"></i><?php echo e($item->name); ?>

               <span class="fw-normal float-end"><?php echo e(money($item->price, $units['currency'], $seperation)); ?></span>
            </h4>
            <div class="row">
               <div class="col-3">
                  <?php if(filled($item->image_url)): ?>
                  <img src="<?php echo e($item->image_url); ?>" alt="<?php echo e($item->name); ?>" title="<?php echo e($item->name); ?>" class="img-fluid" width="300">
                  <?php endif; ?>
               </div>
               <div class="col-9">
                  <?php echo $item->description; ?>

               </div>
            </div>
         </div>
         <?php if(filled($item->notes) && Auth::id() == $owner): ?>
         <div class="card-footer text-center">
            <p class="mb-0"><?php echo $item->notes; ?></p>
         </div>
         <?php endif; ?>
      </div>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
<?php echo e($items->links('pagination.default')); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/market/show.blade.php ENDPATH**/ ?>