<?php $__env->startSection('title', __('DBasic::common.airlines')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/23.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <?php $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><?php echo e($airline->name); ?>

               <span class="float-end"><img src="<?php echo e($airline->logo); ?>" width="90" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo e($airline->name); ?>" alt="<?php echo e($airline->name); ?>"></span>
            </h4>
            <div class="table-responsive">
               <table class="table table-striped table-hover mb-0">
                  <tr>
                     <th><?php echo app('translator')->get('DBasic::common.icao'); ?></th>
                     <td class="text-end"><?php echo e($airline->icao); ?></td>
                  </tr>
                  <tr>
                     <th><?php echo app('translator')->get('DBasic::common.iata'); ?></th>
                     <td class="text-end"><?php echo e($airline->iata ?? '--'); ?></td>
                  </tr>
                  <tr>
                     <td class="fw-bold">Callsign</td>
                     <td class="text-end"><?php echo e($airline->callsign ?? '--'); ?></td>
                  </tr>
                  <tr>
                     <th><?php echo app('translator')->get('common.country'); ?></th>
                     <td class="text-end">
                        <?php if(strlen($airline->country) === 2): ?>
                        <span class="fi fi-<?php echo e($airline->country); ?> shadow-img me-1" title="<?php echo e($country->alpha2($airline->country)['name']); ?>"></span> <?php echo e($country->alpha2($airline->country)['name']); ?>

                        <?php endif; ?>
                     </td>
                  </tr>
               </table>
               <div class="p-4">
                  <a href="<?php echo e(route('DBasic.airline', [$airline->icao])); ?>" title="{ $airline->name }}" class="btn btn-primary d-block"><?php echo app('translator')->get('common.airline'); ?></a>
               </div>
               <table class="table table-striped text-center table-hover mb-0">
                  <tr>
                     <th><?php echo app('translator')->get('DBasic::common.flights'); ?></th>
                     <th><?php echo app('translator')->get('DBasic::common.subfleets'); ?></th>
                     <th><?php echo app('translator')->get('DBasic::common.aircraft'); ?></th>
                     <th><?php echo app('translator')->get('DBasic::common.pilots'); ?></th>
                     <th><?php echo app('translator')->get('DBasic::common.pireps'); ?></th>
                  </tr>
                  <tr>
                     <td><?php echo e($airline->flights_count); ?></td>
                     <td><?php echo e($airline->subfleets_count); ?></td>
                     <td><?php echo e($airline->aircraft_count); ?></td>
                     <td><?php echo e($airline->users_count); ?></td>
                     <td><?php echo e($airline->pireps_count); ?></td>
                  </tr>
               </table>
            </div>
         </div>
      </div>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/airlines/index.blade.php ENDPATH**/ ?>