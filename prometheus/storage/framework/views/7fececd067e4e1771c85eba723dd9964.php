<div class="modal fade" id="onsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="onsLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header pb-0">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-air-traffic-control fs-20 me-2"></i><?php echo app('translator')->get('sptheme.ons-t'); ?></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="ph-fill ph-minus text-danger fs-20"></i></button>
          </div>
         <div class="modal-body pt-0">
            <div class="card-body p-0">              
               <div class="tab-default">
                  <div role="tabpanel">
                     <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                           <a class="nav-link active" data-bs-toggle="tab" href="#vatsim" role="tab" aria-selected="true" tabindex="-1">
                              <img src="<?php echo e(asset('SPTheme/images/vatsim-small.png')); ?>" alt="VATSIM">
                           </a>
                        </li>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link" data-bs-toggle="tab" href="#ivao" role="tab" aria-selected="false" tabindex="-1">
                              <img src="<?php echo e(asset('SPTheme/images/ivao-small.png')); ?>" alt="IVAO">
                           </a>
                        </li>
                     </ul>
                     <div class="tab-content text-muted">
                        <div class="tab-pane active show" id="vatsim" role="tabpanel">
                           <table class="table table-sm table-nowrap mb-0">
                              <tbody>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo e(trans_choice('common.pilot', 2)); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($vatsim_stats['pilots'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.controllers'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($vatsim_stats['controllers'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.atis'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($vatsim_stats['atis'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.observers'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($vatsim_stats['observers'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.supervisors'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($vatsim_stats['supervisors'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.totaluser'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($vatsim_stats['total'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.user24'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($vatsim_stats['twentyfour'] ?? '-'); ?></span></td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                        <div class="tab-pane" id="ivao" role="tabpanel">
                           <table class="table table-sm table-nowrap mb-0">
                              <tbody>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo e(trans_choice('common.pilot', 2)); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($ivao_stats['connections']['pilot'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.controllers'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($ivao_stats['connections']['atc'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.atis'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($ivao_stats['connections']['worldTour'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.observers'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($ivao_stats['connections']['observer'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.supervisors'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($ivao_stats['connections']['supervisor'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.totaluser'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($ivao_stats['connections']['total'] ?? '-'); ?></span></td>
                                 </tr>
                                 <tr>
                                    <td><i class="ph-fill ph-cell-signal-low align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.user24'); ?></td>
                                    <td class="text-end"><span class="badge bg-secondary fs-6"><?php echo e($ivao_stats['connections']['uniqueUsers24h'] ?? '-'); ?></span></td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo app('translator')->get('common.close'); ?></button>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/modules/SPTheme/Providers/../Resources/views/widgets/ons.blade.php ENDPATH**/ ?>