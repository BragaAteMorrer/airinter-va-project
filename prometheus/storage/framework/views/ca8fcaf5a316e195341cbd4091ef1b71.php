<table class="table table-hover table-responsive" id="fares-table">
  <thead>
    <th>Code</th>
    <th>Name</th>
    <th>Type</th>
    <th>Price</th>
    <th>Cost</th>
    <th>Notes</th>
    <th class="text-center">Active</th>
    <th class="text-right">Action</th>
  </thead>
  <tbody>
    <?php $__currentLoopData = $fares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><a href="<?php echo e(route('admin.fares.edit', [$fare->id])); ?>"><?php echo e($fare->code); ?></a></td>
        <td><?php echo e($fare->name); ?></td>
        <td><?php echo e(\App\Models\Enums\FareType::label($fare->type)); ?></td>
        <td><?php echo e($fare->price); ?></td>
        <td><?php echo e($fare->cost); ?></td>
        <td><?php echo e($fare->notes); ?></td>
        <td class="text-center">
          <?php if($fare->active == 1): ?>
            <span class="label label-success">Active</span>
          <?php else: ?>
            <span class="label label-default">Inactive</span>
          <?php endif; ?>
        </td>
        <td class="text-right">
          <?php echo e(Form::open(['route' => ['admin.fares.destroy', $fare->id], 'method' => 'delete'])); ?>

          <a href="<?php echo e(route('admin.fares.edit', [$fare->id])); ?>" class='btn btn-sm btn-success btn-icon'>
            <i class="fas fa-pencil-alt"></i></a>
          <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

          <?php echo e(Form::close()); ?>

        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/fares/table.blade.php ENDPATH**/ ?>