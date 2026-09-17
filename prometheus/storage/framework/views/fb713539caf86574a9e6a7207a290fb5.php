<?php $__env->startSection('title', __('errors.404.title')); ?>

<?php $__env->startSection('content'); ?>
  <div class="container registered-page">
    <h3><?php echo app('translator')->get('errors.404.title'); ?></h3>
    <p>
      <?php echo str_replace(':link', config('app.url'), __('errors.404.message')).'<br />'; ?>

      <?php echo e($exception->getMessage()); ?>

    </p>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/promethee/resources/views/layouts/beta/errors/404.blade.php ENDPATH**/ ?>