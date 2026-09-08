<?php $__env->startComponent('mail::message'); ?>
  # PIREP Accepted!

  Your PIREP has been accepted

  <?php $__env->startComponent('mail::button', ['url' => route('frontend.pireps.show', [$pirep->id])]); ?>
    View PIREP
  <?php echo $__env->renderComponent(); ?>

  Thanks,<br>
  <?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/notifications/mail/pirep/accepted.blade.php ENDPATH**/ ?>