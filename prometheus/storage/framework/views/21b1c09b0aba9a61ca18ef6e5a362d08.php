<div class="sidebar" data-background-color="white" data-active-color="info">

  <!--
      Tip 1: you can change the color of the sidebar's background using: data-background-color="white | black"
      Tip 2: you can change the color of the active button using the data-active-color="primary | info | success | warning | danger"
  -->


  <div class="sidebar-wrapper">
    <div class="logo" style="background: #067ec1; margin: 0px; text-align: center; min-height: 74px;">
      <a href="<?php echo e(url('/dashboard')); ?>">
        <img src="<?php echo e(public_asset('/assets/img/logo_blue_bg.svg')); ?>" width="110px" style="">
      </a>
    </div>

    <ul class="nav">
      <?php echo $__env->make('admin.menu', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </ul>

    <br/>

    <div class="row" style="margin-bottom: 20px;">
      <div class="col-xs-12 text-center">
        <a class="small"
           style="cursor: pointer"
           data-container="body"
           data-toggle="popover"
           data-placement="right"
           data-content="<?php echo e($version_full); ?>">
          version <?php echo e($version); ?>

        </a>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/sidebar.blade.php ENDPATH**/ ?>