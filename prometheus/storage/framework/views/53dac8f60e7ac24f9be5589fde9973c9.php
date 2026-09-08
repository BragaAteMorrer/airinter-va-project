<div class="table-responsive">
   <table class="table table-striped table-hover mb-0">
      <thead>
         <tr>
            <th></th>
            <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', __('flights.flightnumber')));?></th>
            <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
            <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('aircraft.registration', __('common.aircraft')));?></th>
            <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('blocktime', __('sptheme.blocktime')));?></th>
            <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('landing_rate', __('sptheme.lrate')));?></th>
            <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('score', __('sptheme.score')));?></th>
            <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('status', __('common.status')));?></th>
         </tr>
      </thead>
      <tbody>
         <tr class="align-middle">
            <td class="text-center"><img src="<?php echo e(optional($pirep->airline)->logo); ?>" width="90" alt="<?php echo e(optional($pirep->airline)->name); ?>"></td>
            <td><a href="<?php echo e(route('frontend.pireps.show', [$pirep->id])); ?>" class="tooltiptop" title="<?php echo app('translator')->get('pireps.flightinformations'); ?>"><?php echo e($pirep->ident); ?></a></td>
            <td class="text-center">
               <a href="<?php echo e(route('frontend.airports.show', [$pirep->dpt_airport_id])); ?>" title="<?php echo e(optional($pirep->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($pirep->dpt_airport_id); ?></a>
               <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
               <a href="<?php echo e(route('frontend.airports.show', [$pirep->arr_airport_id])); ?>" title="<?php echo e(optional($pirep->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($pirep->arr_airport_id); ?></a>
            </td>
            <td class="text-center"><?php echo e($pirep->aircraft->registration); ?> (<?php echo e($pirep->aircraft->icao); ?>)</td>
            <td class="text-center"><i class="ph-fill ph-clock-countdown align-text-bottom fs-20"></i> <?php if(!is_null($pirep->flight_time)): ?> <?php echo e(\Modules\SPTheme\Services\TimeService::convert($pirep->flight_time)); ?> <?php else: ?> - <?php endif; ?></td>
            <td class="text-end"><i class="ph-fill ph-airplane-taxiing align-text-bottom fs-20"></i> <?php if(!is_null($pirep->landing_rate)): ?> <?php echo e(round($pirep->landing_rate).' ft/min'); ?> <?php else: ?> - <?php endif; ?></td>
            <td class="text-end"><i class="ph-fill ph-trophy align-text-bottom fs-20"></i> <?php if(!is_null($pirep->score)): ?> <?php echo e($pirep->score); ?> <?php else: ?> - <?php endif; ?></td>
            <td class="text-end">
               <?php if($pirep->state==1): ?> <span class="badge badge-warning"><i class="ph-fill ph-hourglass-medium"></i> <?php echo app('translator')->get('pireps.state.pending'); ?></span> <?php endif; ?>
               <?php if($pirep->state==2): ?> <span class="badge badge-success"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('pireps.state.accepted'); ?></span> <?php endif; ?>
               <?php if($pirep->state==6): ?> <span class="badge badge-danger"><i class="ph-fill ph-first-aid"></i> <?php echo app('translator')->get('pireps.state.rejected'); ?></span> <?php endif; ?>
            </td>
         </tr>
         <?php if($pirep->comments->count() > 0): ?>
         <?php $__currentLoopData = $pirep->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
         <tr>
            <td colspan="8" class="px-0"><div class="alert alert-info" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo $comment->comment; ?></div></td>
         </tr>
         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         <?php endif; ?>
      </tbody>
   </table>
</div>
<div class="alert alert-secondary mt-3" role="alert"><i class="ph-fill ph-airplane-in-flight align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.lastflight'); ?> <?php echo e($pirep->submitted_at->diffForHumans()); ?></div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/dashboard/pirep_card.blade.php ENDPATH**/ ?>