<?php if($settings): ?>
  <div style="margin-bottom: 5px;">
    <form class="form" method="post" action="<?php echo e(route('DBasic.settings_update')); ?>">
      <?php echo csrf_field(); ?>
      <table class="table table-striped text-left" style="margin-bottom: 2px;">
        <?php $__currentLoopData = $settings->where('group', $group)->sortBy('order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr>
            <td style="width:30%; max-width: 30%;"><?php echo e($st->name); ?></td>
            <td>
              <?php if($st->field_type === 'check'): ?>
                <input type="hidden" name="<?php echo e($st->id); ?>" value="false">
                <input class="form-control" type="checkbox" name="<?php echo e($st->id); ?>" value="true" <?php if($st->value === 'true'): ?> checked <?php endif; ?>>
              <?php elseif($st->field_type === 'select'): ?>
                <?php $values = explode(',', $st->options); ?>
                <select class="form-control" name="<?php echo e($st->id); ?>">
                  <?php $__currentLoopData = $values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($value); ?>" <?php if($st->value === $value || !filled($st->value) && $st->default === $value): ?> selected <?php endif; ?>><?php echo e($value); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              <?php else: ?>
                <input
                  class="form-control"
                  <?php if($st->field_type === 'decimal'): ?>
                    type="number" step="0.0001" min="0" max="9999"
                  <?php elseif($st->field_type === 'numeric'): ?>
                    type="number" step="1" <?php if($st->key === 'dbasic.ar_marginlrate'): ?> min="-9999" max="0" <?php else: ?> min="0" max="9999" <?php endif; ?>
                  <?php else: ?>
                    type="text" maxlength="500"
                  <?php endif; ?>
                  name="<?php echo e($st->id); ?>" placeholder="<?php echo e($st->default); ?>" value="<?php echo e($st->value); ?>">
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </table>
      <input type="hidden" name="group" value="<?php echo e($group); ?>">
      <input class="button" type="submit" value="Save Section Settings">
    </form>
  </div>
  
  <style>
    ::placeholder { color: indianred !important; opacity: 0.6 !important; }
    :-ms-input-placeholder { color: indianred !important; }
    ::-ms-input-placeholder { color: indianred !important; }
  </style>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/modules/DisposableBasic/Providers/../Resources/views/admin/settings_table.blade.php ENDPATH**/ ?>