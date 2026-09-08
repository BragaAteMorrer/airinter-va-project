<table class="table table-hover table-responsive text-center" id="airlines-table">
  <thead>
    <th class="text-left"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', 'Company Name'));?></th>
    <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('country', 'Country'));?></th>
    <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('iata', 'IATA Code'));?></th>
    <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('icao', 'ICAO Code'));?></th>
    <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('callsign', 'Radio Callsign'));?></th>
    <th class="text-center">Active</th>
    <th class="text-right">Actions</th>
  </thead>
  <tbody>
    <?php $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $al): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td class="text-left">
          <a href="<?php echo e(route('admin.airlines.edit', [$al->id])); ?>"><?php echo e($al->name); ?></a>
        </td>
        <td nowrap="true">
          <?php if(filled($al->country)): ?>
            <span class="flag-icon flag-icon-<?php echo e($al->country); ?>" title="<?php echo e($country->alpha2($al->country)['name']); ?>"></span>
          <?php endif; ?>
        </td>
        <td><?php echo e($al->iata); ?></td>
        <td><?php echo e($al->icao); ?></td>
        <td><?php echo e($al->callsign); ?></td>
        <td>
          <?php if($al->active == 1): ?>
            <span class="label label-success">Active</span>
          <?php else: ?>
            <span class="label label-default">Inactive</span>
          <?php endif; ?>
        </td>
        <td class="text-right">
          <?php echo e(Form::open(['route' => ['admin.airlines.destroy', $al->id], 'method' => 'delete'])); ?>

          <a href="<?php echo e(route('admin.airlines.edit', [$al->id])); ?>"
            class='btn btn-sm btn-success btn-icon'><i class="fas fa-pencil-alt"></i></a>
          <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

          <?php echo e(Form::close()); ?>

        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/airlines/table.blade.php ENDPATH**/ ?>