<table class="table table-responsive table-hover" id="flight-fields-table">
  <?php if(count($pirep->fields)): ?>
    <thead>
    <th></th>
    <th>Value</th>
    <th>Source</th>
    </thead>
  <?php endif; ?>
  <tbody>
  <?php $__currentLoopData = $pirep->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td>
        <?php echo e($field->name); ?>

        <?php if($field->required === true): ?>
          <span class="text-danger">*</span>
        <?php endif; ?>
      </td>
      <td>
        <div class="form-group">
          <?php if(!$field->read_only): ?>
            <?php echo e(Form::text($field->slug, $field->value, [
                'class' => 'form-control'
                ])); ?>

          <?php else: ?>
            <p><?php echo e($field->value); ?></p>
          <?php endif; ?>
        </div>
        <p class="text-danger"><?php echo e($errors->first($field->slug)); ?></p>
      </td>
      <td>
        <?php echo e(PirepSource::label($field->source)); ?>

      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/field_values.blade.php ENDPATH**/ ?>