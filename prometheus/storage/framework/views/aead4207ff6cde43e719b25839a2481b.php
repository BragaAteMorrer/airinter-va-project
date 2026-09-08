<!DOCTYPE html>
<html lang="fr" data-bs-theme="light" data-layout="blank">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <meta name="author" content="<?php echo e(config('app.name')); ?>">
   <title><?php echo $__env->yieldContent('title'); ?> - <?php echo e(config('app.name')); ?></title>
   <meta name="base-url" content="<?php echo url(''); ?>">
   <meta name="api-key" content="<?php echo Auth::check() ? Auth::user()->api_key : ''; ?>">
   <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
   <link rel="shortcut icon" href="<?php echo e(public_asset('SPTheme/images/favicon.png')); ?>">
   <link rel="stylesheet" href="<?php echo e(public_asset('SPTheme/fonts/phosphor.css')); ?>">
   <link rel="stylesheet" type="text/css" href="<?php echo e(public_asset('SPTheme/css/style.css')); ?>">
   <link rel="stylesheet" type="text/css" href="<?php echo e(public_asset('SPTheme/css/plugins/cookieconsent.min.css')); ?>">
   <?php echo $__env->yieldContent('css'); ?>
   <link rel="stylesheet" type="text/css" href="<?php echo e(public_asset('SPTheme/css/colors.css')); ?>">
   <?php echo $__env->yieldContent('scripts_head'); ?>
   <?php
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
   ?>

   <?php if(strpos($userAgent, 'Mac OS X') !== false): ?>
      <style>
         .page-inner { padding: calc(79px + 1.5rem) 1rem 118px 1rem !important; }
      </style>
   <?php endif; ?>
</head>

<body>
   <div class="page-container">
      <?php echo $__env->make('sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <div class="page-header">
         <?php echo $__env->make('nav', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
      <div class="page-content">
         <div class="page-inner">
            <div class="container-fluid">
               <div class="row">
                  <?php echo $__env->make('breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
               </div>
            </div>
            <div class="container-fluid" id="main-wrapper">
               <?php echo $__env->yieldContent('content'); ?>
            </div>
         </div>
      </div>
      <div class="page-footer">
         <div class="container-fluid">
            <div class="row">
               <?php echo $__env->make('footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
         </div>
      </div>
      <div class="page-right-sidebar border border-left" id="main-right-sidebar">
         <?php echo $__env->make('wio', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
   </div>
   <?php echo $__env->make('sptheme::widgets.ons', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   <?php echo $__env->make('external_redirect_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   <script src="<?php echo e(public_mix('/assets/global/js/vendor.js')); ?>"></script>
   <script src="<?php echo e(public_mix('/assets/frontend/js/vendor.js')); ?>"></script>
   <script src="<?php echo e(public_mix('/assets/frontend/js/app.js')); ?>"></script>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/sweetalert2.all.min.js')); ?>"></script>
   <?php echo $__env->yieldContent('scripts'); ?>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/jquery.min.js')); ?>"></script>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/tom-select.complete.min.js')); ?>"></script>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/select2.full.min.js')); ?>"></script>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/bootstrap.bundle.min.js')); ?>"></script>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/simplebar.min.js')); ?>"></script>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/owl.carousel.min.js')); ?>"></script>
   <script src="<?php echo e(public_asset('SPTheme/js/custom.js')); ?>"></script>
   <?php echo $__env->make('flash.message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   <?php
   $gtag = setting('general.google_analytics_id');
   ?>
   <script>
      $(document).ready(function () {
         if (!$(".select2").hasClass("select2-hidden-accessible")) {
            $(".select2").select2({width: '100%'});
         }
      });

      window.addEventListener("load", function() {
         window.cookieconsent.initialise({
            palette: {
               popup: {
                  background: "#f9faff",
                  text: "#343a40"
               },
               button: {
                  background: "#518ce5",
                  text: "#ffffff"
               }
            },
            position: "bottom",
         })
      });
   </script>
   <?php if($gtag): ?>
   <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo e($gtag); ?>"></script>
   <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
         dataLayer.push(arguments);
      }
      gtag('js', new Date());
      gtag('config', '<?php echo e($gtag); ?>');
   </script>
   <?php endif; ?>
</body>

</html><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/app.blade.php ENDPATH**/ ?>