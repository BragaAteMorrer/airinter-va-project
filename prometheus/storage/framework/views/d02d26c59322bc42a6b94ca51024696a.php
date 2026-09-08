<?php if(filled($details) && $card_view === true): ?>
<div class="card border mb-0">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-cloud-sun align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::widgets.sundetails'); ?></h4>
      <table class="table table-hover table-striped table-responsive-sm table-sm table-borderless align-middle text-nowrap mb-0">
         <tbody>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::widgets.twilight_begin'); ?></td>
               <td class="text-end"><?php echo e($details['twilight_begin'] ?? ''); ?></td>
            </tr>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::widgets.sunrise'); ?></td>
               <td class="text-end"><?php echo e($details['sunrise'] ?? ''); ?></td>
            </tr>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::widgets.sunset'); ?></td>
               <td class="text-end"><?php echo e($details['sunset'] ?? ''); ?></td>
            </tr>
            <tr>
               <td class="fw-bold"><?php echo app('translator')->get('DBasic::widgets.twilight_end'); ?></td>
               <td class="text-end"><?php echo e($details['twilight_end'] ?? ''); ?></td>
            </tr>
         <tbody>
      </table>
      <?php if($footer_note): ?>
      <p class="m-2 text-muted"><?php echo e($footer_note); ?></p>
      <?php endif; ?>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/sunrise_sunset.blade.php ENDPATH**/ ?>