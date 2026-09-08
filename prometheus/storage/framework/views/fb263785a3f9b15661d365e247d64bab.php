<table class="table table-hover table-striped mb-0">
   <thead>
      <tr>
         <th>#</th>
         <th class="text-center"><?php echo app('translator')->get('DSpecial::common.orig'); ?> / <?php echo app('translator')->get('DSpecial::common.dest'); ?></th>
         <th class="text-center"><?php echo app('translator')->get('DSpecial::common.notes'); ?></th>
         <th class="text-center"><?php echo app('translator')->get('DSpecial::common.dist'); ?></th>
         <th class="text-center"><?php echo app('translator')->get('DSpecial::common.block_time'); ?></th>
         <th class="text-center"><?php echo app('translator')->get('common.status'); ?></th>
      </tr>
   </thead>
   <tbody>
      <?php $__currentLoopData = $tour->legs->sortby('route_leg'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $leg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
         <td><span class="fi fi-<?php echo e(strtolower(optional($leg->arr_airport)->country)); ?> shadow-img me-1"></span> <?php echo e($leg->route_leg); ?></td>
         <td class="text-center">
            <a href="<?php echo e(route('frontend.airports.show', [$leg->dpt_airport_id])); ?>" title="<?php echo e(optional($leg->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($leg->dpt_airport_id); ?></a>
            <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
            <a href="<?php echo e(route('frontend.airports.show', [$leg->arr_airport_id])); ?>" title="<?php echo e(optional($leg->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($leg->arr_airport_id); ?></a>
         </td>
         <td class="text-center">
            <?php if($leg->start_date && $leg->end_date): ?>
            <i class="ph-fill ph-note mx-1 fs-4 text-danger tooltiptop" title="Valid between <?php echo e($leg->start_date->format('d.M.Y').' - '.$leg->end_date->format('d.M.Y')); ?>"></i>
            <?php else: ?>
            <i class="ph-fill ph-note-blank fs-4 mx-1 text-muted tooltiptop" title="No notes"></i>
            <?php endif; ?>
            <?php if($leg->subfleets_count > 0): ?>
            <i class="ph-fill ph-airplane fs-4 mx-1 text-primary tooltiptop" title="Valid only with assigned subfleets"></i>
            <?php endif; ?>
         </td>
         <td class="text-center"><?php if($leg->distance[$units['distance']] > 0): ?> <?php echo e(number_format($leg->distance[$units['distance']]).' '.$units['distance']); ?> <i class="ph-fill ph-arrows-horizontal"></i> <?php endif; ?></td>
         <td class="text-center"><?php if($leg->flight_time > 0): ?> <?php echo \App\Support\Units\Time::minutesToTimeString($leg->flight_time); ?> <i class="ph-fill ph-clock-clockwise"></i> <?php endif; ?></td>
         <td class="text-center">
            <?php if($leg_checks[$leg->route_leg] === true): ?>
            <span class="badge badge-success tooltiptop" title="<?php echo app('translator')->get('DSpecial::tours.icontrue'); ?>"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('sptheme.completed'); ?></span>
            <?php else: ?>
            <?php if($leg->route_leg == '1'): ?>
            <a href="<?php echo e(route('frontend.flights.show', [$leg->id])); ?>" class="btn btn-warning tooltiptop" title="Book now"><i class="ph-fill ph-hourglass-medium"></i> <?php echo app('translator')->get('sptheme.booknow'); ?></span>
               <?php else: ?>
               <?php if($leg_checks[$leg->route_leg-1] === true): ?>
               <a href="<?php echo e(route('frontend.flights.show', [$leg->id])); ?>" class="btn btn-warning tooltiptop" title="Book now"><i class="ph-fill ph-hourglass-medium"></i> <?php echo app('translator')->get('sptheme.booknow'); ?></span>
                  <?php else: ?>
                  <a href="javascript:void(0);" class="btn btn-outline-danger tooltiptop" title="Leg #<?php echo e($leg->route_leg-1); ?> is needed to unlock"><?php echo app('translator')->get('sptheme.flyleg'); ?> #<?php echo e($leg->route_leg-1); ?></span>
                     <?php endif; ?>
                     <?php endif; ?>
                     <?php endif; ?>
         </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
   </tbody>
</table><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/tours/legs_table.blade.php ENDPATH**/ ?>