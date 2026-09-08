<?php if($pireps->count()): ?>
<div class="card border mb-3">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-books align-middle fs-20 me-1"></i><?php echo app('translator')->get('dashboard.recentreports'); ?></h4>
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/19.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
      <div class="table-responsive">
         <table class="table table-striped table-hover mb-0">
            <thead>
               <tr>
                  <th></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', __('flights.flightnumber')));?></th>
                  <th><?php echo e(trans_choice('common.pilot', 1)); ?></th>
                  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
                  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('aircraft.registration', __('common.aircraft')));?></th>
                  <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('score', __('sptheme.score')));?></th>
                  <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('status', __('common.status')));?></th>
                  </tr>
            </thead>
            <tbody>
               <?php $__currentLoopData = $pireps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <tr class="align-middle">
                  <td class="text-center"><img src="<?php echo e(optional($p->airline)->logo); ?>" width="90" alt="<?php echo e(optional($p->airline)->name); ?>"></td>
                  <td><a href="<?php echo e(route('frontend.pireps.show', [$p->id])); ?>" class="tooltiptop" title="<?php echo app('translator')->get('pireps.flightinformations'); ?>"><?php echo e($p->ident); ?></a></td>
                  <td><span class="fi fi-<?php echo e(optional($p->user)->country); ?> shadow-img me-1" title="Country"></span> <a href="<?php echo e(route('frontend.users.show.public', [$p->user_id])); ?>" class="tooltiptop" title="<?php echo e(optional($p->user)->ident); ?>"> <?php echo e(optional($p->user)->name); ?></a><?php if(optional($p->user)->hasRole('staff')): ?> <i class="ph-duotone ph-star text-warning fs-4 mx-2" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="A member of our staff" data-bs-original-title="A member of our staff"></i></span> <?php endif; ?></td>
                  <td class="text-center">
                     <a href="<?php echo e(route('frontend.airports.show', [$p->dpt_airport_id])); ?>" title="<?php echo e(optional($p->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($p->dpt_airport_id); ?></a>
                     <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                     <a href="<?php echo e(route('frontend.airports.show', [$p->arr_airport_id])); ?>" title="<?php echo e(optional($p->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($p->arr_airport_id); ?></a>
                  </td>
                  <td class="text-center"><span class="tooltiptop" title="<?php echo e($p->aircraft->icao); ?>"><?php echo e($p->aircraft->registration); ?></span></td>
                  <td class="text-end"><i class="ph-fill ph-trophy align-text-bottom fs-20"></i> <?php if(!is_null($p->score)): ?> <?php echo e($p->score); ?> <?php else: ?> - <?php endif; ?></td>
                  <td class="text-end">
                     <?php if($p->state==1): ?> <span class="badge badge-warning"><i class="ph-fill ph-hourglass-medium"></i> <?php echo app('translator')->get('pireps.state.pending'); ?></span> <?php endif; ?>
                     <?php if($p->state==2): ?> <span class="badge badge-success"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('pireps.state.accepted'); ?></span> <?php endif; ?>
                     <?php if($p->state==3): ?> <span class="badge badge-danger"><i class="ph-fill ph-first-aid"></i> <?php echo app('translator')->get('pireps.state.cancelled'); ?></span> <?php endif; ?>
                     <?php if($p->state==6): ?> <span class="badge badge-danger"><i class="ph-fill ph-first-aid"></i> <?php echo app('translator')->get('pireps.state.rejected'); ?></span> <?php endif; ?>
                  </td>
               </tr>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
         </table>
      </div>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/widgets/latest_pireps.blade.php ENDPATH**/ ?>