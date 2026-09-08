<div class="col-12">
  <?php if(count($journal['transactions']) > 0): ?>
    <table class="table table-hover" id="users-table">
      <tbody>
      <?php $__currentLoopData = $journal['transactions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($entry->memo); ?></td>
          <td>
            <?php if($entry->credit): ?>
              <?php echo e(money($entry->credit, setting('units.currency'))); ?>

            <?php endif; ?>
          </td>
          <td>
            <?php if($entry->debit): ?>
              <?php echo e(money($entry->debit, setting('units.currency'))); ?>

            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      
      <tr>
        <td></td>
        <td>
          <?php echo e($journal['credits']); ?>

        </td>
        <td>
          <i><?php echo e($journal['debits']); ?></i>
        </td>
      </tr>

      
      <tr style="border-top: 3px; border-top-style: double;">
        <td></td>
        <td align="right">
          <b>Total</b>
        </td>
        <td>
          <?php echo e($journal['credits']->subtract($journal['debits'])); ?>

        </td>
      </tr>
      </tbody>
    </table>
</div>
<?php endif; ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/transactions.blade.php ENDPATH**/ ?>