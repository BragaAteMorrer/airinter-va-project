<?php if($pirep->fares): ?>
  <table class="table table-hover table-responsive">
    <thead>
      <th>Fare</th>
      <th>Count</th>
      <th>Price</th>
      <th>Capacity</th>
    </thead>
    </thead>
    <tbody>
    <?php $__currentLoopData = $pirep->fares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e($fare->name); ?> (<?php echo e($fare->code); ?>)</td>
        <td>
          <div class="form-group">
            <?php if(isset($pirep) && $pirep->read_only): ?>
              <p><?php echo e($fare->count); ?></p>
            <?php else: ?>
              <?php echo e(Form::number('fare_'.$fare->id.'_count', $fare->count, [
                  'class' => 'form-control',
                  'min' => 0,
                  'step' => '0.01',
                  ])); ?>

            <?php endif; ?>
          </div>
        </td>
        <td>
          <?php if(isset($pirep) && $pirep->read_only): ?>
              <p><?php echo e($fare->price); ?></p>
            <?php else: ?>
              <?php echo e(Form::number('fare_'.$fare->id.'_price', $fare->price, [
                  'class' => 'form-control',
                  'min' => 0,
                  'step' => '0.01',
                  ])); ?>

            <?php endif; ?>
        </td>
        <td>
          <?php echo e($fare->capacity); ?>

        </td>

      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
<?php endif; ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/fares.blade.php ENDPATH**/ ?>