<div class="card border mb-0">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom">
         <i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo e($subfleet->name); ?>

         <img src="<?php echo e(optional($subfleet->airline)->logo); ?>" width="90" class="float-end" alt="<?php echo e(optional($subfleet->airline)->name); ?>">
      </h4>
      <?php if($subfleet->fares_count > 0): ?>
      <ul class="list-unstyled d-flex flex-wrap text-center justify-content-between">
         <?php $__currentLoopData = $subfleet->fares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
         <li class="mt-2">
            <div class="mb-1"><img src="<?php echo e(public_asset('/SPTheme/images/seat_')); ?><?php echo e($fare->name); ?>.png" class="me-2"> <?php echo e(number_format($fare->pivot->capacity)); ?> <?php if($fare->type == 1): ?> <?php echo e($units['weight']); ?> <?php else: ?> Pax <?php endif; ?></div>
            <span><?php echo e($fare->name); ?></span>
         </li>
         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </ul>
      <?php endif; ?>
      <table class="table table-hover table-striped mb-0">
         <tbody>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.type'); ?></td>
               <td class="text-end"><?php echo e($subfleet->type); ?></td>
            </tr>
            <?php if(filled($subfleet->typeratings)): ?>
            <tr>
               <td class="fw-bold">Type Rating</td>
               <td class="text-end"><?php $__currentLoopData = $subfleet->typeratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if(!$loop->first): ?> &bull; <?php endif; ?> <?php echo e($rating->name); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.airline'); ?></td>
               <td class="text-end"><a href="<?php echo e(route('DBasic.airline', [$subfleet->airline->icao ?? ''])); ?>"><?php echo e($subfleet->airline->name ?? ''); ?></a></td>
            </tr>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.base'); ?></td>
               <td class="text-end"><a href="<?php echo e(route('DBasic.hub', [$subfleet->hub_id ?? ''])); ?>" title="<?php echo e($subfleet->hub->icao ?? ''); ?>" class="tooltipTop"><?php echo e($subfleet->hub->name ?? ''); ?></a></td>
            </tr>
            <?php if($subfleet->flights_count > 0): ?>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.flights'); ?></td>
               <td class="text-end"><?php echo e(number_format($subfleet->flights_count)); ?></td>
            </tr>
            <?php endif; ?>
         </tbody>
      </table>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/fleet/subfleet_details.blade.php ENDPATH**/ ?>