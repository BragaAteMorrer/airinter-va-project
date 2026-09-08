<div class="content table-responsive table-full-width">
  <table class="table table-hover table-responsive" id="subfleets-table">
    <thead>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', 'Name'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('airline.name', 'Airline'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('type', 'Type Code'));?></th>
      <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('hub_id', 'Base'));?></th>
      <th>Aircraft</th>
      <th class="text-right">Actions</th>
    </thead>
    <tbody>
      <?php $__currentLoopData = $subfleets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subfleet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($subfleet->name); ?></td>
          <td><?php echo e(optional($subfleet->airline)->name); ?></td>
          <td><?php echo e($subfleet->type); ?></td>
          <td><?php echo e($subfleet->hub_id); ?></td>
          <td><?php echo e($subfleet->aircraft->count()); ?></td>
          <td class="text-right">
            <?php echo e(Form::open(['route' => ['admin.subfleets.destroy', $subfleet->id], 'method' => 'delete'])); ?>


            <a href="<?php echo e(route('admin.aircraft.index')); ?>?subfleet=<?php echo e($subfleet->id); ?>" class='btn btn-sm btn-info text-black'>Manage Aircraft</a>
            <a href="<?php echo e(route('admin.subfleets.edit', [$subfleet->id])); ?>" class='btn btn-sm btn-success text-black'>Edit Subfleet</a>

            <?php echo e(Form::button('Delete', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger', 'onclick' => "return confirm('Are you sure?')"])); ?>

            <?php echo e(Form::close()); ?>

          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/subfleets/table.blade.php ENDPATH**/ ?>