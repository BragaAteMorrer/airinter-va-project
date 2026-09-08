<?php $__env->startSection('title', 'Fleet Maintenance'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/36.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-screencast align-middle fs-20 me-1"></i>Fleet Maintenance Status</h4>
            <?php if(!$maintenance->count() && !$activemaint->count()): ?>
               <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>Congratulations! All fleet members are in good shape.</div>
            <?php else: ?>
               <table class="table table-hover table-striped mb-0">
                  <tr>
                     <th class="text-start">Aircraft</th>
                     <th class="text-start">Location</th>
                     <th>Curr. State</th>
                     <th>Last Flight</th>
                     <th>Last Check Type</th>
                     <th>Last Check Time</th>
                     <th colspan="2">A Check (Rem.)</th>
                     <th colspan="2">B Check (Rem.)</th>
                     <th colspan="2">C Check (Rem.)</th>
                     <?php if($staff_check): ?>
                     <th>Actions</th>
                     <?php endif; ?>
                  </tr>
                  <?php $__currentLoopData = $maintenance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr class="align-middle">
                     <td class="text-start"><a href="<?php echo e(route('DBasic.aircraft', [optional($maint->aircraft)->registration ?? ''])); ?>" class="tooltiptop" title="<?php echo e(optional($maint->aircraft)->name); ?>"><?php echo e(optional($maint->aircraft)->ident); ?></a></td>
                     <td class="text-start"><a href="<?php echo e(route('frontend.airports.show', [optional($maint->aircraft)->airport_id])); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-house"></i><?php echo e(optional($maint->aircraft)->airport_id); ?></a></td>
                     <td>
                        <div class="progress m-3" style="height: 20px;">
                           <div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" style="width: <?php echo e($maint->curr_state.'%'); ?>" aria-valuenow="<?php echo e($maint->curr_state.'%'); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo e($maint->curr_state.'%'); ?></div>
                        </div>
                     </td>
                     <td>
                        <?php if($maint->aircraft): ?>
                           <?php echo e(optional($maint->aircraft->landing_time)->format('d. F Y - H:i')); ?>

                        <?php endif; ?>
                     </td>
                     <td><?php echo e($maint->last_note); ?></td>
                     <td>
                        <?php if(filled($maint->last_time)): ?>
                           <?php echo e($maint->last_time->format('d. F Y - H:i')); ?>

                        <?php endif; ?>
                     </td>
                     <td><?php echo e(($maint->rem_ca).' Cycles'); ?></td>
                     <td>
                        <?php if($maint->rem_ta < 0): ?>
                           <span class="text-danger fw-bold">
                        <?php endif; ?>
                        <?php echo e(DS_ConvertMinutes($maint->rem_ta, '%2dh')); ?>

                        <?php if($maint->rem_ta < 0): ?>
                           </span>
                        <?php endif; ?>
                     </td>
                     <td><?php echo e(($maint->rem_cb).' Cycles'); ?></td>
                     <td>
                        <?php if($maint->rem_tb < 0): ?>
                           <span class="text-danger fw-bold">
                        <?php endif; ?>
                        <?php echo e(DS_ConvertMinutes($maint->rem_tb, '%2dh')); ?>

                        <?php if($maint->rem_tb < 0): ?>
                           </span>
                        <?php endif; ?>
                     </td>
                     <td><?php echo e(($maint->rem_cc).' Cycles'); ?></td>
                     <td>
                        <?php if($maint->rem_tc < 0): ?>
                           <span class="text-danger fw-bold">
                        <?php endif; ?>
                        <?php echo e(DS_ConvertMinutes($maint->rem_tc, '%2dh')); ?>

                        <?php if($maint->rem_tc < 0): ?>
                           </span>
                        <?php endif; ?>
                     </td>
                     <?php if($staff_check): ?>
                     <td>
                        <?php if(optional($maint->aircraft)->state === 0): ?>
                        <form class="form" method="post" action="<?php echo e(route('DSpecial.maint_finish')); ?>">
                           <?php echo csrf_field(); ?>
                           <input type="hidden" name="id" value="<?php echo e($maint->id); ?>" />
                           <input type="hidden" name="ops" value="manual" />
                           <?php if($maint->rem_tc < 600 || $maint->rem_cc < 3): ?>
                              <input type="hidden" name="act_note" value="C Check" />
                              <button class="btn btn-danger" type="submit">Perform C Check</button>
                           <?php elseif($maint->rem_tb < 600 || $maint->rem_cb < 3): ?>
                              <input type="hidden" name="act_note" value="B Check" />
                              <button class="btn btn-warning" type="submit">Perform B Check</button>
                           <?php elseif($maint->rem_ta < 600 || $maint->rem_ca < 3): ?>
                              <input type="hidden" name="act_note" value="A Check" />
                              <button class="btn btn-warning" type="submit">Perform A Check</button>
                           <?php elseif($maint->curr_state < 75): ?>
                              <input type="hidden" name="act_note" value="Line Check" />
                              <button class="btn btn-primary" type="submit">Perform Line Check</button>
                           <?php else: ?>
                              <button class="btn btn-success" type="button" disabled>Aircraft Servicable</button>
                           <?php endif; ?>
                        </form>
                        <?php else: ?>
                        <button class="btn btn-primary" type="button" disabled>Aircraft in flight</button>
                        <?php endif; ?>
                     </td>
                     <?php endif; ?>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </table>
               <?php if(filled($activemaint)): ?>
               <h4 class="my-3 header-title border-bottom"><i class="ph-fill ph-screwdriver align-middle fs-20 me-1"></i>Active Maintenance Status</h4>
               <table class="table table-hover table-striped mb-0">
                  <tr>
                     <th class="text-start">Aircraft</th>
                     <th class="text-start">Location</th>
                     <th>Expected State</th>
                     <th>Current Operation</th>
                     <th>Started Time</th>
                     <th>Scheduled End</th>
                     <th class="text-end">Time Remaining</th>
                     <?php if($staff_check): ?>
                     <th class="text-end">Actions</th>
                     <?php endif; ?>
                  </tr>
                  <?php $__currentLoopData = $activemaint; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <form class="form" method="post" action="<?php echo e(route('DSpecial.maint_finish')); ?>">
                     <?php echo csrf_field(); ?>
                     <tr class="align-middle">
                        <td class="text-start"><a href="<?php echo e(route('DBasic.aircraft', [optional($active->aircraft)->registration ?? ''])); ?>" class="tooltiptop" title="<?php echo e(optional($active->aircraft)->name); ?>"><?php echo e(optional($active->aircraft)->ident); ?></a></td>
                        <td class="text-start"><a href="<?php echo e(route('frontend.airports.show', [optional($active->aircraft)->airport_id])); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-house"></i><?php echo e(optional($active->aircraft)->airport_id); ?></a></td>
                        <td>
                           <div class="progress m-3" style="height: 20px;">
                              <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: <?php echo e($active->curr_state.'%'); ?>" aria-valuenow="<?php echo e($active->curr_state.'%'); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo e($active->curr_state.'%'); ?></div>
                           </div>
                        </td>
                        <td><?php echo e($active->act_note); ?></td>
                        <td><?php echo e($active->act_start->format('d. F Y - H:i')); ?></td>
                        <td><?php echo e($active->act_end->format('d. F Y - H:i')); ?></td>
                        <td class="text-end"><?php echo e($active->act_end->diffForHumans()); ?></td>
                        <?php if($staff_check): ?>
                        <td class="text-end">
                           <input type="hidden" name="id" value="<?php echo e($active->id); ?>" />
                           <input type="hidden" name="act_note" value="<?php echo e($active->act_note); ?>" />
                           <button class="btn btn-success" type="submit">Finish Maintenance</button>
                        </td>
                        <?php endif; ?>
                     </tr>
                  </form>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </table>
               <?php endif; ?>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <?php echo e($maintenance->links('pagination.default')); ?>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/maintenance/index.blade.php ENDPATH**/ ?>