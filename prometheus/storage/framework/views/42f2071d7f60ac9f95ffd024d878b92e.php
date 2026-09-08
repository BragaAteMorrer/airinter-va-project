<?php if($aircraft_hub->count() > 0): ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-tilt align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.haircraft'); ?>
         <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($aircraft_hub->count()); ?></span>
      </h4>
      <?php echo $__env->make('DBasic::fleet.table', ['aircraft' => $aircraft_hub, 'compact_view' => true, 'hub_ac' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
</div>
<?php endif; ?>
<?php if($aircraft_off->count() > 0): ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-tilt align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.vaircraft'); ?>
         <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($aircraft_off->count()); ?></span>
      </h4>
      <?php echo $__env->make('DBasic::fleet.table', ['aircraft' => $aircraft_off, 'compact_view' => true, 'visitor_ac' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/hubs/show_fleet.blade.php ENDPATH**/ ?>