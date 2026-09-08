<?php if($is_visible): ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-file-archive align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::common.mn_assignments'); ?>
         <?php if($hide === true): ?>
         <span class="fw-normal float-end small"><a href="<?php echo e(route('DSpecial.assignments')); ?>" class="tooltiptop" title="<?php echo app('translator')->get('DSpecial::common.assignments'); ?>"><?php echo e($counts['completed'].' / '.$counts['total']); ?></a></span>
         <?php endif; ?>
      </h4>
      <table class="table table-striped table-hover mb-0">
         <thead>
            <tr>
               <th class="text-center"><?php echo app('translator')->get('DSpecial::common.flight_no'); ?></th>
               <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
               <th class="text-end"><?php echo app('translator')->get('DSpecial::common.block_time'); ?></th>
               <th class="text-end"><?php echo app('translator')->get('common.status'); ?></th>
            </tr>
         </thead>
         <tbody>
            <?php $__currentLoopData = $assignments->sortBy('assignment_order', SORT_NATURAL); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $as): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($as->flight): ?>
               <tr class="align-middle">
                  <td class="text-center">
                     <?php if($as->flight): ?>
                     <a href="<?php echo e(route('frontend.flights.show', [$as->flight->id])); ?>" title="<?php echo app('translator')->get('flights.flightnumber'); ?>" class="tooltiptop"><?php echo e(optional($as->flight->airline)->code.' '.optional($as->flight)->flight_number); ?></a>
                     <?php endif; ?>
                  </td>
                  <td class="text-center">
                     <a href="<?php echo e(route('frontend.airports.show', [$as->flight->dpt_airport_id])); ?>" title="<?php echo e(optional($as->flight->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e(optional($as->flight->dpt_airport)->id); ?></a>
                     <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                     <a href="<?php echo e(route('frontend.airports.show', [$as->flight->arr_airport_id])); ?>" title="<?php echo e(optional($as->flight->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e(optional($as->flight->arr_airport)->id); ?></a>
                  </td>
                  <td class="text-end">
                     <?php if($as->flight): ?>
                     <?php echo \App\Support\Units\Time::minutesToTimeString($as->flight->flight_time); ?> <i class="ph-fill ph-clock-countdown align-text-bottom fs-20"></i>
                     <?php endif; ?>
                  </td>
                  <td class="text-end">
                     <?php if($as->completed): ?>
                     <?php if(filled($as->pirep_id)): ?>
                     <a href="<?php echo e(route('frontend.pireps.show', [$as->pirep_id])); ?>" class="btn btn-success btn-sm"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('sptheme.completed'); ?></span></a>
                     <?php else: ?>
                     <span class="badge badge-success"><i class="ph-fill ph-check-circle"></i> <?php echo app('translator')->get('sptheme.completed'); ?></span>
                     <?php endif; ?>
                     <?php else: ?>
                     <span class="badge badge-warning"><i class="ph-fill ph-hourglass-medium"></i> <?php echo app('translator')->get('sptheme.open'); ?></span>
                     <?php endif; ?>
                  </td>
               </tr>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         </tbody>
      </table>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/widgets/assignments.blade.php ENDPATH**/ ?>