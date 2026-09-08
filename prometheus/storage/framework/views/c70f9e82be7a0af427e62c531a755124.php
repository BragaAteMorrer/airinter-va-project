<?php $__env->startSection('title', 'Financial Reports'); ?>
<?php $__env->startSection('actions'); ?>
  <li><a href="<?php echo e(route('admin.finances.index')); ?>"><i class="ti-menu-alt"></i>Overview</a></li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <div style="float:right;">
        <?php echo e(Form::select(
                'month_select',
                $months_list,
                $current_month,
                ['id' => 'month_select']
            )); ?>

      </div>

      <?php echo $__env->make('admin.finances.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.finances.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/finances/index.blade.php ENDPATH**/ ?>