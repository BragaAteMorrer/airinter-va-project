<?php $__env->startSection('title', __('errors.404.title')); ?>

<?php $__env->startSection('content'); ?>
  <div class="d-flex align-items-center justify-content-center">
    <div class="text-center">
      <h1 class="display-1 fw-bold">404</h1>
      <h3 class="fs-3"><?php echo app('translator')->get('errors.404.title'); ?></h3>
      <p class="lead">
        Well, this is embarrassing, the page you requested does not exist.
      </p>
      <a href="<?php echo e(route('frontend.home')); ?>" class="btn btn-primary">Go Home</a>
      <a href="<?php echo e(url()->previous()); ?>" class="btn btn-primary">Go Back</a>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/seven/errors/404.blade.php ENDPATH**/ ?>