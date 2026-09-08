<div class="content table-responsive table-full-width">
  <table class="table table-hover" id="activities-table">
    <thead>
    <th>Action</th>
    <th>Causer</th>
    <th>Date</th>
    <th class="text-right">Actions</th>
    </thead>
    <tbody>
    <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e(class_basename($activity->subject_type).' '. $activity->event); ?></td>
        <td>
          <?php if(class_basename($activity->causer_type) === 'User'): ?>
            <a href="<?php echo e(route('admin.users.edit', [$activity->causer_id])); ?>">
              <?php echo e($activity->causer_id .' | '. $activity->causer->name_private); ?>

            </a>
          <?php else: ?>
            <?php echo e($activity->causer_id.' | '. class_basename($activity->causer_type)); ?>

          <?php endif; ?>
        </td>
        <td><?php echo e($activity->created_at->diffForHumans().' | '.$activity->created_at->format('d.M')); ?></td>
        <td class="text-right">
          <a href="<?php echo e(route('admin.activities.show', [$activity->id])); ?>" class='btn btn-sm btn-success btn-icon'><i class="fas fa-eye"></i> View Details</a>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/activities/table.blade.php ENDPATH**/ ?>