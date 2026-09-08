<table class="table table-hover table-striped mb-0">
  <thead>
    <tr>
      <th><?php echo app('translator')->get('DBasic::common.airline'); ?></th>
      <th class="text-start"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('registration', __('DBasic::common.reg')));?></th>
      <?php if(empty($compact_view)): ?>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('subfleet.name', __('DBasic::common.subfleet')));?></th>
      <?php endif; ?>
      <?php if(empty($hub_ac)): ?>
      <th><?php echo app('translator')->get('DBasic::common.base'); ?></th>
      <?php endif; ?>
      <?php if(empty($visitor_ac)): ?>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('airport_id', __('DBasic::common.location')));?></th>
      <?php endif; ?>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_time', __('DBasic::common.btime')));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('fuel_onboard', __('DBasic::common.fuelob')));?></th>
      <th><?php echo app('translator')->get('DBasic::common.lastlnd'); ?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('state', __('DBasic::common.state')));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('status', __('DBasic::common.status')));?></th>
    </tr>
  </thead>
  <tbody>
    <?php $__currentLoopData = $aircraft; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ac): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr class=" align-middle <?php if($ac->simbriefs_count > 0): ?> table-warning <?php endif; ?>">
    <td class="text-center"><img class="airline_<?php echo e(optional($ac->airline)->icao); ?> tooltiptop" src="<?php echo e(optional($ac->airline)->logo); ?>" width="90" alt="<?php echo e(optional($ac->airline)->name); ?>" title="<?php echo e(optional($ac->airline)->name); ?>"></td>
    <td class="text-start"><a href="<?php echo e(route('DBasic.aircraft', [$ac->registration])); ?>" title="<?php echo e($ac->icao); ?>" class="tooltiptop"><?php echo e($ac->registration); ?></a></td>
    <?php if(empty($compact_view)): ?>
    <td><a href="<?php echo e(route('DBasic.subfleet', [$ac->subfleet->type ?? '-'])); ?>" class="tooltiptop" title="<?php echo e($ac->name); ?>"><?php echo e($ac->subfleet->name ?? ''); ?></a></td>
    <?php endif; ?>
    <?php if(empty($hub_ac)): ?>
    <td>
      <?php if(filled($ac->hub_id)): ?>
      <a href="<?php echo e(route('DBasic.hub', [$ac->hub_id ?? '-'])); ?>" class="badge badge-rounded badge-primary tooltiptop" title="<?php echo app('translator')->get('sptheme.oai'); ?>"><i class="ph-fill ph-house"></i> <?php echo e($ac->hub_id ?? ''); ?></a>
      <?php else: ?>
      <a href="<?php echo e(route('DBasic.hub', [$ac->subfleet->hub_id ?? '-'])); ?>" class="badge badge-rounded badge-primary tooltiptop" title="<?php echo app('translator')->get('sptheme.oai'); ?>"><i class="ph-fill ph-house"></i> <?php echo e($ac->subfleet->hub_id ?? ''); ?></a>
      <?php endif; ?>
    </td>
    <?php endif; ?>
    <?php if(empty($visitor_ac)): ?>
    <td><a href="<?php echo e(route('frontend.airports.show', [$ac->airport_id ?? '-'])); ?>" class="badge badge-rounded badge-primary tooltiptop" title="ph-fill ph-house"><i class="ph-fill ph-house"></i> <?php echo e($ac->airport_id ?? ''); ?></a></td>
    <?php endif; ?>
    <td><?php if($ac->flight_time >= '1'): ?> <?php echo e(DB_ConvertMinutes($ac->flight_time, '%2dh %2dm')); ?> <i class="ph-fill ph-clock-countdown align-text-bottom fs-20"></i> <?php else: ?>  - <i class="ph-fill ph-clock-countdown align-text-bottom fs-20"></i> <?php endif; ?></td>
    <td><?php if($ac->fuel_onboard >= '1'): ?> <?php echo e(DB_ConvertWeight($ac->fuel_onboard, $units['fuel'])); ?> <i class="ph-fill ph-gas-pump align-text-bottom fs-20"></i> <?php else: ?> - <i class="ph-fill ph-gas-pump align-text-bottom fs-20"></i> <?php endif; ?></td>
    <td><?php echo e(optional($ac->landing_time)->diffForHumans() ?? '-'); ?> <i class="ph-fill ph-airplane-taxiing align-text-bottom fs-20"></i></td>
    <td><?php echo DB_AircraftState($ac); ?></td>
    <td><?php echo DB_AircraftStatus($ac); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/fleet/table.blade.php ENDPATH**/ ?>