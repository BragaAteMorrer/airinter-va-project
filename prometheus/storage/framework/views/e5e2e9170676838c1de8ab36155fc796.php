<?php $__env->startSection('title', 'Edit '. $user->name); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo e(Form::model($user, ['route' => ['admin.users.update', $user->id], 'method' => 'patch', 'autocomplete' => false])); ?>

      <?php echo $__env->make('admin.users.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

      <div class="row">
        <div class="form-group col-sm-12 text-right">
          
          &nbsp;
          <?php if(!$user->email_verified_at): ?>
            <a href="<?php echo e(route('admin.users.verify_email', [$user->id])); ?>" class="btn btn-danger">Verify email manually</a>
          <?php else: ?>
            <a href="<?php echo e(route('admin.users.request_email_verification', [$user->id])); ?>" class="btn btn-warning">Request new email verification</a>
          <?php endif; ?>

          <?php echo e(Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

          <a href="<?php echo e(route('admin.users.index')); ?>" class="btn btn-default">Cancel</a>
        </div>
      </div>
      <?php echo e(Form::close()); ?>


      <div class="row">
        <div class="form-group col-sm-6">
          <?php echo $__env->make('admin.users.custom_fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
        <div class="form-group col-sm-6">
          <?php echo $__env->make('admin.users.details', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <div class="header">
        <h3>Type Ratings</h3>
      </div>
      <?php echo $__env->make('admin.users.type_ratings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <div class="header">
        <h3>Awards</h3>
      </div>
      <?php echo $__env->make('admin.users.awards', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  </div>

  <div class="card border-blue-bottom">
    <div class="content">
      <div class="header">
        <h3>PIREPs</h3>
      </div>

      <?php echo $__env->make('admin.pireps.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

      <div class="row">
        <div class="col-12 text-center">
          <?php echo e($pireps->withQueryString()->links('admin.pagination.default')); ?>

        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.users.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/edit.blade.php ENDPATH**/ ?>