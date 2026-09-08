<?php $__env->startSection('title', 'Flights'); ?>

<?php $__env->startSection('actions'); ?>
  <li>
    <a href="<?php echo e(route('admin.flights.export')); ?><?php if(request()->get('airline_id')): ?><?php echo e('?airline_id='.request()->get('airline_id')); ?><?php endif; ?>">
      <i class="ti-plus"></i>
      Export to CSV <?php if(request()->get('airline_id')): ?> (Selected Airline) <?php endif; ?>
    </a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.flights.import')); ?>"><i class="ti-plus"></i>Import from CSV</a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.flights.create')); ?>">
      <i class="ti-plus"></i>
      Add Flight</a>
  </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card">
    <?php echo $__env->make('admin.flights.search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.flights.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-12 text-center">
      <?php echo e($flights->withQueryString()->links('admin.pagination.default')); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
  <?php echo $__env->make('admin.scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/flights/index.blade.php ENDPATH**/ ?>