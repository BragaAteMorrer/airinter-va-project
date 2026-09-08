<?php $__env->startSection('title', 'Aircraft'); ?>

<?php $__env->startSection('actions'); ?>
  <?php if(request()->get('subfleet')): ?>
    <li>
      <a href="<?php echo e(route('admin.aircraft.export')); ?><?php echo e('?subfleet='.request()->get('subfleet')); ?>">
        <i class="ti-plus"></i>
        Export to CSV (Selected Subfleet Only)
      </a>
    </li>
  <?php endif; ?>
  <li>
    <a href="<?php echo e(route('admin.aircraft.export')); ?>">
      <i class="ti-plus"></i>
      Export to CSV
    </a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.aircraft.import')); ?>">
      <i class="ti-plus"></i>
      Import from CSV
    </a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.aircraft.create')); ?>?subfleet=<?php echo e($subfleet_id); ?>">
      <i class="ti-plus"></i>
      New Aircraft
    </a>
  </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.aircraft.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php if(filled($trashed)): ?>
        <hr>
        <div class="row mb-2 text-center"><b>Trashed Aircraft Records</b></div>
        <?php echo $__env->make('admin.aircraft.trash_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php endif; ?>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/aircraft/index.blade.php ENDPATH**/ ?>