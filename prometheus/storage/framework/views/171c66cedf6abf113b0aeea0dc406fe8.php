
<div class="content table-responsive table-full-width">
  <table class="table table-hover" id="flights-table">
    <thead>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('state', 'State'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('user.name', 'Pilot'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', 'Flight #'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('aircraft.registration', 'Aircraft'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', 'Dep'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', 'Arr'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_time', 'Time'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('distance', 'Distance'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('score', 'Score'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('source', 'Source'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('submitted_at', 'Submitted'));?></th>
      <th class="text-right">Actions</th>
    </thead>
    <tbody>
      <?php $__currentLoopData = $pireps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pirep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td>
            <div id="pirep_<?php echo e($pirep->id); ?>_status_container">
              <?php
                $PirepStateClass = "badge badge-info" ;
                if($pirep->state === PirepState::PENDING ) { $PirepStateClass = "badge badge-warning" ; }
                if($pirep->state === PirepState::ACCEPTED ) { $PirepStateClass = "badge badge-success" ; }
                if($pirep->state === PirepState::REJECTED ) { $PirepStateClass = "badge badge-danger" ; }
              ?>
              <div class="<?php echo e($PirepStateClass); ?>"><?php echo e(PirepState::label($pirep->state)); ?></div>
            </div>
          </td>
          <td>
            <a href="<?php echo e(route('admin.users.edit', [$pirep->user->id])); ?>">
              <?php echo e($pirep->user_id.' | '.optional($pirep->user)->name_private); ?>

            </a>
          </td>
          <td>
            <a href="<?php echo e(route('admin.pireps.edit', [$pirep->id])); ?>"><?php echo e($pirep->ident); ?></a>
          </td>
          <td>
            <?php if($pirep->aircraft): ?>
              <?php echo e($pirep->aircraft->ident); ?>

            <?php else: ?>
              <?php echo e($pirep->aircraft_id); ?>

            <?php endif; ?>
          </td>
          <td><?php echo e($pirep->dpt_airport_id); ?></td>
          <td><?php echo e($pirep->arr_airport_id); ?></td>
          <td><?php echo \App\Support\Units\Time::minutesToTimeString($pirep->flight_time); ?></td>
          <td><?php echo e(round($pirep->distance->local()).' '.setting('units.distance')); ?></td>
          <td><?php echo e($pirep->score); ?></td>
          <td>
            <?php echo e(PirepSource::label($pirep->source)); ?>

            <?php if(filled($pirep->source_name)): ?>
              (<?php echo e($pirep->source_name); ?>)
            <?php endif; ?>
          </td>
          <td><?php echo e($pirep->submitted_at->format('d.M.Y H:i')); ?></td>
          <td class="text-right">
            <div id="pirep_<?php echo e($pirep->id); ?>_actionbar" class="pull-right">
              <?php echo $__env->make('admin.pireps.actions', ['pirep' => $pirep, 'on_edit_page' => false], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>

<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/table.blade.php ENDPATH**/ ?>