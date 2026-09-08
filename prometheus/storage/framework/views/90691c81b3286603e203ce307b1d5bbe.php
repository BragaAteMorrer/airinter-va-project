<?php $__env->startComponent('mail::message'); ?>
  # New PIREP Submitted

  A new PIREP has been submitted by <?php echo e($pirep->user->ident); ?> <?php echo e($pirep->user->name); ?>


  <?php $__env->startComponent('mail::button', ['url' => route('admin.pireps.edit', [$pirep->id])]); ?>
    View PIREP
  <?php echo $__env->renderComponent(); ?>

  Thanks,<br>
  <?php echo e(config('app.name')); ?>

<?php echo $__env->renderComponent(); ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/notifications/mail/admin/pirep/submitted.blade.php ENDPATH**/ ?>