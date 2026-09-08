<div class="content table-responsive table-full-width">
  <table class="table table-hover" id="flights-table">
    <thead>
      
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', 'Flight #'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('callsign', 'Callsign'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dep_airport_id', 'Orig'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', 'Dest'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('alt_airport_id', 'Altn'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_time', 'Dpt Time'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_time', 'Arr Time'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('subfleets_count', 'Subfleets'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('route', 'Route'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('notes', 'Notes'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('distance', 'Distance'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_time', 'Duration'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_type', 'Type'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('active', 'Active'));?></th>
      <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('visible', 'Visible'));?></th>
      <th class="text-right">Actions</th>
    </thead>
    <tbody>
      <?php $__currentLoopData = $flights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          
          <td><a href="<?php echo e(route('admin.flights.edit', [$flight->id])); ?>"><?php echo e($flight->ident); ?></a></td>
          <td><?php echo e($flight->callsign); ?></td>
          <td><?php echo e($flight->dpt_airport_id); ?></td>
          <td><?php echo e($flight->arr_airport_id); ?></td>
          <td><?php echo e($flight->alt_airport_id); ?></td>
          <td><?php echo e($flight->dpt_time); ?></td>
          <td><?php echo e($flight->arr_time); ?></td>
          <td class="text-center">
            <?php if($flight->subfleets_count > 0): ?>
              <?php echo e($flight->subfleets_count); ?>

              <span class="text-info"><i class="fas fa-info-circle fa2x ml-1" title="<?php $__currentLoopData = $flight->subfleets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php echo e($sf->type); ?> <?php if(!$loop->last): ?> | <?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>"></i></span>
            <?php else: ?>
              -
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if(filled($flight->route)): ?>
              <span class="text-info"><i class="fas fa-info-circle fa2x" title="<?php echo e($flight->route); ?>"></i></span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if(filled($flight->notes)): ?>
              <span class="text-info"><i class="fas fa-info-circle fa2x" title="<?php echo e($flight->notes); ?>"></i></span>
            <?php endif; ?>
          </td>
          <td class="text-center"><?php echo e(round($flight->distance->local()).' '.setting('units.distance')); ?></td>
          <td class="text-center"><?php echo \App\Support\Units\Time::minutesToTimeString($flight->flight_time); ?></td>
          <td class="text-center"><?php echo e($flight->flight_type); ?></td>
          <td class="text-center">
            <?php if($flight->active == 1): ?>
              <span class="label label-success"><?php echo app('translator')->get('common.active'); ?></span>
            <?php else: ?>
              <span class="label label-default"><?php echo app('translator')->get('common.inactive'); ?></span>
            <?php endif; ?>
          </td>
          <td class="text-center">
            <?php if($flight->visible == 1): ?>
              <span class="text-success"><i class="fas fa-check fa2x" title="Visible"></i></span>
            <?php else: ?>
              <span class="text-danger"><i class="fas fa-times fa2x" title="Hidden"></i></span>
            <?php endif; ?>
          </td>
          <td class="text-right">
            <?php echo e(Form::open(['route' => ['admin.flights.destroy', $flight->id], 'method' => 'delete'])); ?>

            <a href="<?php echo e(route('admin.flights.edit', [$flight->id])); ?>" class='btn btn-sm btn-success btn-icon'><i class="fas fa-pencil-alt"></i></a>
            <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

            <?php echo e(Form::close()); ?>

          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/flights/table.blade.php ENDPATH**/ ?>