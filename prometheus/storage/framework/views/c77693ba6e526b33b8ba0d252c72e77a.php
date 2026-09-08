<?php if($user->awards->count() > 0): ?>
  <table class="table table-hover">
  <?php $__currentLoopData = $user->awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($award->name); ?></td>
      <td><?php echo e($award->description); ?></td>
      <td>
        <?php echo e(Form::open(['url' => url('/admin/users/'.$user->id.'/award/'.$award->id),
              'method' => 'delete', 'class' => 'pjax_form form-inline'])); ?>

        <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit',
                         'class' => 'btn btn-danger btn-small',
                         'onclick' => "return confirm('Are you sure?')",
                         ])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </table>
<?php else: ?>
  <div class="jumbotron">
    <p class="text-center">This user has no awards</p>
  </div>
<?php endif; ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/awards.blade.php ENDPATH**/ ?>