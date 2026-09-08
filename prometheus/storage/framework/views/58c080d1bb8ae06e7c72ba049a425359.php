<?php $__env->startSection('title', "Edit $subfleet->name"); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo e(Form::model($subfleet, ['route' => ['admin.subfleets.update', $subfleet->id], 'method' => 'patch', 'autocomplete' => false])); ?>

      <?php echo $__env->make('admin.subfleets.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php echo e(Form::close()); ?>

    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.subfleets.ranks', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.subfleets.type_ratings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.subfleets.fares', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.subfleets.expenses', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.common.file_upload', ['model' => $subfleet], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.subfleets.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/subfleets/edit.blade.php ENDPATH**/ ?>