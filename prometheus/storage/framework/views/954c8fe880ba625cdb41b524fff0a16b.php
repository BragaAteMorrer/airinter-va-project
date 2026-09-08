<div id="ranks_table_wrapper">
  <table class="table table-hover table-responsive">
    <thead>
    <th>Name</th>
    <th class="text-center">Hours</th>
    <th class="text-center">Auto Approve Acars</th>
    <th class="text-center">Auto Approve Manual</th>
    <th class="text-center">Auto Promote</th>
    <th></th>
    </thead>
    <tbody>
    <?php $__currentLoopData = $ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><a href="<?php echo e(route('admin.ranks.edit', [$rank->id])); ?>"><?php echo e($rank->name); ?></a></td>
        <td class="text-center"><?php echo e($rank->hours); ?></td>
        <td class="text-center">
          <?php if($rank->auto_approve_acars): ?>
            <span class="label label-success">Yes</span>
          <?php else: ?>
            <span class="label label-default">No</span>
          <?php endif; ?>
        </td>
        <td class="text-center">
          <?php if($rank->auto_approve_manual): ?>
            <span class="label label-success">Yes</span>
          <?php else: ?>
            <span class="label label-default">No</span>
          <?php endif; ?>
        </td>
        <td class="text-center">
          <?php if($rank->auto_promote): ?>
            <span class="label label-success">Yes</span>
          <?php else: ?>
            <span class="label label-default">No</span>
          <?php endif; ?>
        </td>
        <td class="text-right">
          <?php echo e(Form::open(['route' => ['admin.ranks.destroy', $rank->id], 'method' => 'delete'])); ?>

          <a href="<?php echo e(route('admin.ranks.edit', [$rank->id])); ?>" class='btn btn-sm btn-success btn-icon'>
            <i class="fas fa-pencil-alt"></i></a>
          <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

          <?php echo e(Form::close()); ?>

        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/ranks/table.blade.php ENDPATH**/ ?>