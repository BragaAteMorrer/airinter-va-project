<table class="table table-striped table-responsive" id="owners-table">
  <thead>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('user.name', 'User'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('created_at', 'Issued At'));?></th>
    <th>&nbsp;</th>
  </thead>
  <tbody>
    <?php $__currentLoopData = $owners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ow): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td>
          <?php if(filled($ow->user)): ?>
            <a href="<?php echo e(route('admin.users.edit', [$ow->user->id])); ?>"><?php echo e($ow->user->name_private.' ('.$ow->user->ident.')'); ?></a>
          <?php else: ?>
            Deleted User
          <?php endif; ?>
        </td>
        <td><?php echo e($ow->created_at->format('d.M.Y H:i')); ?></td>
        <td>&nbsp;</td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/awards/owners_table.blade.php ENDPATH**/ ?>