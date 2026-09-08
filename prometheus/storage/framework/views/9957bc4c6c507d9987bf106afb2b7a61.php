<?php echo e(Form::model($all_rules, ['route' => ['vmsacars.admin.rules'], 'method' => 'post'])); ?>

<div class="card border-blue-bottom">
  <div class="content">
    <div class="header">
      <h3>Rules</h3>
      <p class="description">
        These are rules that a PIREP is rated by. There are thresholds for some rules
        and the points that are deducted from the PIREP score for a violation

      <ul>
        <li><strong>Parameter</strong> - the threshold at which this is triggered</li>
        <li><strong>Delay</strong> - Only trigger a violation after this amount of time (seconds)
        </li>
        <li><strong>Repeatable</strong> - If this violaton can be repeated</li>
        <li><strong>Cooldown</strong> - If repeatable, wait this time until triggering again
          (seconds)
        </li>
      </ul>
      </p>
    </div>
    <table class="table table-hover" id="flights-table">
      <thead>
      <th></th>
      <th>Threshold</th>
      <th>Points</th>
      <th>Delay</th>
      <th>Repeatable</th>
      <th>Cooldown</th>
      <th>Enabled</th>
      </thead>

      <?php $__currentLoopData = $all_rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td width="70%">
          <p><?php echo e($rule->name); ?></p>
          <p class="description">
            <?php echo e($rule->description); ?>

          </p>
        </td>
        <td>
          <?php if($rule->has_parameter): ?>
          <?php echo e(Form::input('text', $rule->id.'_parameter', $rule->parameter, [
          'class' => 'form-control',
          'style' => 'width: 5em',
          ])); ?>

          <?php endif; ?>
        </td>
        <td>
          <?php echo e(Form::number($rule->id.'_points', $rule->points, [
          'class' => 'form-control',
          'style' => 'width: 5em',
          ])); ?>

        </td>
        <td>
          <?php echo e(Form::number($rule->id.'_delay', $rule->delay, [
          'class' => 'form-control', 'style' => 'width: 5em',
          ])); ?>

        </td>
        <td align="center">
          <?php echo e(Form::hidden($rule->id.'_repeatable', 0)); ?>

          <?php echo e(Form::checkbox($rule->id.'_repeatable', null, $rule->repeatable)); ?>

        </td>
        <td>
          <?php echo e(Form::number($rule->id.'_cooldown', $rule->cooldown, [
          'class' => 'form-control',
          'style' => 'width: 5em',
          ])); ?>

        </td>
        <td align="center">
          <?php echo e(Form::hidden($rule->id.'_enabled', 0)); ?>

          <?php echo e(Form::checkbox($rule->id.'_enabled', null, $rule->enabled)); ?>

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

<?php /**PATH /home/jewe0363/prometheus/modules/VMSAcars/Providers/../Resources/views/admin/rules.blade.php ENDPATH**/ ?>