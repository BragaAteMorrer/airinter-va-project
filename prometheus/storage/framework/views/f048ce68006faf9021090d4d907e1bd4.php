<div class="content">
  <div class="row">
    <div class="col-sm-12">
      <div class="form-group">
        <?php echo e(Form::open(['route' => 'admin.users.index', 'method' => 'GET', 'class'=>'form-inline pull-right'])); ?>


        <?php echo e(Form::label('name', 'Name:')); ?>

        <?php echo e(Form::text('name', null, ['class' => 'form-control'])); ?>


        <?php echo e(Form::label('email', 'Email:')); ?>

        <?php echo e(Form::text('email', null, ['class' => 'form-control'])); ?>

        &nbsp;
        <?php echo e(Form::submit('find', ['class' => 'btn btn-primary'])); ?>

        &nbsp;
        <a href="<?php echo e(route('admin.users.index')); ?>">clear</a>
        <?php echo e(Form::close()); ?>

      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/search.blade.php ENDPATH**/ ?>