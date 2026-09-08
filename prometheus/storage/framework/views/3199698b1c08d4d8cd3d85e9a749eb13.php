<table class="table table-hover table-responsive" id="aircrafts-table">
  <thead>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('registration', 'Registration'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', 'Name'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('fin', 'FIN'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('subfleet.name', 'Subfleet'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('hub_id', 'Home'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('airport_id', 'Location'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('landing_time', 'Last Landing'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('fuel_onboard', 'Fuel OB'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_time', 'Hours'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('status', 'Status'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('state', 'State'));?></th>
    <th style="text-align: right;">Actions</th>
  </thead>
  <tbody>
  <?php $__currentLoopData = $aircraft; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ac): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><a href="<?php echo e(route('admin.aircraft.edit', [$ac->id])); ?>"><?php echo e($ac->registration); ?></a></td>
      <td><?php echo e($ac->name); ?></td>
      <td><?php echo e($ac->fin); ?></td>
      <td><?php if($ac->subfleet_id && $ac->subfleet): ?><a href="<?php echo e(route('admin.subfleets.edit', [$ac->subfleet_id])); ?>"><?php echo e($ac->subfleet->name); ?></a><?php endif; ?></td>
      <td style="text-align: center;"><?php echo e($ac->hub_id); ?></td>
      <td style="text-align: center;"><?php echo e($ac->airport_id); ?></td>
      <td style="text-align: center;"><?php if(filled($ac->landing_time)): ?><?php echo e($ac->landing_time->diffForHumans()); ?><?php endif; ?></td>
      <td style="text-align: center;"><?php if(filled($ac->fuel_onboard)): ?><?php echo e(round($ac->fuel_onboard->local()).' '.setting('units.fuel')); ?><?php endif; ?></td>
      <td style="text-align: center;"><?php echo \App\Support\Units\Time::minutesToTimeString($ac->flight_time); ?></td>
      <td style="text-align: center;">
        <?php if($ac->status == \App\Models\Enums\AircraftStatus::ACTIVE): ?>
          <span class="label label-success"><?php echo e(\App\Models\Enums\AircraftStatus::label($ac->status)); ?></span>
        <?php else: ?>
          <span class="label label-default"><?php echo e(\App\Models\Enums\AircraftStatus::label($ac->status)); ?></span>
        <?php endif; ?>
      </td>
      <td style="text-align: center;">
        <?php if($ac->state == \App\Models\Enums\AircraftState::PARKED): ?>
          <span class="label label-success"><?php echo e(\App\Models\Enums\AircraftState::label($ac->state)); ?></span>
        <?php else: ?>
          <span class="label label-default"><?php echo e(\App\Models\Enums\AircraftState::label($ac->state)); ?></span>
        <?php endif; ?>
      </td>
      <td style="width: 10%; text-align: right;">
        <?php echo e(Form::open(['route' => ['admin.aircraft.destroy', $ac->id], 'method' => 'delete'])); ?>

        <a href="<?php echo e(route('admin.aircraft.edit', [$ac->id])); ?>" class='btn btn-sm btn-success btn-icon'><i class="fas fa-pencil-alt"></i></a>
        <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/aircraft/table.blade.php ENDPATH**/ ?>