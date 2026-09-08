<?php echo e(Form::model($grouped_settings, ['route' => ['admin.settings.update'], 'method' => 'post'])); ?>

<?php $__currentLoopData = $grouped_settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $settings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="card border-blue-bottom">
    <div class="content table-responsive">
      <div class="row">
        <table class="table table-hover" id="flights-table">
          <thead>
          <th colspan="2">
            <h5><?php echo e($group); ?></h5>
          </th>
          </thead>

          <?php $__currentLoopData = $settings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <td width="70%">
                <p><?php echo e($setting->name); ?></p>
                <p class="description">
                  <?php $__env->startComponent('admin.components.info'); ?>
                    <?php echo e($setting->description); ?>

                  <?php echo $__env->renderComponent(); ?>
                </p></td>
              <td align="center">
                <?php if($setting->type === 'date'): ?>
                  <?php echo e(Form::input('text', $setting->id, $setting->value, ['class' => 'form-control', 'id' => 'datepicker'])); ?>

                <?php elseif($setting->type === 'boolean' || $setting->type === 'bool'): ?>
                  <?php echo e(Form::hidden($setting->id, 0)); ?>

                  <?php echo e(Form::checkbox($setting->id, null, $setting->value)); ?>

                <?php elseif($setting->type === 'int'): ?>
                  <?php echo e(Form::number($setting->id, $setting->value, ['class'=>'form-control'])); ?>

                <?php elseif($setting->type === 'number'): ?>
                  <?php echo e(Form::number($setting->id, $setting->value, ['class'=>'form-control', 'step' => '0.01'])); ?>

                <?php elseif($setting->type === 'select'): ?>

                  <?php if($setting->id === 'general_theme'): ?>
                    <?php echo e(Form::select(
                          $setting->id,
                          list_to_assoc($themes),
                          $setting->value,
                          ['class' => 'select2', 'style' => 'width: 100%; text-align: left;'])); ?>

                  <?php elseif($setting->id === 'units_currency'): ?>
                    <?php echo e(Form::select(
                          $setting->id,
                          $currencies,
                          $setting->value,
                          ['class' => 'select2', 'style' => 'width: 100%; text-align: left;'])); ?>

                  <?php else: ?>
                    <?php echo e(Form::select(
                            $setting->id,
                             list_to_assoc(explode(',', $setting->options)),
                             $setting->value,
                             ['class' => 'select2', 'style' => 'width: 100%; text-align: left;'])); ?>

                  <?php endif; ?>
                <?php else: ?>
                  <?php echo e(Form::input('text', $setting->id, $setting->value, ['class' => 'form-control'])); ?>

                <?php endif; ?>

              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
      </div>

      

    </div>
  </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<div class="card">
  <div class="content">
    <div class="row">
      <div class="col-sm-12 text-right">
        <?php echo e(Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

        <a href="<?php echo e(route('admin.subfleets.index')); ?>" class="btn btn-default">Cancel</a>
      </div>
    </div>
  </div>
</div>

<?php echo e(Form::close()); ?>


<script>
  $(document).ready(function () {
    $('#datepicker').datetimepicker({
      format: "YYYY-MM-DD"
    });
  });
</script>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/settings/table.blade.php ENDPATH**/ ?>