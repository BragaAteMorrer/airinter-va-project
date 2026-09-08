<?php $__env->startSection('title', __('common.livemap')); ?>
<?php $__env->startSection('content'); ?>
   <?php echo e(Widget::liveMap()); ?>

   <?php echo app('arrilot.widget')->run('DBasic::ActiveBookings'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/livemap/index.blade.php ENDPATH**/ ?>