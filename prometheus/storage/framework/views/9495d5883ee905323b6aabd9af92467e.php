<div class="row">
  <div class="col-sm-12">
    <div class="form-container">
      <h6><i class="fas fa-clock"></i>
        &nbsp;Reset Caches
      </h6>
      <div class="row" style="padding-top: 5px">
        <div class="col-sm-3 text-center">
          <?php echo e(Form::open(['route' => 'admin.maintenance.cache'])); ?>

          <?php echo e(Form::hidden('type', 'all')); ?>

          <?php echo e(Form::button('Clear all caches', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

          <?php echo e(Form::close()); ?>

        </div>
        <div class="col-sm-3 text-center">
          <?php echo e(Form::open(['route' => 'admin.maintenance.cache'])); ?>

          <?php echo e(Form::hidden('type', 'application')); ?>

          <?php echo e(Form::button('Application', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

          <?php echo e(Form::close()); ?>

        </div>
        <div class="col-sm-3 text-center">
          <?php echo e(Form::open(['route' => 'admin.maintenance.cache'])); ?>

          <?php echo e(Form::hidden('type', 'views')); ?>

          <?php echo e(Form::button('Views', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

          <?php echo e(Form::close()); ?>

        </div>
        <div class="col-sm-3 text-center">
          <?php echo e(Form::open(['route' => 'admin.maintenance.queue'])); ?>

          <?php echo e(Form::button('Flush Failed Jobs', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

          <?php echo e(Form::close()); ?>

        </div>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/maintenance/caches.blade.php ENDPATH**/ ?>