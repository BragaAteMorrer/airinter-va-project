<?php $__env->startSection('title', 'Disposable Airports'); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom" style="margin-bottom: 10px;">
    <div class="content">
      <p>This module is designed to automate Airport Imports and Updates via Open Sources</p>
      <p>
        Documentation about this module can be found in the <b>README.md</b> file or at GitHub via this link
        <a href="https://github.com/FatihKoz/DisposableAirports#readme" target="_blank" title="Online Readme">Online Readme</a>
      </p>
      <hr>
      <p><?php if(filled($details->version)): ?> Version: <?php echo e($details->version); ?> <?php endif; ?> <a href="https://github.com/FatihKoz" target="_blank">&copy; B.Fatih KOZ</a></p>
    </div>
  </div>
  
  <div class="row text-center" style="margin-left:5px; margin-right:5px;">
    <div class="col-sm-12">
      
      <div class="col-sm-8">
        <div class="card border-blue-bottom" style="padding:10px;">
          <?php echo $__env->make('DAirports::airports_table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
      </div>
      
      <div class="col-sm-4">
        <div class="card border-blue-bottom" style="padding:5px;">
          <br>
          <a href="<?php echo e(route('DAirports.update_all')); ?>" class="btn btn-primary btn-sm" style="margin-top:5px;">
            Download & Update <?php if(DA_Setting('dairports.update_only', true) === false): ?> and Create <?php endif; ?></a>
          <br><br>
          <span class="text-info">Download latest airport data and process (be patient)</span>
        </div>
        <div class="card border-blue-bottom" style="padding:5px;">
          <br>
          <a href="<?php echo e(route('DAirports.fix_uzbekistan')); ?>" class="btn btn-warning btn-sm" style="margin-top:5px;">Fix Uzbekistan Codes</a>
          <br><br>
          <span class="text-info">Airports, Flights and Pireps will be checked & updated with new codes</span>
        </div>
        <div class="card border-blue-bottom" style="padding:5px;">
          <br>
          <a href="<?php echo e(route('DAirports.cleanup_airports')); ?>" class="btn btn-danger btn-sm" style="margin-top:5px;" onclick="return confirm('This will delete airport records !!!\n\n Are you sure ?')">Cleanup Airports</a>
          <br><br>
          <span class="text-info">Keep only scheduled and flown airports (including alternates)</span>
        </div>
        <div class="card border-blue-bottom" style="padding:5px;">
          <b>Module Settings</b>
          <br>
          <?php echo $__env->make('DAirports::settings_table', ['group' => 'General'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
          <span class="text-info">CRON is needed for automation, manual imports are always possible</span>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/DisposableAirports/Providers/../Resources/views/index.blade.php ENDPATH**/ ?>