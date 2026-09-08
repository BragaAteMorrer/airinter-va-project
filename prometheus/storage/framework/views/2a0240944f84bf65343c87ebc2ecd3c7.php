<div class="modal fade" id="externalRedirectModal" tabindex="-1" aria-labelledby="externalRedirectModal" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title"><?php echo app('translator')->get('common.external_redirection'); ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo app('translator')->get('common.close'); ?>"></button>
         </div>
         <div class="modal-body">
            <?php echo app('translator')->get('common.abouttoleave'); ?> <span class="text-primary" id="externalRedirectHost"></span>. <?php echo app('translator')->get('common.wanttocontinue'); ?>
            <div class="form-check mt-2">
               <input class="form-check-input" type="checkbox" value="" id="redirectAlwaysTrustThisDomain">
               <label class="form-check-label" for="redirectAlwaysTrustThisDomain">
                  <?php echo app('translator')->get('common.alwaystrustdomain'); ?>
               </label>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo app('translator')->get('common.close'); ?></button>
            <a href="#" target="_blank" class="btn btn-primary" id="externalRedirectUrl"><?php echo app('translator')->get('common.continue'); ?></a>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/external_redirect_modal.blade.php ENDPATH**/ ?>