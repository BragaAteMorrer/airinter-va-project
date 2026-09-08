<?php $__env->startSection('title', 'Sample'); ?>
<?php $__env->startSection('actions'); ?>
  <li>
    <a href="<?php echo e(url('/admin/sample/create')); ?>">
      <i class="ti-plus"></i>
      Add New</a>
  </li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <div class="header"><h4 class="title">Admin Scaffold!</h4></div>
      <p>This view is loaded from module: <?php echo e(config('sample.name')); ?></p>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('sample::layouts.admin', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/Sample/Providers/../Resources/views/admin/index.blade.php ENDPATH**/ ?>