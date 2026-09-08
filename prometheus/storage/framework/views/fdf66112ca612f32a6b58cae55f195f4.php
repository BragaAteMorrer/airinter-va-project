<?php if($users_hub->count() > 0): ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-users align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.hpilots'); ?>
         <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($users_hub->count()); ?></span>
      </h4>
      <?php echo $__env->make('DBasic::roster.table', ['users' => $users_hub, 'type' => 'hub'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
</div>
<?php endif; ?>
<?php if($users_off->count() > 0): ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-users align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.vpilots'); ?>
         <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($users_off->count()); ?></span>
      </h4>
      <?php echo $__env->make('DBasic::roster.table', ['users' => $users_off, 'type' => 'visitor'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/hubs/show_pilots.blade.php ENDPATH**/ ?>