<nav class="navbar navbar-expand-lg navbar-default border bg-body-tertiary py-0">
   <div class="container-fluid">
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
         <div class="navbar-header d-lg-none">
            <div class="logo-sm">
               <a href="javascript:void(0)" id="sidebar-toggle-button"><i class="ph-fill ph-list"></i></a>
            </div>
         </div>
         <div class="d-flex">
            <ul class="nav navbar-nav d-none d-lg-flex mb-2 mb-lg-0">
               <li><img src="<?php echo e(asset($sp_settings['logo_url'])); ?>" alt="<?php echo e(config('app.name')); ?>"></li>
            </ul>
         </div>
         <div class="d-flex justify-content-between align-items-center">
            <div class="dropdown me-1 user-dropdown header-item">
               <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                  <span class="fi fi-<?php echo e($languages[$locale]['flag-icon']); ?>"></span>
               </a>
               <ul class="dropdown-menu dropdown-menu-end">
                  <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if($lang != $locale): ?>
                  <li><a class="dropdown-item" href="<?php echo e(route('frontend.lang.switch', $lang)); ?>">
                        <span class="fi fi-<?php echo e($language['flag-icon']); ?>"></span>&nbsp;&nbsp;<?php echo e($language['display']); ?>

                     </a>
                  </li>
                  <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </ul>
            </div>
            <div class="dropdown topbar-head-dropdown header-item">
               <a class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle border-0 tooltipbottom" href="javascript:void(0)" title="<?php echo app('translator')->get('sptheme.toggled-t'); ?>" id="theme-toggle-switch">
                  <i class="ph-fill ph-moon-stars fs-20"></i>
               </a>
            </div>
            <div class="ms-1 header-item">
               <a href="javascript:void(0)" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle border-0 tooltipbottom" title="<?php echo app('translator')->get('sptheme.togglem-t'); ?>" id="collapsed-sidebar-toggle-button"><i class="ph-fill ph-list fs-20"></i></a>
            </div>
            <div class="ms-1 header-item">
               <a href="javascript:void(0)" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle border-0 tooltipbottom" data-bs-toggle="modal" data-bs-target="#onsModal" title="<?php echo app('translator')->get('sptheme.ons-t'); ?>"><i class="ph-fill ph-air-traffic-control fs-20"></i></a>
            </div>
            <div class="ms-1 header-item">
               <a href="javascript:void(0)" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle border-0 tooltipbottom" title="<?php echo app('translator')->get('sptheme.togglef-t'); ?>" id="toggle-fullscreen"><i class="ph-fill ph-arrows-out fs-20"></i></a>
            </div>
            <div class="ms-1 header-item">
               <a href="javascript:void(0)" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle border-0 tooltipbottom right-sidebar-toggle" title="<?php echo app('translator')->get('sptheme.wio-t'); ?>" data-sidebar-id="main-right-sidebar">
                  <i class="ph-fill ph-smiley fs-3"></i>
                  <span class="badge badge-circle badge-success online-pill"> <?php if($counter>0): ?> <?php echo e($counter); ?> <?php else: ?> 0 <?php endif; ?> </span>
               </a>
            </div>
            <?php if(Auth::check()): ?>
            <div class="dropdown ms-1 user-dropdown header-item">
               <a class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle border-0 dropdown-toggle me-1" href="javascript:void(0);" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                  <?php if(Auth::user()->avatar == null): ?>
                  <img src="<?php echo e(public_asset('SPTheme/images/noavatar.png')); ?>" alt="<?php echo app('translator')->get('profile.avatar'); ?>" class="rounded-circle bg-primary img-fluid">
                  <?php else: ?>
                  <img src="<?php echo e(Auth::user()->avatar->url); ?>" alt="<?php echo app('translator')->get('profile.avatar'); ?>" class="rounded-circle img-fluid">
                  <?php endif; ?>
               </a>
               <ul class="dropdown-menu dropdown-menu-end">
                  <li><a href="<?php echo e(url('/dstable')); ?>" class="tooltipleft" title="<?php echo app('translator')->get('sptheme.fdmreports-t'); ?>"><i class="ph-fill ph-flag align-middle fs-18 me-2"></i><?php echo app('translator')->get('sptheme.fdmreports-t'); ?></a></li>
                  <li role="separator" class="divider"></li>
                  <?php $__currentLoopData = $page_links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><a href="<?php echo e($page->url); ?>" class="tooltipleft" target="<?php echo e($page->new_window ? '_blank' : '_self'); ?>" title="<?php echo e($page['name']); ?>"><i class="<?php echo e($page['icon']); ?> align-middle fs-18 me-2"></i><?php echo e($page['name']); ?></a></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  <?php if(optional($page_links)->isNotEmpty()): ?>
                  <li role="separator" class="divider"></li>
                  <?php endif; ?>
                  <?php if (app('laratrust')->ability('admin', 'admin-access')) : ?>
                  <li><a href="<?php echo e(url('/dvatsim')); ?>" class="tooltipleft" title="<?php echo app('translator')->get('sptheme.audit-t'); ?>"><i class="ph-fill ph-file-csv align-middle text-primary fs-18 me-2"></i>VATSIM <?php echo app('translator')->get('sptheme.audit-t'); ?></a></li>
                  <li><a href="<?php echo e(url('/divao')); ?>" class="tooltipleft" title="<?php echo app('translator')->get('sptheme.audit-t'); ?>"><i class="ph-fill ph-file-csv align-middle text-primary fs-18 me-2"></i>IVAO <?php echo app('translator')->get('sptheme.audit-t'); ?></a></li>
                  <li role="separator" class="divider"></li>
                  <li><a href="<?php echo e(url('/admin')); ?>" class="tooltipleft" title="<?php echo app('translator')->get('sptheme.administration-t'); ?>"><i class="ph-fill ph-lock-key align-middle text-warning fs-18 me-2"></i><?php echo app('translator')->get('common.administration'); ?></a></li>
                  <?php endif; // app('laratrust')->ability ?>
                  <li><a href="<?php echo e(url('/logout')); ?>" class="tooltipleft" title="<?php echo app('translator')->get('sptheme.logout-t'); ?>"><i class="ph-fill ph-sign-out align-middle text-danger fs-18 me-2"></i><?php echo app('translator')->get('common.logout'); ?></a></li>
               </ul>
            </div>
            <?php else: ?>
            <div class="dropdown ms-1 user-dropdown header-item">
               <a class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle border-0 dropdown-toggle me-1" href="javascript:void(0);" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                  <img src="<?php echo e(public_asset('SPTheme/images/noavatar.png')); ?>" alt="<?php echo app('translator')->get('profile.avatar'); ?>" class="rounded-circle bg-primary img-fluid">
               </a>
               <ul class="dropdown-menu dropdown-menu-end">
                  <li><a href="<?php echo e(url('/register')); ?>" class="tooltipleft" title="<?php echo app('translator')->get('sptheme.register-t'); ?>"><i class="ph-fill ph-note-pencil align-middle fs-18 me-2"></i><?php echo app('translator')->get('common.register'); ?></a></li>
                  <li><a href="<?php echo e(url('/login')); ?>" class="tooltipleft" title="<?php echo app('translator')->get('sptheme.login-t'); ?>"><i class="ph-fill ph-sign-in align-middle fs-18 me-2"></i><?php echo app('translator')->get('common.login'); ?></a></li>
                  <?php if(optional($page_links)->isNotEmpty()): ?>
                  <li role="separator" class="divider"></li>
                  <?php endif; ?>
                  <?php $__currentLoopData = $page_links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><a href="<?php echo e($page->url); ?>" class="tooltipleft" target="<?php echo e($page->new_window ? '_blank' : '_self'); ?>" title="<?php echo e($page['name']); ?>"><i class="<?php echo e($page['icon']); ?> align-middle fs-18 me-2"></i><?php echo e($page['name']); ?></a></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </ul>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</nav><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/nav.blade.php ENDPATH**/ ?>