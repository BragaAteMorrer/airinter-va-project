<?php $__env->startSection('title', $aircraft->registration); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/24.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php echo $__env->make('DBasic::fleet.aircraft_details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php if($image): ?>
      <div class="card border">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-camera align-middle fs-20 me-1"></i><?php echo e($image['title']); ?></h4>
            <img src="<?php echo e(public_asset($image['url'])); ?>" class="card-img-top" alt="<?php echo e($image['title']); ?>">
         </div>
      </div>
      <?php endif; ?>
      <?php if($specs): ?>
      <?php echo $__env->make('DBasic::specs.card', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>
   </div>
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php if($maint): ?>
      <div class="card border">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-screwdriver align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.maintenance'); ?></h4>
            <?php echo $__env->make('DSpecial::maintenance.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
      </div>
      <?php endif; ?>
      <?php if($pireps): ?>
      <div class="card border">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-books align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.reports'); ?></h4>
            <?php echo $__env->make('DBasic::pireps.table_compact', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
      </div>
      <?php endif; ?>
      <?php if($stats): ?>
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-chart-bar align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::widgets.stats'); ?></h4>
            <table class="table table-hover table-striped mb-0">
               <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr>
                  <td class="fw-bold"><?php echo e($key); ?></td>
                  <td class="text-end"><?php echo e($value); ?></td>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
         </div>
      </div>
      <?php endif; ?>
      <?php if($files): ?>
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-download align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.downloads'); ?></h4>
            <?php echo $__env->make('downloads.table', ['files' => $files], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
      </div>
      <?php endif; ?>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/fleet/aircraft.blade.php ENDPATH**/ ?>