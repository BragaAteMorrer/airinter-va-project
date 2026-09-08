<div class="col-12">
   <div class="page-title mb-15">
      <div class="d-flex justify-content-md-between justify-content-center py-2">
         <div class="d-none d-md-block">
            <ol class="breadcrumb" id="breadcrumb-placeholder">
               <li class="breadcrumb-item fw-bold"><?php echo e(config('app.name')); ?></li>
               <li class="breadcrumb-item" aria-current="page"><?php echo $__env->yieldContent('title'); ?></li>
            </ol>
         </div>
         <div class="pull-right">
            <div class="btn-group mx-auto">
               <ol class="breadcrumb" id="breadcrumb-placeholder">
                  <li class="breadcrumb-item"><i class="ph-fill ph-clock fs-20 me-1"></i><span id="utc_clock"></span></li>
                  <li class="breadcrumb-item"><?php echo e($sp_settings['slogan']); ?></li>
               </ol>
            </div>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/breadcrumb.blade.php ENDPATH**/ ?>