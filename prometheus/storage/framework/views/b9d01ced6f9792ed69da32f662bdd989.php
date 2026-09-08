<div class="row">
  <!-- Code Field -->
  <div class="form-group col-sm-4">
    <div class="form-container">
      <h6><i class="fas fa-keyboard"></i>
        &nbsp;Name
      </h6>
      <div class="form-container-body">
        <div class="row">
          <div class="form-group col-sm-12">
            <?php echo e(Form::text('display_name', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('display_name')); ?></p>
          </div>
        </div>
      </div>
    </div>
    <div class="form-container">
      <h6><i class="fas fa-check-square"></i>
        Features
      </h6>
      <div class="form-container-body">
        <div class="row">
          <div class="form-group col-sm-12">
            <div class="checkbox">
              <?php echo e(Form::hidden('disable_activity_checks', 0)); ?>

              <?php echo e(Form::checkbox('disable_activity_checks', 1)); ?>

              <?php echo e(Form::label('disable_activity_checks', 'disable activity checks')); ?>

              <p class="text-danger"><?php echo e($errors->first('disable_activity_checks')); ?></p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Permissions Field -->
  <div class="form-group col-sm-8">
    <div class="form-container">
      <h6><i class="fas fa-check-square"></i>
        &nbsp;Permissions
      </h6>
      <div class="form-container-body">
        <div class="row">
          <div class="form-group col-sm-12">
            <?php $__currentLoopData = $permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="checkbox">
                <label class="checkbox-inline">
                  <?php echo e(Form::hidden('permissions[]', false)); ?>

                  <?php echo e(Form::checkbox('permissions[]', $p->id)); ?>

                  <?php echo e(Form::label('permissions[]', $p->display_name)); ?> - <span
                    class="description"><?php echo e($p->description); ?></span>
                </label>
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <!-- Submit Field -->
  <div class="form-group col-sm-12">
    <div class="pull-right">
      <?php echo e(Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

      <a href="<?php echo e(route('admin.roles.index')); ?>" class="btn btn-default">Cancel</a>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/roles/fields.blade.php ENDPATH**/ ?>