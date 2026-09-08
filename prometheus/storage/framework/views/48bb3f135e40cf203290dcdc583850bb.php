<?php if($is_visible): ?>
<div class="card border">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-shuffle align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::widgets.random_flights'); ?></h4>
      <div class="card-body p-0 table-responsive">
         <?php if($random_flights): ?>
         <table class="table table-hover table-striped mb-0">
            <thead>
               <tr>
                  <th class="text-start"><?php echo app('translator')->get('DBasic::common.flightno'); ?></th>
                  <th><?php echo app('translator')->get('DBasic::common.orig'); ?> / <?php echo app('translator')->get('DBasic::common.dest'); ?></th>
                  <th class="text-end"><?php echo app('translator')->get('DBasic::common.expire'); ?></th>
               </tr>
            </thead>
            <tbody>
               <?php $__currentLoopData = $random_flights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr>
                  <?php if($rf->flight): ?>
                     <td class="text-start">
                        <a href="<?php echo e(route('frontend.flights.show', [$rf->flight_id])); ?>" class="tooltiptop" title="<?php echo app('translator')->get('pireps.flightinformations'); ?>"><?php echo e(optional($rf->flight->airline)->code.' '.$rf->flight->flight_number); ?></a>
                     </td>
                     <td>
                        <a href="<?php echo e(route('frontend.airports.show', [$rf->flight->dpt_airport_id])); ?>" title="<?php echo e(optional($rf->flight->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($rf->flight->dpt_airport_id); ?></a>
                        <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                        <a href="<?php echo e(route('frontend.airports.show', [$rf->flight->arr_airport_id])); ?>" title="<?php echo e(optional($rf->flight->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($rf->flight->arr_airport_id); ?></a>
                     </td>
                     <td class="text-end">
                        <?php if($rf->completed): ?>
                        <?php echo app('translator')->get('DBasic::common.completed'); ?>
                        <i class="ph-fill ph-check-circle text-success"></i>
                        <?php else: ?>
                        <?php echo e($today->endOfDay()->DiffForHumans()); ?>

                        <i class="ph-fill ph-hourglass-medium text-danger"></i>
                        <?php endif; ?>
                     </td>
                  <?php else: ?>
                     <td colspan="4" class="fw-bold text-danger">Error: Flight Not Found !</td>
                  <?php endif; ?>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
         </table>
         <?php else: ?>
         <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>There are no flights.</div>
         <?php endif; ?>
      </div>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/random_flights.blade.php ENDPATH**/ ?>