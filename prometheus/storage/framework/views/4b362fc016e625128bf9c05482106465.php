<div class="modal fade" id="bidModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addBidLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">         
         <div class="modal-header pb-0">
            <h4 class="modal-title mt-0 header-title border-bottom" id="bidModalLabel"><i class="ph-fill ph-address-book align-middle fs-20 me-1"></i><?php echo e(__('flights.aircraftbooking')); ?></h4>
            <button type="button" class="btn-close" id="btn-close" data-bs-dismiss="modal" aria-label="<?php echo app('translator')->get('common.close'); ?>"></button>
         </div>
         <div class="modal-body">
            <div class="form-group form-bg-grey rounded mb-0">
               <div class="row">
                  <label class="col-5 control-label"><i class="ph-fill ph-airplane align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.aircraft'); ?></label>
                  <div class="col-7">
                     <div class="input-group input-group-lg">
                        <select name="aircraft_select" id="aircraft_select" class="form-select bid_aircraft" placeholder="<?php echo app('translator')->get('sptheme.typetosearch'); ?>"></select>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" id="without_aircraft" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(__('flights.dontbookaircraft')); ?></button>
            <button type="button" id="with_aircraft" class="btn btn-primary" data-bs-dismiss="modal"><?php echo e(__('flights.bookaircraft')); ?></button>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flights/bids_aircraft.blade.php ENDPATH**/ ?>