<div class="page-sidebar">
   <a class="logo-box" href="<?php echo e(config('app.url')); ?>">
      <?php if(!Auth::check()): ?>
      <span>
         <i class="ph-fill ph-certificate tooltipright fs-2 align-middle text-primary" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('sptheme.visitor'); ?>"></i>
         <span class="logo-box-text"><?php echo app('translator')->get('sptheme.welcome'); ?></span>
      </span>
      <?php else: ?>
      <span>
         <?php if(Auth::user()->state == 0): ?>
         <i class="ph-fill ph-question tooltipright fs-2 align-middle text-info" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.pending'); ?>"></i>
         <?php endif; ?>
         <?php if(Auth::user()->state == 1): ?>
         <i class="ph-fill ph-check-circle tooltipright fs-2 align-middle text-success" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.active'); ?>"></i>
         <?php endif; ?>
         <?php if(Auth::user()->state == 2): ?>
         <i class="ph-fill ph-stop-circle tooltipright fs-2 align-middle text-danger" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.rejected'); ?>"></i>
         <?php endif; ?>
         <?php if(Auth::user()->state == 3): ?>
         <i class="ph-fill ph-pause-circle tooltipright fs-2 align-middle text-warning" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.on_leave'); ?>"></i>
         <?php endif; ?>
         <?php if(Auth::user()->state == 4): ?>
         <i class="ph-fill ph-warning-circle tooltipright fs-2 align-middle text-danger" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.suspended'); ?>"></i>
         <?php endif; ?>
         <?php if(Auth::user()->state == 5): ?>
         <i class="ph-fill ph-x-circle tooltipright fs-2 align-middle text-danger" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.deleted'); ?>"></i>
         <?php endif; ?>
         <span class="logo-box-text">ID: <?php echo e(Auth::user()->ident); ?></span>
      </span>
      <?php endif; ?>
      <i class="ph-fill ph-minus text-danger" id="sidebar-toggle-button-close"></i>
   </a>
   <div class="page-sidebar-inner">
      <div class="page-sidebar-menu simple-bar" data-Current-Page=index data-simplebar>
         <ul class="accordion-menu">
            <?php if(Auth::check()): ?>
            <li class="accordion-menu-item nav-label"><i class="ph-fill ph-network align-middle fs-20 me-2"></i><span><?php echo app('translator')->get('sptheme.menu'); ?></span></li>
            <li class="accordion-menu-item"><a href="<?php echo e(route('DBasic.news')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.news-t'); ?>"><i class="ph-fill ph-newspaper align-middle fs-18 me-2"></i><span><?php echo app('translator')->get('sptheme.news'); ?></span></a></li>
            <li class="accordion-menu-item"><a href="<?php echo e(route('DSpecial.notams')); ?>" class="tooltipright" title="Important notable changes"><i class="ph-fill ph-bell-ringing align-middle fs-18 me-2"></i><span>NOTAMs</span></a></li>
            <li class="accordion-menu-item">
               <a href=""><i class="menu-icon ph-fill ph-hand-peace"></i><span><?php echo app('translator')->get('sptheme.welcome'); ?></span><i class="accordion-icon ph-fill ph-arrow-fat-line-right"></i></a>
               <ul class="sub-menu">
                  <li class="sub-menu-item"><a href="<?php echo e(url('/')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.home-t'); ?>"><i class="ph-fill ph-house align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.home'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.pilots.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.pilot-t'); ?>"><i class="ph-fill ph-users align-middle fs-18 me-2"></i><?php echo e(trans_choice('common.pilot', 2)); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(url('/dreports')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.ourreports-t'); ?>"><i class="ph-fill ph-airplane-tilt align-middle fs-18 me-2"></i><?php echo e(trans_choice('common.flight', 2)); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.livemap.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.livemap-t'); ?>"><i class="ph-fill ph-globe-simple-x align-middle fs-18 me-2"></i><?php echo app('translator')->get('common.livemap'); ?></a></li>
                  <?php $__currentLoopData = $moduleSvc->getFrontendLinks($logged_in = false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li class="sub-menu-item"><a href="<?php echo e(url($link['url'])); ?>" class="tooltipright" title="<?php echo e($link['title']); ?>"><i class="ph-fill ph-list-magnifying-glass align-middle fs-18 me-2"></i><?php echo e($link['title']); ?></a></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  <li class="sub-menu-item"><a href="<?php echo e(url('/dp_page')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.custompage-t'); ?>"><i class="ph-fill ph-question align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.custompage'); ?></a></li>
               </ul>
            </li>        
            <li class="accordion-menu-item">
               <a href=""><i class="menu-icon ph-fill ph-user-circle-check"></i><span><?php echo app('translator')->get('sptheme.pilotcenter'); ?></span><i class="accordion-icon ph-fill ph-arrow-fat-line-right"></i></a>
               <ul class="sub-menu">
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.profile.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.profile-t'); ?>"><i class="ph-fill ph-user-square align-middle fs-18 me-2"></i><?php echo app('translator')->get('common.profile'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.profile.index')); ?>/<?php echo e(Auth::user()->id); ?>/edit" class="tooltipright" title="<?php echo app('translator')->get('sptheme.settings-t'); ?>"><i class="ph-fill ph-gear align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.settings'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DSpecial.market.show', [Auth::user()->id])); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.boughtitem-t'); ?>"><i class="ph-fill ph-shopping-cart align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.boughtitem'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(url('/passport')); ?>" class="tooltipright" title="Passport"><i class="ph-fill ph-identification-card align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.mypassport'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.hub', [Auth::user()->home_airport_id ?? ''])); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.myhub-t'); ?>"><i class="ph-fill ph-crosshair-simple align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.myhub'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.myairline', [Auth::user()->airline_id])); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.airline-t'); ?>"><i class="ph-fill ph-building align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.airline-t'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.scenery')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.sceneries-t'); ?>"><i class="ph-fill ph-trolley align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.sceneries-t'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DSpecial.assignments')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.assignments-t'); ?>"><i class="ph-fill ph-file-archive align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.assignments'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.pireps.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.reports-t'); ?>"><i class="ph-fill ph-books align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.reports-t'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.flights.bids')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.flights-t'); ?>"><i class="ph-fill ph-address-book align-middle fs-18 me-2"></i><?php echo e(trans_choice('flights.mybid', 2)); ?></a></li>
               </ul>
            </li>
            <li class="accordion-menu-item">
               <a href=""><i class="menu-icon ph-fill ph-buildings"></i><span><?php echo app('translator')->get('sptheme.company'); ?></span><i class="accordion-icon ph-fill ph-arrow-fat-line-right"></i></a>
               <ul class="sub-menu">
                  <li class="sub-menu-item"><a href="<?php echo e(url('/droster')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.pilot-t'); ?>"><i class="ph-fill ph-users align-middle fs-18 me-2"></i><?php echo e(trans_choice('common.pilot', 2)); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.airlines')); ?>" class="tooltipright" title="<?php echo app('translator')->get('common.airline'); ?>"><i class="ph-fill ph-building align-middle fs-18 me-2"></i><?php echo app('translator')->get('common.airline'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.fleet')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.fleet-t'); ?>"><i class="ph-fill ph-airplane-tilt align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.fleet'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(url('/dmaintenance')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.maintenance-t'); ?>"><i class="ph-fill ph-screencast align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.maintenance'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DSpecial.market')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.pilotshop-t'); ?>"><i class="ph-fill ph-bag align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.pilotshop'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(url('/sptransfer/hub')); ?>" class="tooltipright" title="HUB Transfer request"><i class="ph-fill ph-repeat align-middle fs-18 me-2"></i>HUB Transfer</a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(url('/sptransfer/airline')); ?>" class="tooltipright" title="Airline Transfer request"><i class="ph-fill ph-repeat align-middle fs-18 me-2"></i>Airline Transfer</a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.ranks')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.ranks-t'); ?>"><i class="ph-fill ph-medal align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.ranks'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.awards')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.awards-t'); ?>"><i class="ph-fill ph-trophy align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.awards'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.stats')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.statistics-t'); ?>"><i class="ph-fill ph-chart-bar align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.statistics'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.downloads.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.download-t'); ?>"><i class="ph-fill ph-tray-arrow-down align-middle fs-18 me-2"></i><?php echo e(trans_choice('common.download', 2)); ?></a></li>
               </ul>
            </li>
            <li class="accordion-menu-item">
               <a href=""><i class="menu-icon ph-fill ph-hard-drives"></i><span><?php echo app('translator')->get('sptheme.operation'); ?></span><i class="accordion-icon ph-fill ph-arrow-fat-line-right"></i></a>
               <ul class="sub-menu">
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.flights.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.bookings-t'); ?>"><i class="ph-fill ph-book-open-text align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.bookings'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DSpecial.freeflight')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.freeflight-t'); ?>"><i class="ph-fill ph-compass-rose align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.freeflight'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DSpecial.missions')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.missions-t'); ?>"><i class="ph-fill ph-siren align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.missions'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DSpecial.tours')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.tours-t'); ?>"><i class="ph-fill ph-bounding-box align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.tours'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.hubs')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.ourhubs-t'); ?>"><i class="ph-fill ph-crosshair align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.ourhubs'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('DBasic.pireps')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.pilotreports-t'); ?>"><i class="ph-fill ph-books align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.pilotreports'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(route('frontend.livemap.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.liveflights-t'); ?>"><i class="ph-fill ph-map-trifold align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.liveflights'); ?></a></li>
                  <li class="sub-menu-item"><a href="<?php echo e(url('/dlivewx')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.liveweather-t'); ?>"><i class="ph-fill ph-cloud-sun align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.liveweather'); ?></a></li>
               </ul>
            </li>
            <li class="accordion-menu-item nav-label mt-3"><i class="ph-fill ph-lock-key align-middle fs-20 me-2"></i><span><?php echo app('translator')->get('sptheme.private'); ?></span></li>
            <li class="accordion-menu-item"><a href="<?php echo e(url('/dashboard')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.dashboard-t'); ?>"><i class="ph-fill ph-desktop align-middle fs-18 me-2"></i><span><?php echo app('translator')->get('common.dashboard'); ?></span></a></li>
            <li class="accordion-menu-item"><a href="<?php echo e(url('/logout')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.logout-t'); ?>"><i class="ph-fill ph-sign-out align-middle text-danger fs-18 me-2"></i><span><?php echo app('translator')->get('common.logout'); ?></span></a></li>
            <?php else: ?>
            <li class="accordion-menu-item nav-label"><i class="ph-fill ph-network align-middle fs-20 me-2"></i><span><?php echo app('translator')->get('sptheme.menu'); ?></span></li>
            <li class="accordion-menu-item"><a href="<?php echo e(url('/')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.home-t'); ?>"><i class="ph-fill ph-house align-middle fs-18 me-2"></i><span><?php echo app('translator')->get('sptheme.home'); ?></span></a></li>
            <li class="accordion-menu-item"><a href="<?php echo e(route('frontend.pilots.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.pilot-t'); ?>"><i class="ph-fill ph-users align-middle fs-18 me-2"></i><span><?php echo e(trans_choice('common.pilot', 2)); ?></span></a></li>
            <li class="accordion-menu-item"><a href="<?php echo e(url('/dreports')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.flight-t'); ?>"><i class="ph-fill ph-airplane-tilt align-middle fs-18 me-2"></i><span><?php echo e(trans_choice('common.flight', 2)); ?></span></a></li>
            <li class="accordion-menu-item"><a href="<?php echo e(route('frontend.livemap.index')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.livemap-t'); ?>"><i class="ph-fill ph-globe-simple-x align-middle fs-18 me-2"></i><span><?php echo app('translator')->get('common.livemap'); ?></span></a></li>
            <?php $__currentLoopData = $moduleSvc->getFrontendLinks($logged_in = false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li class="accordion-menu-item"><a href="<?php echo e(url($link['url'])); ?>" class="tooltipright" title="<?php echo e($link['title']); ?>"><i class="ph-fill ph-list-magnifying-glass align-middle fs-18 me-2"></i><span><?php echo e($link['title']); ?></span></a></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <li class="accordion-menu-item"><a href="<?php echo e(url('/dp_page')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.custompage-t'); ?>"><i class="ph-fill ph-question align-middle fs-18 me-2"></i><span><?php echo app('translator')->get('sptheme.custompage'); ?></span></a></li>
            <li class="accordion-menu-item nav-label mt-3"><i class="ph-fill ph-lock-key align-middle fs-20 me-2"></i><span><?php echo app('translator')->get('sptheme.private'); ?></span></li>
            <li class="accordion-menu-item"><a href="<?php echo e(url('/register')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.register-t'); ?>"><i class="ph-fill ph-note-pencil align-middle fs-18 me-2"></i><span><?php echo app('translator')->get('common.register'); ?></span></a></li>
            <li class="accordion-menu-item"><a href="<?php echo e(url('/login')); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.login-t'); ?>"><i class="ph-fill ph-sign-in align-middle fs-18 me-2"></i><span><?php echo app('translator')->get('common.login'); ?></span></a></li>
            <?php endif; ?>
         </ul>
      </div>
      <div class="help-box text-center">
         <div class="p-3">
            <?php if(!is_null($sp_settings['youtube']) && !is_null($sp_settings['discord']) && !is_null($sp_settings['instagram'])): ?>
            <h5 class="pt-2"><?php echo app('translator')->get('sptheme.socials'); ?></h5>
            <?php endif; ?>
            <div class="align-items-center">
               <?php if(!is_null($sp_settings['youtube'])): ?>
               <a href="<?php echo e($sp_settings['youtube']); ?>" target="_blank" title="Youtube" class="btn btn-youtube tooltiptop mx-1 p-2"><i class="ph-fill ph-youtube-logo fs-5"></i></a>
               <?php endif; ?>
               <?php if(!is_null($sp_settings['discord'])): ?>
               <a href="<?php echo e($sp_settings['discord']); ?>" target="_blank" title="Discord" class="btn btn-discord tooltiptop mx-1 p-2 "><i class="ph-fill ph-discord-logo fs-5"></i></a>
               <?php endif; ?>
               <?php if(!is_null($sp_settings['instagram'])): ?>
               <a href="<?php echo e($sp_settings['instagram']); ?>" target="_blank" title="Instagram" class="btn btn-instagram tooltiptop mx-1 p-2 "><i class="ph-fill ph-instagram-logo fs-5"></i></a>
               <?php endif; ?>
               <?php if(optional(Auth::user())->hasRole('admin')): ?>
               <p class="small text-muted mb-0 mt-2">PHP v<?php echo e(PHP_VERSION); ?><br>Render Time: <?php echo e(round(microtime(true) - LARAVEL_START, 2)); ?>s</p>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/sidebar.blade.php ENDPATH**/ ?>