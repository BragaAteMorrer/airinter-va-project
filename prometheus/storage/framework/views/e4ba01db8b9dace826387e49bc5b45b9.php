<?php $__env->startSection('title', 'Airports'); ?>

<?php $__env->startSection('actions'); ?>
  <li><a href="<?php echo e(route('admin.airports.export')); ?>"><i class="ti-plus"></i>Export to CSV</a></li>
  <li><a href="<?php echo e(route('admin.airports.import')); ?>"><i class="ti-plus"></i>Import from CSV</a></li>
  <li><a href="<?php echo e(route('admin.airports.create')); ?>"><i class="ti-plus"></i>Add New</a></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card">
    <?php echo $__env->make('admin.airports.search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.airports.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-12 text-center">
      <?php echo e($airports->withQueryString()->links('admin.pagination.default')); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.airports.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/airports/index.blade.php ENDPATH**/ ?>