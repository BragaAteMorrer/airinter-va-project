<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
    <div class="container-fluid">
        <a class="navbar-brand " href="<?php echo e(url('/')); ?>">
            <img src="<?php echo e(public_asset('/assets/img/logo_blue_bg.svg')); ?>" width="135px" alt="phpvms Logo" />
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <?php if(Auth::check()): ?>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link  d-flex gap-1" href="<?php echo e(route('frontend.dashboard.index')); ?>">
                            <i class="bi bi-speedometer2"></i>
                            <?php echo app('translator')->get('common.dashboard'); ?>
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item d-flex align-items-center">
                    <a class="nav-link  d-flex gap-1" href="<?php echo e(route('frontend.livemap.index')); ?>">
                        <i class="bi bi-globe"></i>
                        <?php echo app('translator')->get('common.livemap'); ?>
                    </a>
                </li>

                <li class="nav-item d-flex align-items-center">
                    <a class="nav-link  d-flex gap-1" href="<?php echo e(route('frontend.pilots.index')); ?>">
                        <i class="bi bi-people"></i>
                        <?php echo e(trans_choice('common.pilot', 2)); ?>

                    </a>
                </li>

                
                <?php $__currentLoopData = $moduleSvc->getFrontendLinks($logged_in = false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link  d-flex gap-1" href="<?php echo e(url($link['url'])); ?>">
                            <i class="<?php echo e($link['icon']); ?>"></i>
                            <?php echo e($link['title']); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php $__currentLoopData = $page_links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link  d-flex gap-1" href="<?php echo e($page->url); ?>"
                            target="<?php echo e($page->new_window ? '_blank' : '_self'); ?>">
                            <i class="<?php echo e($page['icon']); ?>"></i>
                            <?php echo e($page['name']); ?>

                        </a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                <?php if(!Auth::check()): ?>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link  d-flex gap-1" href="<?php echo e(url('/register')); ?>">
                            <i class="bi bi-person-vcard"></i>
                            <?php echo app('translator')->get('common.register'); ?>
                        </a>
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link  d-flex gap-1" href="<?php echo e(url('/login')); ?>">
                            <i class="bi bi-box-arrow-in-right"></i>
                            <?php echo app('translator')->get('common.login'); ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link d-flex gap-1" href="<?php echo e(route('frontend.flights.index')); ?>">
                            <i class="bi bi-airplane"></i>
                            <?php echo e(trans_choice('common.flight', 2)); ?>

                        </a>
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <a class="nav-link d-flex gap-1" href="<?php echo e(route('frontend.downloads.index')); ?>">
                            <i class="bi bi-download"></i>
                            <?php echo e(trans_choice('common.download', 2)); ?>

                        </a>
                    </li>

                    
                    <?php $__currentLoopData = $moduleSvc->getFrontendLinks($logged_in = true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item d-flex align-items-center">
                            <a class="nav-link d-flex gap-1" href="<?php echo e(url($link['url'])); ?>">
                                <i class="<?php echo e($link['icon']); ?>"></i>
                                <?php echo e($link['title']); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <li class="nav-item py-2 py-lg-1 col-12 col-lg-auto">
          <div class="d-none d-lg-flex h-100 mx-lg-2 text-body-secondary"></div>
        </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdownMenuLink" role="button"
                            data-bs-toggle="dropdown" data-boundary="viewport" aria-haspopup="true"
                            aria-expanded="false">
                            <?php if(Auth::user()->avatar == null): ?>
                                <img src="<?php echo e(Auth::user()->gravatar(38)); ?>" style="height: 38px; width: 38px;">
                            <?php else: ?>
                                <img src="<?php echo e(Auth::user()->avatar->url); ?>" style="height: 38px; width: 38px;">
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">

                            <a class="dropdown-item" href="<?php echo e(route('frontend.profile.index')); ?>">
                                <i class="bi bi-person"></i>&nbsp;&nbsp;<?php echo app('translator')->get('common.profile'); ?>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo e(route('frontend.pireps.index')); ?>">
                                <i class="bi bi-journal-check"></i>&nbsp;&nbsp;<?php echo e(trans_choice('common.pirep', 2)); ?>

                            </a>
                            <a class="dropdown-item" href="<?php echo e(route('frontend.flights.bids')); ?>">
                                <i class="bi bi-bookmark-check"></i>&nbsp;&nbsp;<?php echo e(trans_choice('flights.mybid', 2)); ?>

                            </a>
                            <div class="dropdown-divider"></div>
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('access_admin')): ?>
                                <a class="dropdown-item" href="<?php echo e(url('/admin')); ?>">
                                    <i class="bi bi-gear"></i>&nbsp;&nbsp;<?php echo app('translator')->get('common.administration'); ?>
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?php echo e(url('/logout')); ?>">
                                <i class="bi bi-box-arrow-right"></i>&nbsp;&nbsp;<?php echo app('translator')->get('common.logout'); ?>
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
                <li class="nav-item py-2 py-lg-1 col-12 col-lg-auto">
          <div class="d-none d-lg-flex h-100 mx-lg-2 text-body-secondary"></div>
        </li>
                <li class="nav-item dropdown my-0 my-md-auto">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
                            <span
                                class="fi fi-<?php echo e($languages[$locale]['flag-icon']); ?>"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if($lang != $locale): ?>
                                    <a class="dropdown-item" href="<?php echo e(route('frontend.lang.switch', $lang)); ?>">
                                        <span
                                            class="fi fi-<?php echo e($language['flag-icon']); ?>"></span>&nbsp;&nbsp;<?php echo e($language['display']); ?>

                                    </a>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </li>
                <li class="nav-item dropdown my-0 my-md-auto">
                    <button class="btn btn-link nav-link py-2 px-0 px-lg-2 dropdown-toggle d-flex align-items-center"
                        id="bd-theme" type="button" aria-expanded="true" data-bs-toggle="dropdown"
                        data-bs-display="static" aria-label="Toggle theme (light)">
                        <i class="bi-sun-fill" id="theme-icon-active"></i>
                        <span class="d-lg-none ms-2" id="bd-theme-text"><?php echo app('translator')->get('common.toggleColors'); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text"
                        data-bs-popper="static">
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center active"
                                data-bs-theme-value="light" aria-pressed="true">
                                <i class="bi-sun-fill"></i>
                                &nbsp;<?php echo app('translator')->get('common.light'); ?>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="dark" aria-pressed="false">
                                <i class="bi-moon-stars-fill"></i>
                                &nbsp;<?php echo app('translator')->get('common.dark'); ?>
                            </button>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center"
                                data-bs-theme-value="system" aria-pressed="false">
                                <i class="bi-circle-half"></i>
                                &nbsp;<?php echo app('translator')->get('common.auto'); ?>
                            </button>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/seven/nav.blade.php ENDPATH**/ ?>