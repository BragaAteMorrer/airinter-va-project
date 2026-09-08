<?php $__env->startSection('title', 'Awards'); ?>
<?php $__env->startSection('actions'); ?>
  <li>
    <a href="<?php echo route('admin.awards.create'); ?>">
      <i class="ti-plus"></i>
      Add New
    </a>
  </li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
  <div class="card">
    <?php echo $__env->make('admin.awards.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/awards/index.blade.php ENDPATH**/ ?>