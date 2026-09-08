<?php if($user->fields): ?>
  <table class="table table-hover">
    <tr>
      <td colspan="2"><h5>Custom Fields</h5></td>
    </tr>
    
    <?php $__currentLoopData = $user->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e($field->field->name); ?></td>
        <td>
          <?php if(in_array($field->name, ['IVAO', 'IVAO ID'])): ?>
            <a href='https://www.ivao.aero/Member.aspx?ID=<?php echo e($field->value); ?>' target='_blank'><?php echo e($field->value); ?></a>
          <?php elseif(in_array($field->name, ['VATSIM', 'VATSIM CID', 'VATSIM ID'])): ?>
            <a href='https://stats.vatsim.net/search_id.php?id=<?php echo e($field->value); ?>' target='_blank'><?php echo e($field->value); ?></a>
          <?php else: ?>
            <?php echo e($field->value); ?>

          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </table>
<?php endif; ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/custom_fields.blade.php ENDPATH**/ ?>