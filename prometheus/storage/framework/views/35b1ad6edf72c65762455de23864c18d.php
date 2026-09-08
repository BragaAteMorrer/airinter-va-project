<div class="progress m-3" style="height: 20px;">
  <div class="progress-bar progress-bar-striped progress-bar-animated <?php if($maint->curr_state < 50): ?> bg-danger <?php elseif($maint->curr_state >= 50 && $maint->curr_state < 75): ?> bg-warning <?php else: ?> bg-success <?php endif; ?>" role="progressbar" style="width: <?php echo e(floor($maint->curr_state)); ?>%" aria-valuenow="<?php echo e(floor($maint->curr_state)); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo app('translator')->get('DSpecial::common.current_st'); ?> <?php echo e(floor($maint->curr_state)); ?>%</div>
</div>
<div class="row m-0">
  <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 p-0">
    <table class="table table-striped table-hover mb-0">
      <thead>
        <tr>
          <th class="text-center" colspan="2">A Check</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Rem. Time</td>
          <td class="text-end"><?php echo \App\Support\Units\Time::minutesToTimeString($maint->rem_ta); ?></td>
        </tr>
        <tr>
          <td>Rem. Cycle</td>
          <td class="text-end"><?php echo e($maint->rem_ca); ?></td>
        </tr>
        <tr>
          <td>Last Check</td>
          <td class="text-end"><?php echo e($maint->last_a ?? '-'); ?></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 p-0">
    <table class="table table-striped table-hover mb-0">
      <thead>
        <tr>
          <th class="text-center" colspan="2">B Check</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="border-start">Rem. Time</td>
          <td class="text-end border-end"><?php echo \App\Support\Units\Time::minutesToTimeString($maint->rem_tb); ?></td>
        </tr>
        <tr>
          <td class="border-start">Rem. Cycle</td>
          <td class="text-end border-end"><?php echo e(floor($maint->rem_cb)); ?></td>
        </tr>
        <tr>
          <td class="border-start">Last Check</td>
          <td class="text-end border-end"><?php echo e($maint->last_b ?? '-'); ?></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 p-0">
    <table class="table table-striped table-hover mb-0">
      <thead>
        <tr>
          <th class="text-center" colspan="2">C Check</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Rem. Time</td>
          <td class="text-end"><?php echo \App\Support\Units\Time::minutesToTimeString($maint->rem_tc); ?></td>
        </tr>
        <tr>
          <td>Rem. Cycle</td>
          <td class="text-end"><?php echo e($maint->rem_cc); ?></td>
        </tr>
        <tr>
          <td>Last Check</td>
          <td class="text-end"><?php echo e($maint->last_c ?? '-'); ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<?php if($maint->last_note): ?>
<div class="card-footer">
  <div class="row">
    <div class="col text-center">
      <span>
      <?php echo app('translator')->get('DSpecial::common.last_action'); ?>: <b><?php echo e($maint->last_note); ?></b> |
      <?php echo app('translator')->get('DSpecial::common.completed'); ?>: <?php echo e($maint->last_time->format('d. F Y - H:i').' UTC'); ?>

      </span>
    </div>
  </div>
</div>
<?php endif; ?>
<?php if($maint->act_start): ?>
<div class="row">
  <div class="col">
    <table class="table table-striped table-hover mb-0">
      <thead>
        <tr>
          <th class="text-center text-danger" colspan="2"><i class="ph-duotone ph-warning-circle fs-4"></i> <?php echo app('translator')->get('DSpecial::common.under_maint'); ?> <i class="ph-duotone ph-warning-circle fs-4"></i></th>
        </tr>
      </thead>
      </tbody>
      <tr>
        <td><?php echo app('translator')->get('DSpecial::common.current_op'); ?></td>
        <td class="text-end"><?php echo e($maint->act_note); ?></td>
      </tr>
      <tr>
        <td><?php echo app('translator')->get('DSpecial::common.rem_time'); ?></td>
        <td class="text-end"><?php echo e($maint->act_end->diffForHumans()); ?></td>
      </tr>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/maintenance/table.blade.php ENDPATH**/ ?>