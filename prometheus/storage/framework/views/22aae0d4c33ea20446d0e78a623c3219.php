<div class="row row-cols-lg-2">
   <?php if($flights_dpt->count() > 0): ?>
   <div class="col">
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-takeoff align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.hdeps'); ?>
               <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($flights_dpt->count()); ?></span>
            </h4>
            <?php echo $__env->make('DBasic::flights.table', ['flights' => $flights_dpt, 'type' => 'dpt'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <?php if($flights_arr->count() > 0): ?>
   <div class="col">
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.harrs'); ?>
               <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($flights_arr->count()); ?></span>
            </h4>
            <?php echo $__env->make('DBasic::flights.table', ['flights' => $flights_arr, 'type' => 'arr'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
      </div>
   </div>
   <?php endif; ?>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/hubs/show_flights.blade.php ENDPATH**/ ?>