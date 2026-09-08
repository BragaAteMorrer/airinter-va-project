<div style="margin-bottom: 5px;">
  <form class="form" method="post" action="<?php echo e(route('DBasic.manual_award')); ?>">
    <?php echo csrf_field(); ?>
    <table class="table table-striped text-left" style="margin-bottom: 2px;">
      <tr>
        <td style="width: 30%; max-width: 30%;">User</td>
        <td class="text-right">
          <select class="form-control select2" style="width: 98%;" name="ma_user">
            <option value="ZZZ">Select user...</option>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($user->id); ?>"><?php echo e($user->pilot_id.' - '.$user->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </td>
      </tr>
      <tr>
        <td style="width: 30%; max-width: 30%;">Award</td>
        <td class="text-right">
          <select class="form-control select2" style="width: 98%" name="ma_award">
            <option value="ZZZ">Select award to assign...</option>
            <?php $__currentLoopData = $awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($award->id); ?>"><?php echo e($award->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </td>
      </tr>
    </table>
    <input class="button" type="submit" value="Award User">
  </form>
</div><?php /**PATH /home/jewe0363/prometheus/modules/DisposableBasic/Providers/../Resources/views/admin/manual_awards.blade.php ENDPATH**/ ?>