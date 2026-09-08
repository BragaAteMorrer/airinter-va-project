<?php $__env->startSection('title', 'Disposable ICAO Type Techical Details'); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom" style="margin-left:5px; margin-right:5px; margin-bottom:5px;">
    <div class="content">
      <p>Details defined here are based on <b>AIRCRAFT ICAO TYPE</b>. All values are optional, if defined properly per ICAO type, values may be used for pirep checks and maintenance events.</p>
      <br>
      <p><a href="https://github.com/FatihKoz" target="_blank">&copy; B.Fatih KOZ</a></p>
    </div>
  </div>

  <div class="row text-center" style="margin:5px;">
    <h4 style="margin: 5px; padding:0px;"><b>ICAO Type Maintenance Check Periods, Pitch, Roll, Flap And Gear Speed Definitions</b></h4>
  </div>

  <div class="row" style="margin-left:5px; margin-right:5px;">
    <div class="card border-blue-bottom" style="padding:10px;">
      <form class="form" method="post" action="<?php echo e(route('DBasic.tech_store')); ?>">
        <?php echo csrf_field(); ?>
        <?php if($tech && filled($tech->id)): ?>
          <input type="hidden" name="id" value="<?php echo e($tech->id); ?>">
          <input type="hidden" name="icao" value="<?php echo e($tech->icao ?? ''); ?>">
        <?php endif; ?>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-3">
            <label class="pl-1 mb-1" for="subfleet_id">Select a record for Editing</label>
            <select id="tech_selection" class="form-control select2" onchange="checkselection()">
              <option value="0">Please select a record...</option>
              <?php $__currentLoopData = $tech_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $list): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($list->id); ?>" <?php if($tech && $list->id == $tech->id): ?> selected <?php endif; ?>><?php echo e($list->icao); ?> <?php if($list->active == 1): ?> (Active) <?php endif; ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="col-sm-3 text-left align-middle"><br>
            <a id="edit_link" style="visibility: hidden" href="<?php echo e(route('DBasic.tech')); ?>" class="btn btn-primary pl-1 mb-1">Load selected record for Edit</a>
          </div>
          <div class="col-sm-3 text-left align-middle"><br>
            <a id="delete_link" style="visibility: hidden" href="<?php echo e(route('DBasic.tech')); ?>" class="btn btn-danger pl-1 mb-1">Delete !</a>
          </div>
        </div>
        <?php if(!$tech): ?>
          <div class="row" style="margin-bottom: 10px;">
            <div class="col-sm-3">
              <label class="pl-1 mb-1" for="icao">Or select an ICAO Type to create a new record</label>
              <select id="icao_selection" name="icao" class="form-control select2">
                <option value="0">Please select an ICAO Type code...</option>
                <?php $__currentLoopData = $icao_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $icao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($icao); ?>" <?php if($tech && $icao == $tech->icao): ?> selected <?php endif; ?>><?php echo e($icao); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
          </div>
        <?php endif; ?>
        
        <hr>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f0_name">Detent 0</label>
            <div class="input-group">
              <input name="f0_name" type="text" class="form-control" placeholder="UP" maxlength="10" value="<?php echo e($tech->f0_name ?? ''); ?>">
              <input name="f0_speed" type="number" class="form-control" placeholder="0 kts" min="0" max="999" value="<?php echo e($tech->f0_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f1_name">Detent 1</label>
            <div class="input-group">
              <input name="f1_name" type="text" class="form-control" placeholder="1" maxlength="10" value="<?php echo e($tech->f1_name ?? ''); ?>">
              <input name="f1_speed" type="number" class="form-control" placeholder="250 kts" min="0" max="999" value="<?php echo e($tech->f1_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f2_name">Detent 2</label>
            <div class="input-group">
              <input name="f2_name" type="text" class="form-control" placeholder="2" maxlength="10" value="<?php echo e($tech->f2_name ?? ''); ?>">
              <input name="f2_speed" type="number" class="form-control" placeholder="250 kts" min="0" max="999" value="<?php echo e($tech->f2_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f3_name">Detent 3</label>
            <div class="input-group">
              <input name="f3_name" type="text" class="form-control" placeholder="5" maxlength="10" value="<?php echo e($tech->f3_name ?? ''); ?>">
              <input name="f3_speed" type="number" class="form-control" placeholder="250 kts" min="0" max="999" value="<?php echo e($tech->f3_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f4_name">Detent 4</label>
            <div class="input-group">
              <input name="f4_name" type="text" class="form-control" placeholder="10" maxlength="10" value="<?php echo e($tech->f4_name ?? ''); ?>">
              <input name="f4_speed" type="number" class="form-control" placeholder="230 kts" min="0" max="999" value="<?php echo e($tech->f4_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f5_name">Detent 5</label>
            <div class="input-group">
              <input name="f5_name" type="text" class="form-control" placeholder="15" maxlength="10" value="<?php echo e($tech->f5_name ?? ''); ?>">
              <input name="f5_speed" type="number" class="form-control" placeholder="210 kts" min="0" max="999" value="<?php echo e($tech->f5_speed ?? ''); ?>">
            </div>
          </div>
        </div>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f6_name">Detent 6</label>
            <div class="input-group">
              <input name="f6_name" type="text" class="form-control" placeholder="25" maxlength="10" value="<?php echo e($tech->f6_name ?? ''); ?>">
              <input name="f6_speed" type="number" class="form-control" placeholder="190 kts" min="0" max="999" value="<?php echo e($tech->f6_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f7_name">Detent 7</label>
            <div class="input-group">
              <input name="f7_name" type="text" class="form-control" placeholder="30" maxlength="10" value="<?php echo e($tech->f7_name ?? ''); ?>">
              <input name="f7_speed" type="number" class="form-control" placeholder="170 kts" min="0" max="999" value="<?php echo e($tech->f7_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f8_name">Detent 8</label>
            <div class="input-group">
              <input name="f8_name" type="text" class="form-control" placeholder="40" maxlength="10" value="<?php echo e($tech->f8_name ?? ''); ?>">
              <input name="f8_speed" type="number" class="form-control" placeholder="170 kts" min="0" max="999" value="<?php echo e($tech->f8_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f9_name">Detent 9</label>
            <div class="input-group">
              <input name="f9_name" type="text" class="form-control" placeholder="50" maxlength="10" value="<?php echo e($tech->f9_name ?? ''); ?>">
              <input name="f9_speed" type="number" class="form-control" placeholder="150 kts" min="0" max="999" value="<?php echo e($tech->f9_speed ?? ''); ?>">
            </div>
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="f10_name">Detent 10</label>
            <div class="input-group">
              <input name="f10_name" type="text" class="form-control" placeholder="70" maxlength="10" value="<?php echo e($tech->f10_name ?? ''); ?>">
              <input name="f10_speed" type="number" class="form-control" placeholder="130 kts" min="0" max="999" value="<?php echo e($tech->f10_speed ?? ''); ?>">
            </div>
          </div>
        </div>
        
        <hr>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="mzfw">Maximum Zero Fuel Weight</label>
            <input name="mzfw" type="number" class="form-control" placeholder="<?php echo e($units['weight']); ?>" min="0" max="999999" value="<?php echo e($tech->mzfw ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="mrw">Maximum Ramp Weight</label>
            <input name="mrw" type="number" class="form-control" placeholder="<?php echo e($units['weight']); ?>" min="0" max="999999" value="<?php echo e($tech->mrw ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="mtow">Maximum Take Off Weight</label>
            <input name="mtow" type="number" class="form-control" placeholder="<?php echo e($units['weight']); ?>" min="0" max="999999" value="<?php echo e($tech->mtow ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="mlaw">Maximum Landing Weight</label>
            <input name="mlaw" type="number" class="form-control" placeholder="<?php echo e($units['weight']); ?>" min="0" max="999999" value="<?php echo e($tech->mlaw ?? ''); ?>">
          </div>
        </div>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="gear_extend">Gear Extension Speed</label>
            <input name="gear_extend" type="number" class="form-control" placeholder="250 Kts" min="0" max="999" value="<?php echo e($tech->gear_extend ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="gear_retract">Gear Retraction Speed</label>
            <input name="gear_retract" type="number" class="form-control" placeholder="220 Kts" min="0" max="999" value="<?php echo e($tech->gear_retract ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="gear_maxtire">Max. Tire Speed</label>
            <input name="gear_maxtire" type="number" class="form-control" placeholder="190 Kts" min="0" max="999" value="<?php echo e($tech->gear_maxtire ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="max_pitch">Max. Pitch Angle (TO/LND)</label>
            <input name="max_pitch" type="number" class="form-control" placeholder="13.5 &deg;" min="0" max="99" step="0.1" value="<?php echo e($tech->max_pitch ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="max_roll">Max. Roll Angle (TO/LND)</label>
            <input name="max_roll" type="number" class="form-control" placeholder="7.4 &deg;" min="0" max="99" step="0.1" value="<?php echo e($tech->max_roll ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="avg_fuel">Avg. Fuel Burn (lbs/hour)</label>
            <input name="avg_fuel" type="number" class="form-control" placeholder="2400 lbs/hour" min="0" max="9999" step="0.01" value="<?php echo e($tech->avg_fuel ?? ''); ?>">
          </div>
        </div>
        
        <hr>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="max_cycle_a">A Check (Cycle)</label>
            <input name="max_cycle_a" type="number" class="form-control" placeholder="250 flights" min="0" max="99999" value="<?php echo e($tech->max_cycle_a ?? ''); ?>">
            <br>
            <label class="pl-1 mb-1" for="max_time_a">A Check (Flight Time)</label>
            <input name="max_time_a" type="number" class="form-control" placeholder="500 flight hours" min="0" max="99999" value="<?php echo e($tech->max_time_a ?? ''); ?>">
            <br>
            <label class="pl-1 mb-1" for="duration_a">A Check Duration (Hours)</label>
            <input name="duration_a" type="number" class="form-control" placeholder="10 hours" min="0" max="9999" step="0.01" value="<?php echo e($tech->duration_a ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="max_cycle_b">B Check (Cycle)</label>
            <input name="max_cycle_b" type="number" class="form-control" placeholder="500 flights" min="0" max="99999" value="<?php echo e($tech->max_cycle_b ?? ''); ?>">
            <br>
            <label class="pl-1 mb-1" for="max_time_b">B Check (Flight Time)</label>
            <input name="max_time_b" type="number" class="form-control" placeholder="1000 flight hours" min="0" max="99999" value="<?php echo e($tech->max_time_b ?? ''); ?>">
            <br>
            <label class="pl-1 mb-1" for="duration_b">B Check Duration (Hours)</label>
            <input name="duration_b" type="number" class="form-control" placeholder="48 hours" min="0" max="9999" step="0.01" value="<?php echo e($tech->duration_b ?? ''); ?>">
          </div>
          <div class="col-sm-2">
            <label class="pl-1 mb-1" for="max_cycle_c">C Check (Cycle)</label>
            <input name="max_cycle_c" type="number" class="form-control" placeholder="2500 flights" min="0" max="99999" value="<?php echo e($tech->max_cycle_c ?? ''); ?>">
            <br>
            <label class="pl-1 mb-1" for="max_time_c">C Check (Flight Time)</label>
            <input name="max_time_c" type="number" class="form-control" placeholder="5000 flight hours" min="0" max="99999" value="<?php echo e($tech->max_time_c ?? ''); ?>">
            <br>
            <label class="pl-1 mb-1" for="duration_c">C Check Duration (Hours)</label>
            <input name="duration_c" type="number" class="form-control" placeholder="120 hours" min="0" max="9999" step="0.01" value="<?php echo e($tech->duration_c ?? ''); ?>">
          </div>
        </div>
        
        <hr>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-2 text-left">
            <input type="hidden" name="active" value="0">
            <label class="pl-1 mb-1" for="active">Active <input name="active" type="checkbox" <?php if($tech && $tech->active == 1): ?> checked="true" <?php endif; ?> class="form-control" value="1"></label>
          </div>
          <div class="col-sm-10 text-right">
            <button class="btn btn-primary pl-1 mb-1" type="submit"><?php if($tech && $tech->id): ?> Update <?php else: ?> Save <?php endif; ?></button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
  <style>
    ::placeholder { color: indianred !important; opacity: 0.6 !important; }
    :-ms-input-placeholder { color: indianred !important; }
    ::-ms-input-placeholder { color: indianred !important; }
  </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
  <?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
  <script type="text/javascript">
    // Simple selection with dropdown change
    // Also keeps buttons hidden until a valid selection
    const $oldlink = document.getElementById('edit_link').href;

    function checkselection() {
      if (document.getElementById('tech_selection').value === '0') {
        document.getElementById('edit_link').style.visibility = 'hidden';
        document.getElementById('delete_link').style.visibility = 'hidden';
      } else {
        document.getElementById('edit_link').style.visibility = 'visible';
        document.getElementById('delete_link').style.visibility = 'visible';
      }
      const selected_item = document.getElementById('tech_selection').value;
      const link_edit = '?tech_edit='.concat(selected_item);
      const link_delete = '?tech_delete='.concat(selected_item);

      document.getElementById('edit_link').href = $oldlink.concat(link_edit);
      document.getElementById('delete_link').href = $oldlink.concat(link_delete);
    }
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/DisposableBasic/Providers/../Resources/views/admin/tech.blade.php ENDPATH**/ ?>