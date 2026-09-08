<?php $__env->startSection('title', $airline->name); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/23.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-9 col-xl-9 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-tilt align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.subfleet'); ?>
               <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($aircraft->count()); ?></span>
            </h4>
            <?php if($aircraft->count() > 0): ?>
            <div class="card-body p-0 table-responsive">
               <div class="dz-scroll" style="max-height:1380px;">
                  <?php echo $__env->make('DBasic::fleet.table', ['aircraft' => $aircraft, 'airline_view' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
               </div>
            </div>
            <?php else: ?>
            <div class="card-body p-0">
               <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>We could not find any aircraft.</div>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.aldetails'); ?>
               <span class="float-end"><img src="<?php echo e($airline->logo); ?>" width="90" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e($airline->name); ?>" alt="<?php echo e($airline->name); ?>"></span>
            </h4>
            <table class="table table-striped table-hover mb-0">
               <tr>
                  <th style="width:30%;"><?php echo app('translator')->get('common.name'); ?></th>
                  <td class="text-end"><?php echo e($airline->name); ?></td>
               </tr>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.icao'); ?></th>
                  <td class="text-end"><?php echo e($airline->icao); ?></td>
               </tr>
               <?php if(filled($airline->iata)): ?>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.iata'); ?></th>
                  <td class="text-end"><?php echo e($airline->iata); ?></td>
               </tr>
               <?php endif; ?>
               <?php if(filled($airline->callsign)): ?>
               <tr>
                  <th><?php echo app('translator')->get('DBasic::common.callsign'); ?></th>
                  <td class="text-end"><?php echo e($airline->callsign); ?></td>
               </tr>
               <?php endif; ?>
               <?php if(strlen($airline->country) === 2): ?>
               <tr>
                  <th><?php echo app('translator')->get('common.country'); ?></th>
                  <td class="text-end">
                     <?php if(strlen($airline->country) === 2): ?>
                     <span class="fi fi-<?php echo e($airline->country); ?> shadow-img me-1" title="<?php echo e($country->alpha2($airline->country)['name']); ?>"></span> <?php echo e($country->alpha2($airline->country)['name']); ?>

                     <?php endif; ?>
                  </td>
               </tr>
               <?php endif; ?>
            </table>
            <div class="row mt-3">
               <div class="col">
                  <?php echo app('arrilot.widget')->run('DBasic::Map', ['source' => $airline->id]); ?>
               </div>
               <div class="col">
                  <?php echo app('arrilot.widget')->run('DBasic::Map', ['source' => 'fleet', 'airline' => $airline->id]); ?>
               </div>
            </div>
         </div>
      </div>
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-bank align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.finance'); ?></h4>
            <table class="table table-striped table-hover mb-0">
               <?php $__currentLoopData = $finance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr>
                  <th><?php echo e($key); ?></th>
                  <td class="text-end"><?php echo $value; ?></td>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
         </div>
      </div>
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo e($sp_settings['bottombox_title']); ?></h4>
            <table class="table table-striped table-hover mb-0">
               <tbody>
                  <?php $__currentLoopData = $stats_b; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                     <td class="fw-bold"><?php echo e($key); ?></td>
                     <td class="text-end"><?php echo e($value); ?></td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  <?php if($stats_p): ?>
                  <?php $__currentLoopData = $stats_p; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                     <td class="fw-bold"><?php echo e($key); ?></td>
                     <td class="text-end"><?php echo e($value); ?></td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  <?php endif; ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/airlines/show.blade.php ENDPATH**/ ?>