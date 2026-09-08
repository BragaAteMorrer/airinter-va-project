<?php $__env->startSection('title', "Edit \"$aircraft->name\""); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo e(Form::model($aircraft, [
            'route' => ['admin.aircraft.update', $aircraft->id],
            'method' => 'patch',
            'autocomplete' => false,
            ])); ?>

      <?php echo $__env->make('admin.aircraft.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php echo e(Form::close()); ?>

    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.aircraft.expenses', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.common.file_upload', ['model' => $aircraft], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.aircraft.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/aircraft/edit.blade.php ENDPATH**/ ?>