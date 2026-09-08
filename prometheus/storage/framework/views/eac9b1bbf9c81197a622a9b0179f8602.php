<div class="mb-3">
  <label for="<?php echo e($field->slug); ?>" class="form-label">
    <?php echo e($field->name); ?>

    <?php if($field->required === true): ?>
      <span class="text-danger">*</span>
    <?php endif; ?>
    <?php if(filled($field->description)): ?>
      <span class="text-info mx-1"></span>
    <?php endif; ?>
  </label>
  <div class="input-group input-group-sm">
    <?php if(!$field->read_only): ?>
      <input type="text" name="<?php echo e($field->slug); ?>" id="<?php echo e($field->slug); ?>" class="form-control" value="<?php echo e($field->value); ?>" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?>/>
    <?php else: ?>
      <input type="text" class="form-control-plaintext" value="<?php echo e($field->value); ?>" readonly/>
    <?php endif; ?>
  </div>
  <p class="text-danger"><?php echo e($errors->first('field_'.$field->slug)); ?></p>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/custom_fields.blade.php ENDPATH**/ ?>