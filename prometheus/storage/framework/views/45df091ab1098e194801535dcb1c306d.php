<div class="row">
  <div class="form-group col-sm-1">
    <?php echo e(Form::label('id', 'ID:')); ?>

    <?php echo e(Form::number('id', null, ['class' => 'form-control', 'readonly' => 'readonly'])); ?>

  </div>
  <div class="form-group col-sm-3">
    <?php echo e(Form::label('name', 'Name:')); ?>

    <?php echo e(Form::text('name', null, ['class' => 'form-control', 'autocomplete' => 'off'])); ?>

    <p class="text-danger"><?php echo e($errors->first('name')); ?></p>
  </div>
  <div class="form-group col-sm-2">
    <?php echo e(Form::label('email', 'Email:')); ?>

    <?php echo e(Form::text('email', null, ['class' => 'form-control', 'autocomplete' => 'off'])); ?>

    <p class="text-danger"><?php echo e($errors->first('email')); ?></p>
  </div>
  <div class="form-group col-sm-2">
    <?php echo e(Form::label('password', 'Password:')); ?>

    <?php echo e(Form::password('password', ['class' => 'form-control', 'autocomplete' => 'off'])); ?>

    <p class="text-danger"><?php echo e($errors->first('password')); ?></p>
  </div>
  <div class="form-group col-sm-2">
    <?php echo e(Form::label('country', 'Country:')); ?> <br/>
    <?php echo e(Form::select('country', $countries, null, ['class' => 'form-control select2' ])); ?>

    <p class="text-danger"><?php echo e($errors->first('country')); ?></p>
  </div>
  <div class="form-group col-sm-2">
    <?php echo e(Form::label('timezone', 'Timezone:')); ?> <br/>
    <?php echo e(Form::select('timezone', $timezones, null, ['id' => 'timezone', 'class' => 'form-control select2' ])); ?>

    <p class="text-danger"><?php echo e($errors->first('timezone')); ?></p>
  </div>
</div>

<div class="row">
  <div class="form-group col-sm-1">
    <?php echo e(Form::label('pilot_id', 'Ident:')); ?>

    <?php echo e(Form::number('pilot_id', null, ['class' => 'form-control'])); ?>

    <p class="text-danger"><?php echo e($errors->first('pilot_id')); ?></p>
  </div>
  <div class="form-group col-sm-1">
    <?php echo e(Form::label('callsign', 'Callsign:')); ?>

    <?php echo e(Form::text('callsign', null, ['class' => 'form-control', 'autocomplete' => 'off', 'maxlength' => 4])); ?>

    <p class="text-danger"><?php echo e($errors->first('callsign')); ?></p>
  </div>
  <div class="form-group col-sm-1">
    <?php echo e(Form::label('transfer_time', 'Transfer Hours:')); ?>

    <?php echo e(Form::text('transfer_time', \App\Support\Units\Time::minutesToHours($user?->transfer_time), ['class' => 'form-control'])); ?>

    <p class="text-danger"><?php echo e($errors->first('transfer_time')); ?></p>
  </div>
  <div class="form-group col-sm-3">
    <?php echo e(Form::label('airline_id', 'Airline:')); ?>

    <?php echo e(Form::select('airline_id', $airlines, null, ['class' => 'form-control select2', 'placeholder' => 'Select Airline'])); ?>

  </div>
  <div class="form-group col-sm-3">
    <?php echo e(Form::label('rank_id', 'Rank:')); ?>

    <?php echo e(Form::select('rank_id', $ranks, null, ['class' => 'form-control select2', 'placeholder' => 'Select Rank'])); ?>

  </div>
  <div class="form-group col-md-3">
    <?php echo e(Form::label('state', 'State:')); ?>

    <?php echo e(Form::select('state', UserState::labels(), null, ['class' => 'form-control select2', 'style' => 'width: 100%;'])); ?>

  </div>
</div>

<div class="row">
  <div class="form-group col-sm-3">
    <?php echo e(Form::label('home_airport_id', 'Home Airport:')); ?>

    <?php echo e(Form::select('home_airport_id', $airports, null , ['class' => 'form-control airport_search'])); ?>

    <p class="text-danger"><?php echo e($errors->first('home_airport_id')); ?></p>
  </div>
  <div class="form-group col-sm-3">
    <?php echo e(Form::label('curr_airport_id', 'Current Airport:')); ?>

    <?php echo e(Form::select('curr_airport_id', $airports, null , ['class' => 'form-control airport_search'])); ?>

    <p class="text-danger"><?php echo e($errors->first('curr_airport_id')); ?></p>
  </div>
  <div class="form-group col-sm-6">
    <?php if (app('laratrust')->ability('admin', 'admin-user')) : ?>
      <?php echo e(Form::label('roles', 'Roles:')); ?>

      <?php echo e(Form::select('roles[]', $roles, $user?->roles->pluck('id') ?? collect(), ['class' => 'form-control select2', 'placeholder' => 'Select Roles', 'multiple'])); ?>

    <?php endif; // app('laratrust')->ability ?>
  </div>
</div>

<div class="row">
  <div class="form-group col-md-12">
    <?php echo e(Form::label('notes', 'Management Notes:')); ?>

    <?php echo e(Form::textarea('notes', null, ['class' => 'form-control', 'rows' => 4, 'autocomplete' => 'off'])); ?>

  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/fields.blade.php ENDPATH**/ ?>