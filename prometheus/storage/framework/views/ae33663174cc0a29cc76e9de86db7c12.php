<div class="row">
  <div class="col-sm-12">
    <div class="form-container">
      <h6><i class="fas fa-clock"></i>
        &nbsp;Cron
      </h6>
      <div class="row" style="padding-top: 5px">
        <div class="col-sm-12">
          <p>A cron must be created that runs every minute calling artisan. An example is below.
            <strong><a href="<?php echo e(docs_link('cron')); ?>" target="_blank">See the docs</a></strong></p>
          <label style="width: 100%">
            <input type="text" value="<?php echo e($cron_path); ?>" class="form-control" style="width: 100%"/>
          </label>

          <?php if($cron_problem_exists): ?>
            <div class="alert alert-danger" role="alert">
              There was a problem running the cron; make sure it's setup and check logs at
              <span class="text-monospace bg-gradient-dark">storage/logs/cron.log</span>.
              <a href="<?php echo e(docs_link('cron')); ?>" target="_blank">See the docs</a>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <hr>

      <div class="row" style="padding-top: 5px">
        <div class="col-sm-12">
          <h5>Web Cron</h5>
        </div>
        <div class="col-sm-6">
          <p>
            If you don't have cron access on your server, you can use a web-cron service to
            access this URL every minute. Keep it disabled if you're not using it. It's a
            unique ID that can be reset/changed if needed for security.
          </p>
        </div>
        <div class="col-sm-6 pull-right">
          <table class="table-condensed">
            <tr class="text-right">
              <td style="padding-right: 10px;" class="text-right">
                <?php echo e(Form::open(['url' => route('admin.maintenance.cron_enable'),
                            'method' => 'post'])); ?>

                <?php echo e(Form::button('Enable/Change ID', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

                <?php echo e(Form::close()); ?>

              </td>
              <td class="text-right">
                <?php echo e(Form::open(['url' => route('admin.maintenance.cron_disable'),
                        'method' => 'post'])); ?>

                <?php echo e(Form::button('Disable', ['type' => 'submit', 'class' => 'btn btn-warning'])); ?>

                <?php echo e(Form::close()); ?>

              </td>
            </tr>
          </table>
        </div>
        <div class="col-sm-12">

          <label style="width: 100%">
            <input type="text" value="<?php echo e($cron_url); ?>" class="form-control" style="width: 100%"/>
          </label>
        </div>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/maintenance/cron.blade.php ENDPATH**/ ?>