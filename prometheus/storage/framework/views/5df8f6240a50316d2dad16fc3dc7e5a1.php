<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane align-middle fs-20 me-1"></i><?php echo e($aircraft->registration); ?> <?php if($aircraft->name != $aircraft->registration): ?> "<?php echo e($aircraft->name); ?>" <?php endif; ?>
      <img src="<?php echo e(optional($aircraft->airline)->logo); ?>" class="float-end" width="90" alt="<?php echo e(optional($aircraft->airline)->name); ?>">
      </h4>
      <?php if($aircraft->subfleet && $aircraft->subfleet->fares->count()): ?>
      <ul class="list-unstyled d-flex flex-wrap text-center justify-content-between">
      <?php $__currentLoopData = $aircraft->subfleet->fares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <li class="mt-2">
        <div class="mb-1"><img src="<?php echo e(public_asset('/SPTheme/images/seat_')); ?><?php echo e($fare->name); ?>.png" class="me-2"> <?php echo e(number_format($fare->pivot->capacity)); ?> <?php if($fare->type == 1): ?> <?php echo e($units['weight']); ?> <?php else: ?> Pax <?php endif; ?></div>
        <span><?php echo e($fare->name); ?></span>
      </li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
    <?php endif; ?>
    <table class="table table-hover table-striped table-responsive-sm table-sm table-borderless align-middle mb-0">
      <tbody>
      <tr>
        <td class="fw-bold">ICAO / IATA <?php echo app('translator')->get('DBasic::common.type'); ?></td>
        <td class="text-end"><?php echo e($aircraft->icao); ?> / <?php echo e($aircraft->iata); ?></td>
      </tr>
      <?php if(filled(optional($aircraft->subfleet)->typeratings)): ?>
      <tr>
        <td class="fw-bold">Type Rating(s)</td>
        <td class="text-end">
          <?php $__currentLoopData = $aircraft->subfleet->typeratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php if(!$loop->first): ?> &bull; <?php endif; ?>
          <?php echo e($rating->name); ?>

          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </td>
      </tr>
      <?php endif; ?>
      <tr>
        <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.airline'); ?> / <?php echo app('translator')->get('DBasic::common.subfleet'); ?></td>
        <td class="text-end"><a href="<?php echo e(route('DBasic.airline', [$aircraft->airline->icao ?? ''])); ?>"><?php echo e(optional($aircraft->airline)->name); ?></a> / <a href="<?php echo e(route('DBasic.subfleet', [$aircraft->subfleet->type ?? ''])); ?>"><?php echo e($aircraft->subfleet->name ?? ''); ?></a></td>
      </tr>
      <?php if(filled($aircraft->hub_id) || filled(optional($aircraft->subfleet)->hub_id)): ?>
      <tr>
        <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.base'); ?></td>
        <td class="text-end">
          <?php if(filled($aircraft->hub_id)): ?>
          <a href="<?php echo e(route('DBasic.hub', [$aircraft->hub_id ?? ''])); ?>"><?php echo e($aircraft->hub->full_name ?? ''); ?></a>
          <?php else: ?>
          <a href="<?php echo e(route('DBasic.hub', [$aircraft->subfleet->hub_id ?? ''])); ?>"><?php echo e($aircraft->subfleet->hub->full_name ?? ''); ?></a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endif; ?>
      <tr>
        <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.status'); ?> / <?php echo app('translator')->get('DBasic::common.state'); ?></td>
        <td class="text-end"><?php echo DB_AircraftStatus($aircraft).' '.DB_AircraftState($aircraft); ?></td>
      </tr>
      <?php if($aircraft->airport_id): ?>
      <tr>
        <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.location'); ?></td>
        <td class="text-end">
          <a href="<?php echo e(route('frontend.airports.show', [$aircraft->airport_id])); ?>"><?php echo e($aircraft->airport->full_name ?? $aircraft->airport_id); ?></a>
        </td>
      </tr>
      <?php endif; ?>
      <?php if($aircraft->fuel_onboard->local() > 0 || $aircraft->landing_time): ?>
      <tr>
        <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.fuelob'); ?></td>
        <td class="text-end"><?php if($aircraft->fuel_onboard->local() > 0): ?> <?php echo e(DB_ConvertWeight($aircraft->fuel_onboard, $units['fuel'])); ?> <?php endif; ?></td>
      </tr>
      <tr>
        <td class="fw-bold"><?php echo app('translator')->get('DBasic::common.lastlnd'); ?></td>
        <td class="text-end"><?php if($aircraft->landing_time): ?> <?php echo e($aircraft->landing_time->diffForHumans()); ?> <?php endif; ?></td>
      </tr>
      <?php endif; ?>
    </tbody>
    </table>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/fleet/aircraft_details.blade.php ENDPATH**/ ?>