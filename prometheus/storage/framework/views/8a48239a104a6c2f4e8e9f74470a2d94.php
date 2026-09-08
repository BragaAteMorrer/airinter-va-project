<div id="typeratings_table_wrapper">
  <table class="table table-hover table-responsive">
    <thead>
      <th>Type Code</th>
      <th>Name</th>
      <th>Description</th>
      <th></th>
    </thead>
    <tbody>
      <?php $__currentLoopData = $typeratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typerating): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><a href="<?php echo e(route('admin.typeratings.edit', [$typerating->id])); ?>"><?php echo e($typerating->type); ?></a></td>
          <td><?php echo e($typerating->name); ?></td>
          <td><?php echo e($typerating->description); ?></td>
          <td class="text-right">
            <?php echo e(Form::open(['route' => ['admin.typeratings.destroy', $typerating->id], 'method' => 'delete'])); ?>

            <a href="<?php echo e(route('admin.typeratings.edit', [$typerating->id])); ?>" class='btn btn-sm btn-success btn-icon'>
              <i class="fas fa-pencil-alt"></i></a>
            <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

            <?php echo e(Form::close()); ?>

          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/typeratings/table.blade.php ENDPATH**/ ?>