<?php if($visible === true): ?>
<?php if($config['disp'] === 'full'): ?>
<div class="card border">
   <div class="card-body widget-desk">
      <div class="text-end">
         <h4 class="mt-0 mb-0 fw-bold"><?php echo e($pstat); ?></h4>
         <p class="mb-0"><?php echo e($sname.' '.$speriod); ?></p>
      </div>
      <div class="widget-icon">
         <i class="ph-fill ph-info"></i>
      </div>
      <div class="clearfix"></div>
   </div>
</div>
<?php else: ?>
<?php echo e($pstat); ?>

<?php endif; ?>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/personal_stats.blade.php ENDPATH**/ ?>