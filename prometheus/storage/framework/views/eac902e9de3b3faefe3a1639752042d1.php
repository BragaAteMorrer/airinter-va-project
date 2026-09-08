<?php $__env->startSection('title', 'Disposable Maintenance'); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom" style="margin-left:5px; margin-right:5px; margin-bottom:5px;">
    <div class="content">
      <p>Ongoing maintenance operations can be finished here, also fleet status is listed</p>
      <p>&nbsp;</p>
      <p><a href="https://github.com/FatihKoz" target="_blank">&copy; B.Fatih KOZ</a></p>
    </div>
  </div>
  <?php if($activemaint->count()): ?>
    <div class="row text-center" style="margin:5px;"><h4 style="margin: 5px; padding:0px;"><b>Ongoing Maintenance</b></h4></div>
    <div class="row" style="margin-left:5px; margin-right:5px;">
      <div class="card border-blue-bottom" style="padding:10px;">
        <table class="table table-sm table-striped text-left mt-0 mb-0">
          <tr>
            <th>Registration / Name</th>
            <th>Aircraft State</th>
            <th>Current Operation</th>
            <th>Started Time</th>
            <th>Scheduled End</th>
            <th class="text-right">Actions&nbsp;&nbsp;</th>
          </tr>
          <?php $__currentLoopData = $activemaint; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <form class="form" method="post" action="<?php echo e(route('DSpecial.maint_finish')); ?>">
              <?php echo csrf_field(); ?>
              <tr class="m-0 p-0">
                <td class="m-0 p-0 align-middle">
                  <?php echo e(optional($active->aircraft)->ident); ?>

                  <?php if($active->aircraft && $active->aircraft->registration != $active->aircraft->name): ?> <?php echo e("'".$active->aircraft->name."'"); ?> <?php endif; ?>
                </td>
                <td>
                  <?php echo e('%'.$active->curr_state); ?>

                </td>
                <td class="m-0 p-0 align-middle">
                  <?php echo e($active->act_note); ?>

                </td>
                <td class="m-0 p-0 align-middle">
                  <?php echo e($active->act_start); ?>

                </td>
                <td class="m-0 p-0 align-middle">
                  <?php echo e($active->act_end); ?>

                </td>
                <td class="text-right m-0 p-0 align-middle">
                  <input type="hidden" name="id" value="<?php echo e($active->id); ?>" />
                  <input type="hidden" name="act_note" value="<?php echo e($active->act_note); ?>" />
                  <button class="btn btn-sm btn-success m-0" type="submit">Finish Maintenance</button>
                </td>
              </tr>
            </form>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
      </div>
    </div>
  <?php endif; ?>
  <div class="row text-center" style="margin:5px;"><h4 style="margin: 5px; padding:0px;"><b>Fleet Maintenance Status</b></h4></div>
  <div class="row" style="margin-left:5px; margin-right:5px;">
    <div class="card border-blue-bottom" style="padding:10px;">
      <table class="table table-sm table-striped border-0 text-left mt-0 mb-0">
        <tr>
          <th>Registration / Name</th>
          <th>Curr. State</th>
          <th>A Check (Rem.)</th>
          <th>B Check (Rem.)</th>
          <th>C Check (Rem.)</th>
          <th>Last Check</th>
          <th class="text-right">Actions&nbsp;&nbsp;</th>
        </tr>
        <?php $__currentLoopData = $maintenance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $maint): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <tr class="m-0 p-0">
            <td class="m-0 p-0">
              <?php echo e(optional($maint->aircraft)->ident); ?>

              <?php if($maint->aircraft && $maint->aircraft->registration != $maint->aircraft->name): ?> <?php echo e("'".$maint->aircraft->name."'"); ?> <?php endif; ?>
            </td>
            <td class="m-0 p-0"><?php echo e('%'.$maint->curr_state); ?></td>
            
            <td>
              <?php echo e(($maint->rem_ca).' Cycles'); ?>

              <br>
              <?php if($maint->rem_ta < 0): ?><span class="text-danger fw-bold"><?php endif; ?>
              <?php echo e(DS_ConvertMinutes($maint->rem_ta, '%2dh')); ?>

              <?php if($maint->rem_ta < 0): ?></span><?php endif; ?>
            </td>
            
            <td>
              <?php echo e(($maint->rem_cb).' Cycles'); ?>

              <br>
              <?php if($maint->rem_tb < 0): ?><span class="text-danger fw-bold"><?php endif; ?>
              <?php echo e(DS_ConvertMinutes($maint->rem_tb, '%2dh')); ?>

              <?php if($maint->rem_tb < 0): ?></span><?php endif; ?>
            </td>
            
            <td>
              <?php echo e(($maint->rem_cc).' Cycles'); ?>

              <br>
              <?php if($maint->rem_tc < 0): ?><span class="text-danger fw-bold"><?php endif; ?>
                <?php echo e(DS_ConvertMinutes($maint->rem_tc, '%2dh')); ?>

              <?php if($maint->rem_tc < 0): ?></span><?php endif; ?>
            </td>
            <td class="m-0 p-0">
              <?php echo e($maint->last_note); ?>

              <br>
              <?php echo e($maint->last_time); ?>

            </td>
            <td class="m-0 p-0 text-right">
              <form class="form" method="post" action="<?php echo e(route('DSpecial.maint_finish')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo e($maint->id); ?>" />
                <input type="hidden" name="ops" value="manual" />
                <?php if($maint->rem_tc < 10 || $maint->rem_cc < 3): ?>
                  <input type="hidden" name="act_note" value="C Check" />
                  <button class="btn btn-sm btn-warning m-0" type="submit">Perform C Check</button>
                <?php elseif($maint->rem_tb < 10 || $maint->rem_cb < 3): ?>
                  <input type="hidden" name="act_note" value="B Check" />
                  <button class="btn btn-sm btn-warning m-0" type="submit">Perform B Check</button>
                <?php elseif($maint->rem_ta < 10 || $maint->rem_ca < 3): ?>
                  <input type="hidden" name="act_note" value="A Check" />
                  <button class="btn btn-sm btn-secondary m-0" type="submit">Perform A Check</button>
                <?php elseif($maint->curr_state < 77): ?>
                  <input type="hidden" name="act_note" value="Line Check" />
                  <button class="btn btn-sm btn-primary m-0" type="submit">Perform Line Check</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </table>
    </div>
  </div>
  <?php if($maintenance->hasPages()): ?>
    <div class="row" style="margin-left:5px; margin-right:5px;">
      <div class="col-sm-12 text-center">
        <?php echo e($maintenance->links('pagination.default')); ?>

      </div>
    </div>
  <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/DisposableSpecial/Providers/../Resources/views/admin/maintenance.blade.php ENDPATH**/ ?>