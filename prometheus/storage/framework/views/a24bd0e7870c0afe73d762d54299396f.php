<?php $__currentLoopData = $transaction_groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

  <h3><?php echo e($group['airline']->icao); ?> - <?php echo e($group['airline']->name); ?></h3>

  <table
    id="finances-table"
    style="width: 95%; margin: 0px auto;"
    class="table table-hover table-responsive">

    <thead>
    <th>Expenses</th>
    <th>Credit</th>
    <th>Debit</th>
    </thead>
    <tbody>
    <?php $__currentLoopData = $group['transactions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td>
          <?php echo e($ta->transaction_group); ?>

        </td>
        <td>
          <?php if($ta->sum_credits): ?>
            <?php echo e(money($ta->sum_credits, $ta->currency)); ?>

          <?php endif; ?>
        </td>
        <td>
          <?php if($ta->sum_debits): ?>
            <i><?php echo e(money($ta->sum_debits, $ta->currency)); ?></i>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    
    <tr>
      <td></td>
      <td>
        <?php echo e($group['credits']); ?>

      </td>
      <td>
        <i><?php echo e($group['debits']); ?></i>
      </td>
    </tr>

    
    <tr style="border-top: 3px; border-top-style: double;">
      <td></td>
      <td align="right">
        <b>Total</b>
      </td>
      <td>
        <?php echo e($group['credits']->subtract($group['debits'])); ?>

      </td>
    </tr>

    </tbody>
  </table>

  <?php if(!$loop->last): ?>
    <hr>
  <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/finances/table.blade.php ENDPATH**/ ?>