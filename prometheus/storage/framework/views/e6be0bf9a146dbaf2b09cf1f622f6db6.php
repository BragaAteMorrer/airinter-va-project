<?php if(!empty($pirep) && $pirep->read_only): ?>
  <div class="row">
    <div class="col-sm-12">
      <?php $__env->startComponent('admin.components.info'); ?>
        Once a PIREP has been accepted/rejected, certain fields go into read-only mode.
      <?php echo $__env->renderComponent(); ?>
    </div>
  </div>
<?php endif; ?>
<div class="row">
  <div class="col-xl-12">
    <div class="form-container">
      <h6><i class="fas fa-info-circle"></i>&nbsp;Flight Information</h6>
      <div class="form-container-body">
        <div class="row">
          <div class="form-group col-sm-6">
            <?php echo e(Form::label('flight_number', 'Flight Number/Route Code/Leg')); ?>

            <?php if($pirep->read_only): ?>
              <p><?php echo e($pirep->ident); ?>

                <?php echo e(Form::hidden('flight_number')); ?>

                <?php echo e(Form::hidden('flight_code')); ?>

                <?php echo e(Form::hidden('flight_leg')); ?>

              </p>
            <?php else: ?>
              <div class="row">
                <div class="col-sm-4">
                  <?php echo e(Form::text('flight_number', null, ['placeholder' => 'Flight Number', 'class' => 'form-control'])); ?>

                  <p class="text-danger"><?php echo e($errors->first('flight_number')); ?></p>
                </div>
                <div class="col-sm-4">
                  <?php echo e(Form::text('route_code', null, ['placeholder' => 'Code (optional)', 'class' => 'form-control'])); ?>

                  <p class="text-danger"><?php echo e($errors->first('route_code')); ?></p>
                </div>
                <div class="col-sm-4">
                  <?php echo e(Form::text('route_leg', null, ['placeholder' => 'Leg (optional)', 'class' => 'form-control'])); ?>

                  <p class="text-danger"><?php echo e($errors->first('route_leg')); ?></p>
                </div>
              </div>
            <?php endif; ?>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('flight_type', 'Flight Type')); ?>

            <?php if($pirep->read_only): ?>
              <p><?php echo e($pirep->flight_type.' | '.\App\Models\Enums\FlightType::label($pirep->flight_type)); ?></p>
              <?php echo e(Form::hidden('flight_type')); ?>

            <?php else: ?>
              <?php echo e(Form::select('flight_type', \App\Models\Enums\FlightType::select(), null, ['class' => 'form-control select2'])); ?>

            <?php endif; ?>
            <p class="text-danger"><?php echo e($errors->first('flight_type')); ?></p>
          </div>
          <div class="form-group col-sm-3">
            <p class="description">Filed Via:</p>
            <?php echo e(PirepSource::label($pirep->source)); ?>

            <?php if(filled($pirep->source_name)): ?>
              (<?php echo e($pirep->source_name); ?>)
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-xl-12">
    <div class="form-container">
      <h6><i class="fas fa-info-circle"></i>&nbsp;Pirep Details</h6>
      <div class="form-container-body">
        <div class="row">
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('airline_id', 'Airline')); ?>

            <?php if($pirep->read_only): ?>
              <p><?php echo e($pirep->airline->name); ?></p>
              <?php echo e(Form::hidden('airline_id')); ?>

            <?php else: ?>
              <?php echo e(Form::select('airline_id', $airlines_list, null, ['class' => 'form-control select2'])); ?>

              <p class="text-danger"><?php echo e($errors->first('airline_id')); ?></p>
            <?php endif; ?>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('aircraft_id', 'Aircraft:')); ?>

            <?php if($pirep->read_only): ?>
              <p><?php echo e(optional($pirep->aircraft)->ident); ?></p>
              <?php echo e(Form::hidden('aircraft_id')); ?>

            <?php else: ?>
              <?php echo e(Form::select('aircraft_id', $aircraft_list, null, ['id' => 'aircraft_select', 'class' => 'form-control select2'])); ?>

              <p class="text-danger"><?php echo e($errors->first('aircraft_id')); ?></p>
            <?php endif; ?>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('dpt_airport_id', 'Departure Airport:')); ?>

            <?php if($pirep->read_only): ?>
              <p><?php echo e($pirep->dpt_airport_id); ?><?php if(filled($pirep->dpt_airport)): ?> - <?php echo e($pirep->dpt_airport->name); ?><?php endif; ?></p>
              <?php echo e(Form::hidden('dpt_airport_id')); ?>

            <?php else: ?>
              <?php echo e(Form::select('dpt_airport_id', $airports_list, null, ['class' => 'form-control airport_search'])); ?>

              <p class="text-danger"><?php echo e($errors->first('dpt_airport_id')); ?></p>
            <?php endif; ?>
          </div>
          <div class="form-group col-sm-3">
            <?php echo e(Form::label('arr_airport_id', 'Arrival Airport:')); ?>

            <?php if($pirep->read_only): ?>
              <p><?php echo e($pirep->arr_airport->id); ?><?php if(filled($pirep->arr_airport)): ?> - <?php echo e($pirep->arr_airport->name); ?><?php endif; ?></p>
              <?php echo e(Form::hidden('arr_airport_id')); ?>

            <?php else: ?>
              <?php echo e(Form::select('arr_airport_id', $airports_list, null, ['class' => 'form-control airport_search'])); ?>

              <p class="text-danger"><?php echo e($errors->first('arr_airport_id')); ?></p>
            <?php endif; ?>
          </div>
        </div>
        <div class="row">
          <!-- Planned Flight Time Field -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('planned_flight_time', 'Pln.Time:')); ?>

            <div class="row">
              <div class="col-sm-12">
                <?php $ft = App\Support\Units\Time::minutesToTimeString($pirep->planned_flight_time); ?>
                <?php echo e(Form::text('planned_flight_time', $ft, ['class' => 'form-control', 'disabled' => 'disabled'])); ?>

              </div>
            </div>
          </div>
          <!-- Flight Time Field -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('flight_time', 'Flt.Time (h:m):')); ?>

            <div class="row">
              <div class="col-sm-6">
                <?php echo e(Form::number('hours', null, ['class' => 'form-control', 'placeholder' => 'hours'])); ?>

                <p class="text-danger"><?php echo e($errors->first('hours')); ?></p>
              </div>
              <div class="col-sm-6">
                <?php echo e(Form::number('minutes', null, ['class' => 'form-control', 'placeholder' => 'minutes'])); ?>

                <p class="text-danger"><?php echo e($errors->first('minutes')); ?></p>
              </div>
            </div>
          </div>
          <!-- Block Fuel Field -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('block_fuel', 'Block Fuel (lbs):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="block_fuel" value="<?php echo e($pirep->block_fuel->internal() ?? null); ?>" step="0.01" />
                <p class="text-danger"><?php echo e($errors->first('block_fuel')); ?></p>
              </div>
            </div>
          </div>
          <!-- Fuel Used Field -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('fuel_used', 'Used Fuel (lbs):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="fuel_used" value="<?php echo e($pirep->fuel_used->internal() ?? null); ?>" step="0.01" />
                <p class="text-danger"><?php echo e($errors->first('fuel_used')); ?></p>
              </div>
            </div>
          </div>
          <!-- Planned/Flight Level Field -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('planned_level', 'Pln.Level (ft):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <?php echo e(Form::text('planned_level', optional($pirep->flight)->level, ['class' => 'form-control', 'disabled' => 'disabled'])); ?>

              </div>
            </div>
          </div>
          <!-- Level Field -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('level', 'Flt.Level (ft):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <?php echo e(Form::number('level', null, ['class' => 'form-control', 'min' => 0])); ?>

                <p class="text-danger"><?php echo e($errors->first('level')); ?></p>
              </div>
            </div>
          </div>
          <!-- Planned Distance -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('planned_distance', 'Pln.Dist. (nmi):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="planned_distance" value="<?php echo e($pirep->planned_distance->internal() ?? null); ?>" min="0" step="0.01" readonly/>
                <p class="text-danger"><?php echo e($errors->first('planned_distance')); ?></p>
              </div>
            </div>
          </div>
          <!-- Distance -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('distance', 'Flt.Dist. (nmi):')); ?>

            <div class="row">
              <div class="col-sm-12">
                <input class="form-control" type="number" name="distance" value="<?php echo e($pirep->distance->internal() ?? null); ?>" min="0" step="0.01" />
                <p class="text-danger"><?php echo e($errors->first('distance')); ?></p>
              </div>
            </div>
          </div>
          <!-- Landing Rate -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('landing_rate', 'Landing Rate:')); ?>

            <div class="row">
              <div class="col-sm-12">
                <?php echo e(Form::text('landing_rate', $pirep->landing_rate, ['class' => 'form-control', 'disabled' => 'disabled'])); ?>

              </div>
            </div>
          </div>
          <!-- Score -->
          <div class="form-group col-sm-1">
            <?php echo e(Form::label('score', 'Score:')); ?>

            <div class="row">
              <div class="col-sm-12">
                <?php echo e(Form::number('score', null, ['class' => 'form-control', 'min' => 0, 'max' => 100])); ?>

                <p class="text-danger"><?php echo e($errors->first('score')); ?></p>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <!-- Route Field -->
          <div class="form-group col-sm-8">
            <?php echo e(Form::label('route', 'Route:')); ?>

            <?php echo e(Form::textarea('route', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('route')); ?></p>
          </div>
          <!-- Notes Field -->
          <div class="form-group col-sm-4">
            <?php echo e(Form::label('notes', 'Notes:')); ?>

            <?php echo e(Form::textarea('notes', null, ['class' => 'form-control'])); ?>

            <p class="text-danger"><?php echo e($errors->first('notes')); ?></p>
          </div>
        </div>
        <?php if(filled(optional($pirep->flight)->route)): ?>
          <div class="row">
            <!-- Flight Route -->
            <div class="form-group col-sm-8">
              <?php echo e(Form::label('planned_route', 'Provided Route:')); ?>

              <?php echo e(Form::textarea('planned_route', optional($pirep->flight)->route, ['class' => 'form-control', 'rows' => 4, 'disabled' => 'disabled'])); ?>

            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="form-group col-sm-12">
    <div class="pull-right">
      <?php echo e(Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-info'])); ?>

    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="form-container">
      <h6><i class="fas fa-info-circle"></i>&nbsp;Fares</h6>
      <div class="form-container-body">
        <div id="fares_container">
          <?php echo $__env->make('admin.pireps.fares', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-sm-12">
    <div class="form-container">
      <h6><i class="fas fa-info-circle"></i>&nbsp;Fields</h6>
      <div class="form-container-body">
        
        <?php echo $__env->make('admin.pireps.field_values', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/fields.blade.php ENDPATH**/ ?>