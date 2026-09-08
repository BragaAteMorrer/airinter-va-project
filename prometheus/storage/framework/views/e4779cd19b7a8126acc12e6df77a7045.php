<table class="table table-hover table-responsive" id="roles-table">
  <thead>
  <th>Name</th>
  <th class="text-center">Members</th>
  <th class="text-right">Actions</th>
  </thead>
  <tbody>
  <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($role->display_name); ?></td>
      <td class="text-center"><?php echo e($role->users_count); ?></td>
      <td class="text-right">
        <?php echo e(Form::open(['route' => ['admin.roles.destroy', $role->id], 'method' => 'delete'])); ?>

        <a href="<?php echo e(route('admin.roles.edit', [$role->id])); ?>"
           class='btn btn-sm btn-success btn-icon'><i class="fas fa-pencil-alt"></i></a>
        <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/roles/table.blade.php ENDPATH**/ ?>