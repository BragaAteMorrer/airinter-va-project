<table class="table table-hover">
  <tr>
    <td colspan="2"><h5>User Details</h5></td>
  </tr>
  <tr>
    <td>Total Flights</td>
    <td><?php echo e($user->flights); ?></td>
  </tr>
  <tr>
    <td>Flight Time</td>
    <td><?php echo \App\Support\Units\Time::minutesToTimeString($user->flight_time); ?></td>
  </tr>
  <tr>
    <td>Registered On</td>
    <td><?php echo e(show_datetime($user->created_at)); ?></td>
  </tr>
  <tr>
    <td>E-Mail Verified On</td>
    <td>
      <?php if(filled($user->email_verified_at)): ?>
        <?php echo e(show_datetime($user->email_verified_at)); ?>

      <?php else: ?>
        <span class="btn btn-sm btn-danger mx-1 my-0 p-1">USER E-MAIL NOT VERIFIED !!!</span>
      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td>Last Login</td>
    <td>
      <?php if(filled($user->lastlogin_at)): ?>
        <?php echo e(show_datetime($user->lastlogin_at)); ?>

      <?php endif; ?>
    </td>
  </tr>
  <tr>
    <td>IP Address</td>
    <td><?php echo e($user->last_ip ?? '-'); ?></td>
  </tr>
  <tr>
    <td><?php echo app('translator')->get('toc.title'); ?></td>
    <td><?php echo e($user->toc_accepted ? __('common.yes') : __('common.no')); ?></td>
  </tr>
  <tr>
    <td><?php echo app('translator')->get('profile.opt-in'); ?></td>
    <td><?php echo e($user->opt_in ? __('common.yes') : __('common.no')); ?></td>
  </tr>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/details.blade.php ENDPATH**/ ?>