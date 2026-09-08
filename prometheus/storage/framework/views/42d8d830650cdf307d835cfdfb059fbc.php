<div class="row">
  <div class="col-sm-4">
    <div class="form-container">
      <h6><i class="fas fa-clock"></i>
        &nbsp;Update
      </h6>
      <div class="row" style="padding-top: 5px">
        <div class="col-sm-12">
          <div class="row">
            <div class="col-sm-12">
              <p>Force new version check</p>
              <?php echo e(Form::open(['route' => 'admin.maintenance.forcecheck'])); ?>

              <?php echo e(Form::button('Force update check', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

              <?php echo e(Form::close()); ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="form-container">
      <h6><i class="fas fa-clock"></i>
        &nbsp;Re-seed
      </h6>
      <div class="row" style="padding-top: 5px">
        <div class="col-sm-12">
          <div class="row">
            <div class="col-sm-12">
              <p>This runs the seeder for all modules</p>
              <?php echo e(Form::open(['route' => 'admin.maintenance.reseed'])); ?>

              <?php echo e(Form::button('Rerun seeding', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

              <?php echo e(Form::close()); ?>

            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/maintenance/update.blade.php ENDPATH**/ ?>