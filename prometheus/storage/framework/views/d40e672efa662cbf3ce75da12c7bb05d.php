<div class="modal fade" id="externalRedirectModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo app('translator')->get('common.external_redirection'); ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <?php echo app('translator')->get('common.abouttoleave'); ?> <span class="text-primary" id="externalRedirectHost"></span>. <?php echo app('translator')->get('common.wanttocontinue'); ?>
        <div class="input-group form-group-no-border mt-2">
          <input id="redirectAlwaysTrustThisDomain" type="checkbox" value="1">
          <label for="redirectAlwaysTrustThisDomain" class="control-label mb-0 ml-2">
            <?php echo app('translator')->get('common.alwaystrustdomain'); ?>
          </label>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo app('translator')->get('common.close'); ?></button>
        <a href="#" target="_blank" class="btn btn-primary" id="externalRedirectUrl"><?php echo app('translator')->get('common.continue'); ?></a>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/promethee/resources/views/layouts/beta/external_redirect_modal.blade.php ENDPATH**/ ?>