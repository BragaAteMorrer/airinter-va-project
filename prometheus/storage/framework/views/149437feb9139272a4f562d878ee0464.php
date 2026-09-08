<?php $__env->startSection('title', 'Expenses'); ?>

<?php $__env->startSection('actions'); ?>
  <li><a href="<?php echo e(route('admin.expenses.export')); ?>"><i class="ti-plus"></i>Export to CSV</a></li>
  <li><a href="<?php echo e(route('admin.expenses.import')); ?>"><i class="ti-plus"></i>Import from CSV</a></li>
  <li><a href="<?php echo e(route('admin.expenses.create')); ?>"><i class="ti-plus"></i>Add New</a></li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php if(!filled($expenses)): ?>
        <p class="text-center">
          There are no expenses
        </p>
      <?php else: ?>
        <?php echo $__env->make('admin.expenses.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/expenses/index.blade.php ENDPATH**/ ?>