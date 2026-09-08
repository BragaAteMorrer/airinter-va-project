<?php $__env->startSection('title', __('DBasic::common.news')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/25.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-newspaper fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.news'); ?></h4>
            <?php if(!$allnews->count()): ?>
            <div class="alert alert-info mt-3 mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>There are no news.</div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <?php if($allnews->count()): ?>
   <?php $__currentLoopData = $allnews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-newspaper fs-20 me-1"></i><a href="<?php echo e(route('DBasic.news')); ?>" class="text-primary tooltiptop" title="Open all our News"><?php echo app('translator')->get('DBasic::common.news'); ?>:</a> <?php echo e($item->subject); ?>

               <span class="text-muted fw-normal small mt-1 float-end">Written by <a href="/users/<?php echo e(optional($item->user)->id); ?>" title="<?php echo e(optional($item->user)->ident); ?>" class="tooltiptop"><?php echo e(optional($item->user)->name); ?></a> - <?php echo e($item->created_at->format('d.m.Y')); ?></span>
            </h4>
            <p><?php echo $item->body; ?></p>
         </div>
      </div>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
   <?php echo e($allnews->links('pagination.default')); ?>

   <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/news/index.blade.php ENDPATH**/ ?>