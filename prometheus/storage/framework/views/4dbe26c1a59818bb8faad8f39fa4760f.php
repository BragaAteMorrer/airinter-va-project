<?php $__env->startSection('title', 'Ranks'); ?>
<?php $__env->startSection('actions'); ?>
  <li>
    <a href="<?php echo e(route('admin.ranks.create')); ?>">
      <i class="ti-plus"></i>
      Add New</a>
  </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.ranks.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/ranks/index.blade.php ENDPATH**/ ?>