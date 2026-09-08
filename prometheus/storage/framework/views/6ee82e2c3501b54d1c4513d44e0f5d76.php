<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-books align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::common.reports'); ?>
         <span class="float-end fw-normal"><?php echo app('translator')->get('DBasic::common.total'); ?> <?php echo e($pireps->total()); ?></span>
      </h4>
      <?php echo $__env->make('DBasic::pireps.table_compact', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/hubs/show_reports.blade.php ENDPATH**/ ?>