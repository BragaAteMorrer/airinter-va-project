<div class="row">
  <div class="col-sm-12">
    <div class="form-container">
      <h6><i class="fas fa-clock"></i>&nbsp;Subfleet and Status</h6>
      <div class="form-container-body row">
        <div class="form-group col-sm-3">
          <?php echo e(Form::label('subfleet_id', 'Subfleet:')); ?>

          <?php echo e(Form::select('subfleet_id', $subfleets, $subfleet_id ?? null, [
              'class' => 'form-control select2',
              'placeholder' => 'Select Subfleet'
              ])); ?>

          <p class="text-danger"><?php echo e($errors->first('subfleet_id')); ?></p>
        </div>

        <div class="form-group col-sm-3">
          <?php echo e(Form::label('status', 'Status:')); ?>

          <?php echo e(Form::select('status', $statuses, null, ['class' => 'form-control select2', 'placeholder' => 'Select Status'])); ?>

          <p class="text-danger"><?php echo e($errors->first('subfleet_id')); ?></p>
        </div>

        <div class="form-group col-sm-3">
          <?php echo e(Form::label('hub_id', 'Home:')); ?>

          <?php echo e(Form::select('hub_id', $hubs, null, ['class' => 'form-control airport_search'])); ?>

          <p class="text-danger"><?php echo e($errors->first('hub_id')); ?></p>
        </div>

        <div class="form-group col-sm-3">
          <?php echo e(Form::label('airport_id', 'Location:')); ?>

          <?php echo e(Form::select('airport_id', $airports, null, ['class' => 'form-control airport_search'])); ?>

          <p class="text-danger"><?php echo e($errors->first('airport_id')); ?></p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="form-container">
      <h6>
        <i class="fas fa-plane"></i>&nbsp;Aircraft Information
        <span style="float:right">
          View list of
          <a href="https://en.wikipedia.org/wiki/List_of_ICAO_aircraft_type_designators" target="_blank">IATA and ICAO Type Designators</a>
        </span>
      </h6>
      <div class="form-container-body">
        <div class="row">
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('name', 'Name:')); ?>&nbsp;<span class="required">*</span>
            <?php echo e(Form::text('name', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('name')); ?></p>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('registration', 'Registration:')); ?>&nbsp;<span class="required">*</span>
            <?php echo e(Form::text('registration', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('registration')); ?></p>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('fin', 'FIN:')); ?>

            <?php echo e(Form::text('fin', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('fin')); ?></p>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('selcal', 'SELCAL:')); ?>

            <?php echo e(Form::text('selcal', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('selcal')); ?></p>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('iata', 'IATA:')); ?>

            <?php echo e(Form::text('iata', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('iata')); ?></p>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('icao', 'ICAO:')); ?>

            <?php echo e(Form::text('icao', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('icao')); ?></p>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('simbrief_type', 'SimBrief Type:')); ?>

            <?php echo e(Form::text('simbrief_type', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('simbrief_type')); ?></p>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('hex_code', 'Hex Code:')); ?>

            <?php echo e(Form::text('hex_code', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('hex_code')); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-12">
    <div class="form-container">
      <h6><i class="fas fa-plane"></i>&nbsp;Certified Weights (<?php echo e(setting('units.weight')); ?>)</h6>
      <div class="form-container-body">
        <div class="row">
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('dow', 'Dry Operating Weight (DOW/OEW):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="dow" value="<?php if(isset($aircraft)): ?><?php echo e($aircraft->dow->local(0)); ?><?php endif; ?>" step="1" />
                <p class="text-danger"><?php echo e($errors->first('dow')); ?></p>
              </div>
            </div>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('zfw', 'Max Zero Fuel Weight (MZFW):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="zfw" value="<?php if(isset($aircraft)): ?><?php echo e($aircraft->zfw->local(0)); ?><?php endif; ?>" step="1" />
                <p class="text-danger"><?php echo e($errors->first('zfw')); ?></p>
              </div>
            </div>            
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('mtow', 'Max Takeoff Weight (MTOW):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="mtow" value="<?php if(isset($aircraft)): ?><?php echo e($aircraft->mtow->local(0)); ?><?php endif; ?>" step="1" />
                <p class="text-danger"><?php echo e($errors->first('mtow')); ?></p>
              </div>
            </div> 
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('mlw', 'Max Landing Weight (MLW):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="mlw" value="<?php if(isset($aircraft)): ?><?php echo e($aircraft->mlw->local(0)); ?><?php endif; ?>" step="1" />
                <p class="text-danger"><?php echo e($errors->first('mlw')); ?></p>
              </div>
            </div> 
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Submit Field -->
  <div class="form-group col-sm-12">
    <div class="pull-right">
      <?php echo e(Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/aircraft/fields.blade.php ENDPATH**/ ?>