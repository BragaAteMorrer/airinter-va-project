<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport'/>

  <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e(config('app.name')); ?></title>

  
  <meta name="base-url" content="<?php echo url(''); ?>">
  <meta name="api-key" content="<?php echo Auth::check() ? Auth::user()->api_key: ''; ?>">
  <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
  

  <link rel="shortcut icon" type="image/png" href="<?php echo e(public_asset('/assets/img/favicon.png')); ?>"/>
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet"/>
  <link href="<?php echo e(public_asset('/assets/frontend/css/bootstrap.min.css')); ?>" rel="stylesheet"/>
  <link href="<?php echo e(public_mix('/assets/frontend/css/now-ui-kit.css')); ?>" rel="stylesheet"/>
  <link href="<?php echo e(public_asset('/assets/frontend/css/styles.css')); ?>" rel="stylesheet"/>
  <link href="<?php echo e(public_asset('/promethee-assets/global-shell.css')); ?>" rel="stylesheet"/>

  
  <link href="<?php echo e(public_mix('/assets/global/css/vendor.css')); ?>" rel="stylesheet"/>
  <?php echo $__env->yieldContent('css'); ?>
  <?php echo $__env->yieldContent('scripts_head'); ?>
  

</head>
<body class="promethee-shell">
<!-- Navbar -->
<nav class="navbar navbar-expand-lg " style="background: #067ec1;">
  <a class="navbar-brand text-white" href="<?php echo e(url('/')); ?>" style="margin-left: 20px;">
    <img src="<?php echo e(public_asset('/assets/img/logo_blue_bg.svg')); ?>" width="135px" alt=""/>
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navigation"
          aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
    <i class="fas fa-bars text-white"></i>
  </button>
  <div class="collapse navbar-collapse justify-content-end" id="navigation">
    <?php echo $__env->make('nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  </div>
</nav>
<!-- End Navbar -->
<div id="top_anchor" class="clearfix" style="height: 25px;"></div>
<div class="wrapper">
  <div class="clear"></div>
  <div class="container-fluid" style="width: 85%!important;">

    
    <?php echo $__env->make('flash.message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php echo $__env->yieldContent('content'); ?>
    

  </div>
  <div class="clearfix" style="height: 200px;"></div>

  <footer class="footer footer-default">
    <div class="container">
      <div class="copyright">
        
        powered by <a href="http://www.phpvms.net" target="_blank">phpvms</a>
      </div>
    </div>
  </footer>
</div>


<?php echo $__env->make('external_redirect_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<script defer src="https://use.fontawesome.com/releases/v5.0.6/js/all.js"></script>


<script src="<?php echo e(public_mix('/assets/global/js/vendor.js')); ?>"></script>
<script src="<?php echo e(public_mix('/assets/frontend/js/vendor.js')); ?>"></script>
<script src="<?php echo e(public_mix('/assets/frontend/js/app.js')); ?>"></script>
<?php echo $__env->yieldContent('scripts'); ?>


<script>
  window.addEventListener("load", function () {
    window.cookieconsent.initialise({
      palette: {
        popup: {
          background: "#edeff5",
          text: "#838391"
        },
        button: {
          "background": "#067ec1"
        }
      },
      position: "top",
    })
  });
</script>


<script>
  $(document).ready(function () {
    $("select.select2").select2({width: 'resolve'});
  });
</script>


<?php
$gtag = setting('general.google_analytics_id');
?>
<?php if($gtag): ?>
  <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo e($gtag); ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo e($gtag); ?>');
</script>
<?php endif; ?>


</body>
</html>
<?php /**PATH /home/jewe0363/promethee/resources/views/layouts/beta/app.blade.php ENDPATH**/ ?>