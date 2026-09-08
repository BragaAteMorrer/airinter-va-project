<?php $__env->startSection('title', 'Edit ' . $pirep->ident ); ?>

<?php $__env->startSection('content'); ?>
  <div class="content">
    <div class="card border-blue-bottom">
      <div class="content">

        
        <div class="row">
          <div class="col-md-8">
            <h5 style="margin-top: 0px;">
              Filed By: <a href="<?php echo e(route('admin.users.edit', [$pirep->user_id])); ?>" target="_blank">
                <?php echo e($pirep->user->ident); ?> <?php echo e($pirep->user->name); ?>

              </a>
            </h5>
          </div>
          <div class="col-md-4">
            <div class="pull-right">
              <?php echo $__env->make('admin.pireps.actions', ['pirep' => $pirep, 'on_edit_page' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
          </div>
        </div>

        <?php echo e(Form::model($pirep, ['route' => ['admin.pireps.update', $pirep->id], 'method' => 'patch', 'autocomplete' => false])); ?>

        <?php echo $__env->make('admin.pireps.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo e(Form::close()); ?>

      </div>
    </div>

    <div class="card border-blue-bottom">
      <div class="content">
        <h4>comments</h4>
        <?php echo $__env->make('admin.pireps.comments', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
    </div>

    <div class="card border-blue-bottom">
      <div class="content">
        <h4>flight log</h4>
        <?php echo $__env->make('admin.pireps.flight_log', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
    </div>

    <div class="card border-blue-bottom">
      <div class="content">
        <div class="pull-right">
          <button id="recalculate-finances"
                  class="btn btn-success"
                  data-pirep-id="<?php echo e($pirep->id); ?>">Recalcuate Finances
          </button>
        </div>
        <h4>transactions</h4>
        <?php echo $__env->make('admin.pireps.transactions', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
    </div>

  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.pireps.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/edit.blade.php ENDPATH**/ ?>