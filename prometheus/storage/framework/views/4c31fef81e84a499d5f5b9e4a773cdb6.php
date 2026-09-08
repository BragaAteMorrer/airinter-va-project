<?php if($is_visible): ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-pen fs-20 me-1"></i><?php echo e($title); ?></h4>
      <table class="table table-hover table-striped mb-0">
         <tr>
            <th></th>
            <?php if(!$bids): ?>
            <th><?php echo app('translator')->get('DBasic::common.aircraft'); ?></th>
            <?php endif; ?>
            <th><?php echo app('translator')->get('DBasic::common.flightno'); ?></th>
            <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
            <th><?php echo app('translator')->get('DBasic::common.pilot'); ?></th>
            <?php if(!$bids): ?>
            <th>ETD</th>
            <?php endif; ?>
            <th class="text-end"><?php echo app('translator')->get('DBasic::common.expire'); ?></th>
         </tr>
         <?php $__currentLoopData = $active_bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
         <?php if($booking->flight): ?>
         <tr class="align-middle">
            <td class="text-center"><img src="<?php echo e(optional($booking->flight->airline)->logo); ?>" width="90" alt="<?php echo e(optional($booking->flight->airline)->name); ?>"></td>
            <?php if(!$bids): ?>
            <td><a href="<?php echo e(route('DBasic.aircraft', [$booking->aircraft->registration])); ?>" class="tooltiptop" title="<?php echo e(optional($booking->aircraft)->name); ?>"><?php echo e(optional($booking->aircraft)->ident); ?></a></td>
            <?php endif; ?>
            <td><a href="<?php echo e(route('frontend.flights.show', [$booking->flight_id])); ?>" class="tooltiptop" title="<?php echo e($booking->flight->ident); ?>"><?php echo e(optional($booking->flight->airline)->code.' '.$booking->flight->flight_number); ?></a></td>
            <td class="text-center">
               <a href="<?php echo e(route('frontend.airports.show', [$booking->flight->dpt_airport_id])); ?>" title="<?php echo e(optional($booking->flight->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($booking->flight->dpt_airport_id); ?></a>
               <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
               <a href="<?php echo e(route('frontend.airports.show', [$booking->flight->arr_airport_id])); ?>" title="<?php echo e(optional($booking->flight->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($booking->flight->arr_airport_id); ?></a>
            </td>
            <td>
               <?php if(!Auth::check()): ?>
               <span class="fi fi-<?php echo e($booking->user->country); ?> shadow-img me-1" class="tooltiptop" title="<?php echo app('translator')->get('sptheme.country'); ?>"></span> <?php echo e($booking->user->name_private); ?>. <?php if(optional($booking->user)->hasRole('staff')): ?> <span class="badge badge-warning tooltiptop" title="A member of our staff"><i class="ph-fill ph-wrench"></i></span> <?php endif; ?>
               <?php else: ?>
               <a href="<?php echo e(route('frontend.profile.show', [$booking->user_id])); ?>" class="tooltiptop" title="<?php echo e(optional($booking->user)->ident); ?>"><span class="fi fi-<?php echo e($booking->user->country); ?> shadow-img me-1" title="<?php echo app('translator')->get('sptheme.country'); ?>"></span> <?php echo e($booking->user->name); ?></a><?php if(optional($booking->user)->hasRole('staff')): ?> <span class="badge badge-warning tooltiptop" title="A member of our staff"><i class="ph-fill ph-wrench"></i></span> <?php endif; ?>
               <?php endif; ?>
            </td>
            <?php if(!$bids): ?>
            <td class="fw-bold"><?php echo e(date('H:i', $booking->xml->times->est_out->__toString())); ?></td>
            <?php endif; ?>
            <td class="text-end">
               <?php if (app('laratrust')->ability('admin', 'admin-access')) : ?>
               <?php if(!$bids): ?>
               <a href="<?php echo e(route('frontend.simbrief.briefing', [$booking->id])); ?>" target="_blank"><i class="ph-fill ph-info tooltiptop" title="Click to view briefing"></i></a>
               <?php endif; ?>
               <?php endif; // app('laratrust')->ability ?>
               <?php echo e($booking->created_at->addHours($expire)->diffForHumans()); ?>

            </td>
         </tr>
         <?php endif; ?>
         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </table>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/active_bookings.blade.php ENDPATH**/ ?>