<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('actions'); ?>
  <li>
    <a href="<?php echo e(route('admin.userfields.index')); ?>"></i>Profile Fields</a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.users.create')); ?>">Add User</a>
  </li>
  <?php if(setting('general.invite_only_registrations', false)): ?>
      <li>
        <a href="<?php echo e(route('admin.invites.index')); ?>">Invites</a>
      </li>
  <?php endif; ?>
  <li>
    <a href="<?php echo e(route('admin.users.index')); ?>?state=0"><?php echo app('translator')->get(UserState::label(UserState::PENDING)); ?></a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.users.index')); ?>?state=1"><?php echo app('translator')->get(UserState::label(UserState::ACTIVE)); ?></a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.users.index')); ?>?state=2"><?php echo app('translator')->get(UserState::label(UserState::REJECTED)); ?></a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.users.index')); ?>?state=3"><?php echo app('translator')->get(UserState::label(UserState::ON_LEAVE)); ?></a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.users.index')); ?>?state=4"><?php echo app('translator')->get(UserState::label(UserState::SUSPENDED)); ?></a>
  </li>
  <li>
    <a href="<?php echo e(route('admin.users.index')); ?>?state=5"><?php echo app('translator')->get(UserState::label(UserState::DELETED)); ?></a>
  </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card">
    <?php echo $__env->make('admin.users.search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo $__env->make('admin.users.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="row">
    <div class="col-12 text-center">
      <?php echo e($users->withQueryString()->links('admin.pagination.default')); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/index.blade.php ENDPATH**/ ?>