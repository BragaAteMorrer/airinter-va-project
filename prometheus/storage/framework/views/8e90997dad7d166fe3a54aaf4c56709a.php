<?php $__env->startSection('title', 'Pilot Reports'); ?>
<?php $__env->startSection('actions'); ?>
  <li><a href="<?php echo e(route('admin.pirepfields.index')); ?>"><i class="ti-menu-alt"></i>PIREP Fields</a></li>
  <li><a href="<?php echo e(route('admin.pireps.index')); ?>?search=state:<?php echo e(\App\Models\Enums\PirepState::PENDING); ?>"><i class="ti-plus"></i>Pending</a></li>
  <li><a href="<?php echo e(route('admin.pireps.index')); ?>?search=state:<?php echo e(\App\Models\Enums\PirepState::REJECTED); ?>"><i class="ti-plus"></i>Rejected</a></li>
  <li><a href="<?php echo e(route('admin.pireps.index')); ?>?search=state:<?php echo e(\App\Models\Enums\PirepState::ACCEPTED); ?>"><i class="ti-plus"></i>Accepted</a></li>
  <li><a href="<?php echo e(route('admin.pireps.index')); ?>"><i class="ti-plus"></i>View All</a></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.pireps.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>
  <div class="row">
    <div class="col-12 text-center">
      <?php echo e($pireps->withQueryString()->links('admin.pagination.default')); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.pireps.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/index.blade.php ENDPATH**/ ?>