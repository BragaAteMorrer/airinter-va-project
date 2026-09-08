<?php $__env->startSection('title', 'Add Award'); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo e(Form::open(['route' => 'admin.awards.store', 'autocomplete' => false])); ?>

      <?php echo $__env->make('admin.awards.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php echo e(Form::close()); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.awards.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/awards/create.blade.php ENDPATH**/ ?>