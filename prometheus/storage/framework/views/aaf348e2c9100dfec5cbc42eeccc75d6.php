<div class="content">
  <?php echo e(Form::open(['route' => 'admin.flights.index', 'method' => 'GET', 'class'=>'form-group'])); ?>

    <div class="row">
      <div class="form-group col-sm-2">
        <?php echo e(Form::label('airline_id', 'Airline:')); ?>

        <?php echo e(Form::select('airline_id', $airlines, null , ['class' => 'form-control select2'])); ?>

      </div>
      <div class="form-group input-group-sm col-sm-2">
        <?php echo e(Form::label('flight_number', 'Flight Number:')); ?>

        <?php echo e(Form::text('flight_number', null, ['class' => 'form-control'])); ?>

      </div>
      <div class="form-group col-sm-3">
        <?php echo e(Form::label('dpt_airport_id', 'Departure:')); ?>

        <?php echo e(Form::select('dpt_airport_id', $airports, null , ['class' => 'form-control airport_search'])); ?>

      </div>
      <div class="form-group col-sm-3">
        <?php echo e(Form::label('arr_airport_id', 'Arrival:')); ?>

        <?php echo e(Form::select('arr_airport_id', $airports, null , ['class' => 'form-control airport_search'])); ?>

      </div>
      <div class="form-group col-sm-2 text-center">
        <br>
        <?php echo e(Form::submit('Find', ['class' => 'btn btn-primary'])); ?>

        <a href="<?php echo e(route('admin.flights.index')); ?>" class="btn btn-secondary ml-2">Clear</a>
      </div>
    </div>
  <?php echo e(Form::close()); ?>

</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/flights/search.blade.php ENDPATH**/ ?>