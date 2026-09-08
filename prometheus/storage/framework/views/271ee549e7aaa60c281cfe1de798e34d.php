<?php $__env->startSection('title', 'Maintenance'); ?>
<?php $__env->startSection('content'); ?>
  <?php echo $__env->make('flash::message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.maintenance.update', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.maintenance.cron', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.maintenance.caches', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/maintenance/index.blade.php ENDPATH**/ ?>