<?php $__env->startSection('title', 'Notams'); ?>
<?php $__env->startSection('content'); ?>
<div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
   <div class="card border mb-0">
      <div class="card-body">
         <img src="<?php echo e(public_asset('/SPTheme/images/banner/38.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="Banner / Image">
         <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-newspaper fs-20 me-1"></i>NOTAMs</h4>
         <?php if(!$notams->count()): ?>
         <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('DSpecial::common.no_notams'); ?></div>
         <?php endif; ?>
      </div>
   </div>
</div>
<?php if($notams->count()): ?>
<div class="row">
<?php $__currentLoopData = $notams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">      
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo e($notam->ident); ?> <?php if(filled($notam->ref_airline)): ?> | <?php echo e(optional($notam->airline)->name); ?> <?php endif; ?></h4>
            <table class="table table-hover table-striped mb-0">
               <tr>
                  <th class="py-0">A)</th>
                  <td class="py-0"><?php echo e($notam->ref_airport ?? 'NIL'); ?></td>
               </tr>
               <tr>
                  <th class="py-0">B)</th>
                  <td class="py-0"><?php echo e($notam->effectivefrom); ?></td>
               </tr>
               <tr>
                  <th class="py-0">C)</th>
                  <td class="py-0"><?php echo e($notam->effectiveuntil); ?></td>
               </tr>
               <tr>
                  <th class="py-0">E)</th>
                  <td class="py-0"><?php echo str_replace($remove, '', $notam->body); ?></td>
               </tr>
            </table>
         </div>
      </div>
   </div>   
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
<?php echo e($notams->links('pagination.default')); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/notams/index.blade.php ENDPATH**/ ?>