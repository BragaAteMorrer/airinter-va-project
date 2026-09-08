<div class="content">
  <div class="row">
    <div class="col-sm-12">
      <div class="form-group">
        <?php echo e(Form::open(['route' => 'admin.airports.index', 'method' => 'GET', 'class'=>'form-inline pull-right'])); ?>


        <?php echo e(Form::label('icao', 'ICAO:')); ?>

        <?php echo e(Form::text('icao', null, ['class' => 'form-control'])); ?>

        &nbsp;
        <a href="<?php echo e(route('admin.airports.index')); ?>">clear</a>
        <?php echo e(Form::close()); ?>

      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/airports/search.blade.php ENDPATH**/ ?>