<?php if($is_visible): ?>
<div class="card border mb-0">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-books align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.reports'); ?>
         <span class="float-end fw-normal small">Latest <?php echo e($limit); ?> Reports</span>
      </h4>
      <div class="table-responsive">
         <table class="table table-striped table-hover mb-0">
            <thead>
               <tr>
                  <th></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', __('flights.flightnumber')));?></th>
                  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
                  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('aircraft.registration', __('common.aircraft')));?></th>
                  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_time', __('flights.flighttime')));?></th>
                  <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('landing_rate', __('sptheme.lrate')));?></th>
                  <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('score', __('sptheme.score')));?></th>
                  <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('submitted_at', __('pireps.submitted')));?></th>
                  <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('status', __('common.status')));?></th>
               </tr>
            </thead>
            <tbody>
               <?php $__currentLoopData = $pireps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr class="align-middle">
                  <td class="text-center"><img src="<?php echo e(optional($p->airline)->logo); ?>" width="90" alt="<?php echo e(optional($p->airline)->name); ?>"></td>
                  <td><a href="<?php echo e(route('frontend.pireps.show', [$p->id])); ?>" class="tooltiptop" title="<?php echo app('translator')->get('pireps.flightinformations'); ?>"><?php echo e($p->ident); ?></a></td>
                  <td class="text-center">
                     <a href="<?php echo e(route('frontend.airports.show', [$p->dpt_airport_id])); ?>" title="<?php echo e(optional($p->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($p->dpt_airport_id); ?></a>
                     <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                     <a href="<?php echo e(route('frontend.airports.show', [$p->arr_airport_id])); ?>" title="<?php echo e(optional($p->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($p->arr_airport_id); ?></a>
                  </td>
                  <?php if($p->state==5): ?>
                  <td class="text-center"> - </td>
                  <td class="text-center"> - </td>
                  <td class="text-end"> - </td>
                  <td class="text-end"> - </td>
                  <?php else: ?>
                  <td class="text-center"><span class="tooltiptop" title="<?php echo e(optional($p->aircraft)->name); ?>"><?php echo e(optional($p->aircraft)->registration); ?> (<?php echo e(optional($p->aircraft)->icao); ?>)</span></td>
                  <td class="text-center"><i class="ph-fill ph-clock-countdown align-text-bottom fs-20"></i> <?php if(!is_null($p->flight_time)): ?> <?php echo e(\Modules\SPTheme\Services\TimeService::convert($p->flight_time)); ?> <?php else: ?> - <?php endif; ?></td>
                  <td class="text-end"><i class="ph-fill ph-airplane-taxiing align-text-bottom fs-20"></i> <?php if(!is_null($p->landing_rate)): ?> <?php echo e(round($p->landing_rate).' ft/min'); ?> <?php else: ?> - <?php endif; ?></td>
                  <td class="text-end"><i class="ph-fill ph-trophy align-text-bottom fs-20"></i> <?php if(!is_null($p->score)): ?> <?php echo e($p->score); ?> <?php else: ?> - <?php endif; ?></td>
                  <?php endif; ?>
                  <td class="text-end"><?php if(filled($p->submitted_at)): ?><?php echo e($p->submitted_at->diffForHumans()); ?> <?php else: ?> - <?php endif; ?></td>
                  <td class="text-end">
                     <?php if($p->state==1 || $p->state==5): ?> <span class="badge badge-warning"><i class="ph-fill ph-hourglass-medium"></i> <?php echo app('translator')->get('pireps.state.pending'); ?></span> <?php endif; ?>
                     <?php if($p->state==2): ?> <span class="badge badge-success"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('pireps.state.accepted'); ?></span> <?php endif; ?>
                     <?php if($p->state==3): ?> <span class="badge badge-danger"><i class="ph-fill ph-first-aid"></i> <?php echo app('translator')->get('pireps.state.cancelled'); ?></span> <?php endif; ?>
                     <?php if($p->state==6): ?> <span class="badge badge-danger"><i class="ph-fill ph-first-aid"></i> <?php echo app('translator')->get('pireps.state.rejected'); ?></span> <?php endif; ?>
                     <?php if(!$p->read_only): ?><a href="<?php echo e(route('frontend.pireps.edit', [$p->id])); ?>" class="btn btn-sm btn-danger tooltiptop" title="<?php echo app('translator')->get('common.edit'); ?>"><i class="ph-fill ph-pencil-simple fs-12"></i></a><?php endif; ?>
                  </td>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
         </table>
      </div>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/user_pireps.blade.php ENDPATH**/ ?>