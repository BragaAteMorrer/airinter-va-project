<?php $__currentLoopData = $times; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="col-xxl-2 col-xl-6 col-lg-6 col-md-6 col-sm-12">
   <div class="card border">
      <div class="card-body widget-desk">
         <div class="text-end">
            <h4 class="mt-0 mb-0 fw-bold"><?php echo e($city); ?></h4>
            <p class="mb-0"><?php echo e($info['time']); ?> <span>(<?php echo e($info['gmt_diff']); ?>)</p>
         </div>
         <div class="widget-flag">
            <span class="fi fi-<?php echo e(strtolower($info['country'])); ?> shadow-img fs-3"></span>
         </div>
         <div class="clearfix"></div>
      </div>
   </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php /**PATH /home/jewe0363/prometheus/modules/SPTheme/Providers/../Resources/views/widgets/worldclock.blade.php ENDPATH**/ ?>