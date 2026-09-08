<?php $__env->startSection('title', __('home.welcome.title')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/0.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<?php if($sp_settings['worldclock_on'] === 1): ?>
   <div class="row">
      <?php echo $__env->make('sptheme::widgets.worldclock', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
<?php endif; ?>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <?php if(!Auth::check()): ?>
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-hand-waving align-middle fs-20 me-1"></i> <?php echo app('translator')->get('sptheme.welcomev'); ?></h4>
            <?php else: ?>
            <h4 class="mt-0 header-title border-bottom">
               <?php if(!empty(Auth::user()->rank) && !empty(Auth::user()->rank->image_url)): ?>
               <img src="<?php echo e(Auth::user()->rank->image_url); ?>" width="45" alt="<?php echo e(Auth::user()->rank->name); ?>" title="<?php echo e(Auth::user()->rank->name); ?>" class="img-fluid tooltipright">
               <?php endif; ?>          
               <?php echo app('translator')->get('sptheme.welcome'); ?> <?php echo e(Auth::user()->rank->name); ?>, <?php echo e(Auth::user()->name); ?>!</h4>
            <?php endif; ?>
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/1.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <p><?php echo nl2br(e($sp_settings['welcome_text'])); ?></p>
         </div>
      </div>
   </div>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-graduation-cap align-middle fs-20 me-1"></i> <?php echo app('translator')->get('common.newestpilots'); ?></h4>
            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="card-body p-2 border-bottom">
               <div class="row">
                  <div class="col-auto my-auto">
                     <div class="me-2">                      
                        <?php if($user->avatar == null): ?>
                        <img src="<?php echo e(public_asset('SPTheme/images/noavatar.png')); ?>" width="42" height="42" alt="<?php echo app('translator')->get('profile.avatar'); ?>" class="rounded-circle bg-primary img-fluid">
                        <?php else: ?>
                        <img src="<?php echo e($user->avatar->url); ?>" width="42" height="42" alt="<?php echo app('translator')->get('profile.avatar'); ?>" class="rounded-circle img-fluid">
                        <?php endif; ?>
                     </div>
                  </div>
                  <div class="col">
                     <?php if(Auth::check()): ?>
                     <h5 class="my-1"><span class="fi fi-<?php echo e($user->country); ?> shadow-img me-1" title="<?php echo app('translator')->get('common.country'); ?>"></span> <a href="<?php echo e(route('frontend.profile.show', [$user->id])); ?>" class="tooltipright" title="<?php echo app('translator')->get('airports.home'); ?>: <?php echo e(optional($user->home_airport)->icao); ?>"><?php echo e($user->name); ?></a></h5>
                     <?php else: ?>
                     <h5 class="my-1"><span class="fi fi-<?php echo e($user->country); ?> shadow-img me-1" title="<?php echo app('translator')->get('common.country'); ?>"></span> <a href="<?php echo e(route('frontend.profile.show', [$user->id])); ?>" class="tooltipright" title="<?php echo app('translator')->get('airports.home'); ?>: <?php echo e(optional($user->home_airport)->icao); ?>"><?php echo e($user->name_private); ?></a></h5>
                     <?php endif; ?>
                     <p class="fs-14 mb-0"><?php echo e(optional($user->home_airport)->name); ?></p>
                     <span class="text-muted small"><?php echo app('translator')->get('sptheme.joined'); ?> <?php echo e($user->created_at->diffForHumans()); ?>.</span>
                  </div>
               </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-sm-6 col-xl-3">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e($totalPilots); ?> <span><i class="ph-fill ph-arrow-fat-line-up text-success"></i></span></h4>
               <p class="mb-0"><?php echo app('translator')->get('sptheme.tpilots'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-user-check"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="col-sm-6 col-xl-3">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e($totalSchedules); ?> <span><i class="ph-fill ph-arrow-fat-line-up text-success"></i></span></h4>
               <p class="mb-0"><?php echo app('translator')->get('sptheme.tschedules'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-globe"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="col-sm-6 col-xl-3">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e($totalAircrafts); ?> <span><i class="ph-fill ph-arrow-fat-line-up text-success"></i></span></h4>
               <p class="mb-0"><?php echo app('translator')->get('sptheme.taircrafts'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-airplane"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="col-sm-6 col-xl-3">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e(\Modules\SPTheme\Services\TimeService::convert($totalHours)); ?> <span><i class="ph-fill ph-arrow-fat-line-up text-success"></i></span></h4>
               <p class="mb-0"><?php echo app('translator')->get('sptheme.fhours'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-airplane-takeoff"></i>    
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/2.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo e($sp_settings['leftbox_title']); ?></h4>
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/3.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <p><?php echo nl2br(e($sp_settings['leftbox_text'])); ?></p>
         </div>
      </div>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo e($sp_settings['rightbox_title']); ?></h4>
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/4.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <p><?php echo nl2br(e($sp_settings['rightbox_text'])); ?></p>
         </div>
      </div>
   </div>
</div>
<?php if($sp_settings['carousel_on'] === 1): ?>
<div class="row">
   <div class="col-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body owl-carousel">
            <div>
               <a href="<?php echo e($sp_settings['carousel_url_5']); ?>" title="<?php echo app('translator')->get('sptheme.banner'); ?>" class="tooltiptop" target="_blank"><img src="<?php echo e(public_asset('/SPTheme/images/banner/5.jpg')); ?>" alt="<?php echo app('translator')->get('sptheme.banner'); ?>" width="240" height="80"></a>
            </div>
            <div>
               <a href="<?php echo e($sp_settings['carousel_url_6']); ?>" title="<?php echo app('translator')->get('sptheme.banner'); ?>" class="tooltiptop" target="_blank"><img src="<?php echo e(public_asset('/SPTheme/images/banner/6.jpg')); ?>" alt="<?php echo app('translator')->get('sptheme.banner'); ?>" width="240" height="80"></a>
            </div>
            <div>
               <a href="<?php echo e($sp_settings['carousel_url_7']); ?>" title="<?php echo app('translator')->get('sptheme.banner'); ?>" class="tooltiptop" target="_blank"><img src="<?php echo e(public_asset('/SPTheme/images/banner/7.jpg')); ?>" alt="<?php echo app('translator')->get('sptheme.banner'); ?>" width="240" height="80"></a>
            </div>
            <div>
               <a href="<?php echo e($sp_settings['carousel_url_8']); ?>" title="<?php echo app('translator')->get('sptheme.banner'); ?>" class="tooltiptop" target="_blank"><img src="<?php echo e(public_asset('/SPTheme/images/banner/8.jpg')); ?>" alt="<?php echo app('translator')->get('sptheme.banner'); ?>" width="240" height="80"></a>
            </div>
            <div>
               <a href="<?php echo e($sp_settings['carousel_url_9']); ?>" title="<?php echo app('translator')->get('sptheme.banner'); ?>" class="tooltiptop" target="_blank"><img src="<?php echo e(public_asset('/SPTheme/images/banner/9.jpg')); ?>" alt="<?php echo app('translator')->get('sptheme.banner'); ?>" width="240" height="80"></a>
            </div>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<?php if($sp_settings['bottombox_on'] === 1): ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo e($sp_settings['bottombox_title']); ?></h4>
            <p><?php echo nl2br(e($sp_settings['bottombox_text'])); ?></p>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/home.blade.php ENDPATH**/ ?>