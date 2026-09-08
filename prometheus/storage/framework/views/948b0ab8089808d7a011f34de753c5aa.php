<?php if($display_card === true): ?>
<div class="card border">
   <div class="card-body widget-desk">
      <div class="text-end">
         <h4 class="mt-0 mb-0 fw-bold"><a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#JournalModal<?php echo e($journal_id); ?>"><?php echo e($cur_balance); ?></a></h4>
         <p class="mb-0">Current Balance</p>
      </div>
      <div class="widget-icon">
         <i class="ph-fill ph-money"></i>
      </div>
      <div class="clearfix"></div>
   </div>
</div>
<?php else: ?>
<a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#JournalModal"><?php echo e($cur_balance); ?></a>
<?php endif; ?>
<div class="modal fade" id="JournalModal<?php echo e($journal_id); ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="JournalModal<?php echo e($journal_id); ?>Label" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-xl">
      <div class="modal-content">
         <div class="modal-header pb-0">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-money fs-20 me-2"></i>Current Balance</h4>
         </div>
         <div class="modal-body pt-0">
            <div class="card-body p-0">
               <table class="table table-hover table-striped mb-0">
                  <tr>
                     <th class="text-start">Description / Memo</th>
                     <th>Credit</th>
                     <th>Debit</th>
                     <th class="text-end">Date</th>
                  </tr>
                  <?php if($transactions->count() > 0): ?>
                  <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                     <td class="text-start"><?php echo e($record->memo); ?></td>
                     <td>
                        <?php if(filled($record->credit)): ?>
                        <?php echo e(money($record->credit, $curr_unit)); ?>

                        <?php endif; ?>
                     </td>
                     <td>
                        <?php if(filled($record->debit)): ?>
                        <?php echo e(money($record->debit, $curr_unit)); ?>

                        <?php endif; ?>
                     </td>
                     <td class="text-end"><?php echo e($record->created_at->format('d.m.Y H:i')); ?></td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  <tr>
                     <td colspan="4" class="text-end small">Only last <?php echo e($limit); ?> entries are displayed</td>
                  </tr>
                  <?php else: ?>
                  <tr>
                     <td colspan="4">No Records Found</td>
                  </tr>
                  <?php endif; ?>
               </table>
               <table class="table table-hover table-striped text-center mb-0">
                  <tr>
                     <th>Total Credit</th>
                     <th>Total Debit</th>
                     <th>Current Balance</th>
                  </tr>
                  <tr>
                     <td class="text-success"><?php echo e($sum_credit); ?></td>
                     <td class="text-danger"><?php echo e($sum_debit); ?></td>
                     <td class="text-primary fw-bolder"><?php echo e($cur_balance); ?></td>
                  </tr>
               </table>
            </div>
            <button type="button" class="btn btn-primary mt-3 float-end" data-bs-dismiss="modal">Close</button>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/journal_details.blade.php ENDPATH**/ ?>