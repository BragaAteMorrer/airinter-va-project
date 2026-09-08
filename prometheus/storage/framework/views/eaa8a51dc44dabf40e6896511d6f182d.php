<?php echo e(Form::model($all_rules, ['route' => ['vmsacars.admin.config'], 'method' => 'post'])); ?>

<div class="card border-blue-bottom">
  <div class="content">
    <div class="header">
      <h3>Config</h3>
      <p class="description">
        Configuration for ACARS
      </p>
    </div>
    <table class="table table-hover" id="flights-table">
      <?php $__currentLoopData = $all_config; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td width="70%">
          <p><?php echo e($config->name); ?></p>
          <p class="description">
            <?php if($config->description): ?>
            <?php $__env->startComponent('admin.components.info'); ?>
            <?php echo e($config->description); ?>

            <?php if(!empty($config->default)): ?>
            <i>(default <?php echo e($config->default); ?>)</i>
            <?php endif; ?>
            <?php echo $__env->renderComponent(); ?>
            <?php endif; ?>
          </p></td>
        <td align="center">
          <?php if($config->type === 'boolean' || $config->type === 'bool'): ?>
          <?php echo e(Form::hidden($config->id, 0)); ?>

          <?php echo e(Form::checkbox($config->id, null, $config->value)); ?>

          <?php elseif($config->type === 'int'): ?>
          <?php echo e(Form::number($config->id, $config->value, ['class'=>'form-control'])); ?>

          <?php elseif($config->type === 'number'): ?>
          <?php echo e(Form::number($config->id, $config->value, ['class'=>'form-control', 'step' => '0.01'])); ?>

          <?php elseif($config->type === 'select'): ?>
          <?php echo e(Form::select(
          $config->id,
          list_to_assoc(explode(',', $config->options)),
          $config->value,
          ['class' => 'select2', 'style' => 'width: 100%; text-align: left;'])); ?>

          <?php else: ?>
          <?php echo e(Form::input('text', $config->id, $config->value, ['class' => 'form-control'])); ?>

          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
  </div>
  <div class="content">
    <div class="text-right">
      <?php echo e(Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

    </div>
  </div>
</div>
<?php echo e(Form::close()); ?>

<?php /**PATH /home/jewe0363/prometheus/modules/VMSAcars/Providers/../Resources/views/admin/config.blade.php ENDPATH**/ ?>