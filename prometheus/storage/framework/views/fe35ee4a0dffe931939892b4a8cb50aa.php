<?php $__env->startSection('title', 'SimBrief Airframes'); ?>
<?php $__env->startSection('actions'); ?>
  <li>
    <a href="<?php echo e(route('admin.airframes.create')); ?>">
      <i class="ti-plus"></i>
      Add New Airframe
    </a>
  </li>
  &nbsp;
  <li>
    <a href="<?php echo e(route('admin.airframes.sbupdate')); ?>">
      <i class="ti-plus"></i>
      Update SimBrief Airframes & Layouts
    </a>
  </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.airframes.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/airframes/index.blade.php ENDPATH**/ ?>