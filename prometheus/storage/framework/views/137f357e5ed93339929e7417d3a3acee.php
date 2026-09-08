<?php $__env->startSection('title', "Edit \"$award->name\" Award"); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php echo e(Form::model($award, ['route' => ['admin.awards.update', $award->id], 'method' => 'patch', 'autocomplete' => false])); ?>

      <?php echo $__env->make('admin.awards.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php echo e(Form::close()); ?>

    </div>
  </div>
  <?php if(filled($owners)): ?>
    <div class="card border-blue-bottom">
      <?php echo $__env->make('admin.awards.owners_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>
  <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.awards.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/awards/edit.blade.php ENDPATH**/ ?>