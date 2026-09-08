<?php $__env->startSection('title', 'Activities'); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.activities.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-12 text-center">
      <?php echo e($activities->withQueryString()->links('admin.pagination.default')); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/activities/index.blade.php ENDPATH**/ ?>