<?php if(count($pirep->acars_logs) > 0): ?>
  <div class="col-12">
    <table class="table table-hover" id="users-table">
      <tbody>
      <?php $__currentLoopData = $pirep->acars_logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td nowrap="true"><?php echo e(show_datetime($log->created_at)); ?></td>
          <td><?php echo e($log->log); ?></td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/flight_log.blade.php ENDPATH**/ ?>