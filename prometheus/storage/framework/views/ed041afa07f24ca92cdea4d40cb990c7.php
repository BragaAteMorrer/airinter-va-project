<?php $__env->startSection('title', __('DBasic::common.hubs')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/25.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<?php if(!$hubs->count()): ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>We do not have any HUBs.</div>
         </div>
      </div>
   </div>
</div>
<?php else: ?>
<div class="row">
   <?php $__currentLoopData = $hubs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <div class="row align-items-center">
               <div class="col text-truncate">
                  <div class="fw-bold"><?php echo e($hub->name); ?></div>
                  <p class="mb-0">
                     <?php if(strlen($hub->country) === 2): ?>
                     <?php echo e($country->alpha2($hub->country)['name']); ?> (<?php echo e(strtoupper($hub->country)); ?>)
                     <?php endif; ?>
                  </p>
               </div>
               <div class="col-auto">
                  <span class="fi fi-<?php echo e(strtolower($hub->country)); ?> shadow-img me-1" title="Country"></span>
               </div>
            </div>

            <div class="d-flex my-3">
               <a class="btn btn-primary w-100" href="<?php echo e(route('DBasic.hub', [$hub->id])); ?>"><i class="fa fa-file-excel-o me-2"></i>Open HUB Details</a>
            </div>
            <table class="table table-striped table-hover mb-0">
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.icao'); ?></th>
                  <td class="text-end"><?php echo e($hub->icao); ?></td>
               </tr>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.iata'); ?></th>
                  <td class="text-end"><?php echo e($hub->iata ?? '--'); ?></td>
               </tr>
               <tr>
                  <th><?php echo app('translator')->get('common.country'); ?></th>
                  <td class="text-end">
                     <?php if(strlen($hub->country) === 2 && $hub->country === 'GB'): ?>
                     United Kingdom (GB)
                     <?php else: ?>
                     <?php echo e($country->alpha2($hub->country)['name']); ?> (<?php echo e(strtoupper($hub->country)); ?>)
                     <?php endif; ?>
                  </td>
               </tr>
               <tr>
                  <th><?php echo e(trans_choice('common.pilot', 2)); ?></th>
                  <td class="text-end"><?php echo e($pilots[$hub->icao] ?? '-'); ?></td>
               </tr>
            </table>
         </div>
      </div>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/hubs/index.blade.php ENDPATH**/ ?>