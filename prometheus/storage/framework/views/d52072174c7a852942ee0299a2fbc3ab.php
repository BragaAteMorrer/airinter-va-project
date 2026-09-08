<div style="margin-bottom: 5px;">
  <form class="form" method="post" action="<?php echo e(route('DBasic.manual_payment')); ?>">
    <?php echo csrf_field(); ?>
    <table class="table table-striped text-left" style="margin-bottom: 2px;">
      <tr>
        <td style="width: 30%; max-width: 30%;">User</td>
        <td class="text-right">
          <select class="form-control select2" style="width: 98%;" name="mp_user">
            <option value="ZZZ">Select user...</option>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($user->id); ?>"><?php echo e($user->pilot_id.' - '.$user->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </td>
      </tr>
      <tr>
        <td style="width: 30%; max-width: 30%;">Amount (<?php echo e(setting('units.currency')); ?>)</td>
        <td class="text-right input-group-sm">
          <input type="number" class="form-control" name="mp_amount" step="1" min="1" max="100000" placeholder="0"/>
        </td>
      </tr>
    </table>
    <input class="button" type="submit" value="Transfer Money to User">
  </form>
</div><?php /**PATH /home/jewe0363/prometheus/modules/DisposableBasic/Providers/../Resources/views/admin/manual_payment.blade.php ENDPATH**/ ?>