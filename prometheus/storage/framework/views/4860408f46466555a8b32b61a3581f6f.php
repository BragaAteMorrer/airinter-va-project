<?php $__env->startSection('title', 'Subfleets'); ?>

<?php $__env->startSection('actions'); ?>
  <li><a href="<?php echo e(route('admin.subfleets.export')); ?>"><i class="ti-plus"></i>Export to CSV</a>
  <li><a href="<?php echo e(route('admin.subfleets.import')); ?>"><i class="ti-plus"></i>Import from CSV</a></li>
  <li><a href="<?php echo e(route('admin.subfleets.create')); ?>"><i class="ti-plus"></i>Add New Subfleet</a></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.flash.message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php echo $__env->make('admin.subfleets.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php if(filled($trashed)): ?>
        <hr>
        <div class="row mb-2 text-center"><b>Trashed Subfleet Records</b></div>
        <?php echo $__env->make('admin.subfleets.trash_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/subfleets/index.blade.php ENDPATH**/ ?>