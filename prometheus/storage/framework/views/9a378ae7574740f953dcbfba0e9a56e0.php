<div class="nav-tabs-navigation">
  <div class="nav-tabs-wrapper">
    <ul class="navbar-nav align-middle">
      <?php if(Auth::check()): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e(route('frontend.dashboard.index')); ?>">
            <i class="fas fa-tachometer-alt"></i>
            <p><?php echo app('translator')->get('common.dashboard'); ?></p>
          </a>
        </li>
      <?php endif; ?>

      <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('frontend.livemap.index')); ?>">
          <i class="fas fa-globe"></i>
          <p><?php echo app('translator')->get('common.livemap'); ?></p>
        </a>
      </li>

      <li class="nav-item">
        <a class="nav-link" href="<?php echo e(route('frontend.pilots.index')); ?>">
          <i class="fas fa-users"></i>
          <p><?php echo e(trans_choice('common.pilot', 2)); ?></p>
        </a>
      </li>

      
      <?php $__currentLoopData = $moduleSvc->getFrontendLinks($logged_in=false); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e(url($link['url'])); ?>">
            <i class="<?php echo e($link['icon']); ?>"></i>
            <p><?php echo e(($link['title'])); ?></p>
          </a>
        </li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      <?php $__currentLoopData = $page_links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e($page->url); ?>" target="<?php echo e($page->new_window ? '_blank':'_self'); ?>">
            <i class="<?php echo e($page['icon']); ?>"></i>
            <p><?php echo e($page['name']); ?></p>
          </a>
        </li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      <?php if(!Auth::check()): ?>
         <li class="nav-item">
          <a class="nav-link" href="<?php echo e(url('/register')); ?>">
            <i class="far fa-id-card"></i>
            <p><?php echo app('translator')->get('common.register'); ?></p>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e(url('/login')); ?>">
            <i class="fas fa-sign-in-alt"></i>
            <p><?php echo app('translator')->get('common.login'); ?></p>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
            <span class="flag-icon flag-icon-<?php echo e($languages[$locale]['flag-icon']); ?>"></span>&nbsp;&nbsp;<?php echo e($languages[$locale]['display']); ?>

          </a>
          <div class="dropdown-menu dropdown-menu-right">
          <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php if($lang != $locale): ?>
                <a class="dropdown-item" href="<?php echo e(route('frontend.lang.switch', $lang)); ?>">
                  <span class="flag-icon flag-icon-<?php echo e($language['flag-icon']); ?>"></span>&nbsp;&nbsp;<?php echo e($language['display']); ?>

                </a>
              <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </li>
      <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e(route('frontend.flights.index')); ?>">
            <i class="fab fa-avianex"></i>
            <p><?php echo e(trans_choice('common.flight', 2)); ?></p>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e(route('frontend.pireps.index')); ?>">
            <i class="fas fa-cloud-upload-alt"></i>
            <p><?php echo e(trans_choice('common.pirep', 2)); ?></p>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo e(route('frontend.downloads.index')); ?>">
            <i class="fas fa-download"></i>
            <p><?php echo e(trans_choice('common.download', 2)); ?></p>
          </a>
        </li>

        
        <?php $__currentLoopData = $moduleSvc->getFrontendLinks($logged_in=true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo e(url($link['url'])); ?>">
              <i class="<?php echo e($link['icon']); ?>"></i>
              <p><?php echo e(($link['title'])); ?></p>
            </a>
          </li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
            <span class="flag-icon flag-icon-<?php echo e($languages[$locale]['flag-icon']); ?>"></span>&nbsp;&nbsp;<?php echo e($languages[$locale]['display']); ?>

          </a>
          <div class="dropdown-menu dropdown-menu-right">
          <?php $__currentLoopData = $languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang => $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <?php if($lang != $locale): ?>
                <a class="dropdown-item" href="<?php echo e(route('frontend.lang.switch', $lang)); ?>">
                  <span class="flag-icon flag-icon-<?php echo e($language['flag-icon']); ?>"></span>&nbsp;&nbsp;<?php echo e($language['display']); ?>

                </a>
              <?php endif; ?>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </div>
        </li>

        <li class="nav-item dropdown ">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button"
             data-toggle="dropdown" data-boundary="viewport" aria-haspopup="true" aria-expanded="false">
            <?php if(Auth::user()->avatar == null): ?>
              <img src="<?php echo e(Auth::user()->gravatar(38)); ?>" style="height: 38px; width: 38px;">
            <?php else: ?>
              <img src="<?php echo e(Auth::user()->avatar->url); ?>" style="height: 38px; width: 38px;">
            <?php endif; ?>
          </a>
          <div class="dropdown-menu dropdown-menu-right">

            <a class="dropdown-item" href="<?php echo e(route('frontend.profile.index')); ?>">
              <i class="far fa-user"></i>&nbsp;&nbsp;<?php echo app('translator')->get('common.profile'); ?>
            </a>

            <?php if (app('laratrust')->ability('admin', 'admin-access')) : ?>
            <a class="dropdown-item" href="<?php echo e(url('/admin')); ?>">
              <i class="fas fa-circle-notch"></i>&nbsp;&nbsp;<?php echo app('translator')->get('common.administration'); ?>
            </a>
            <?php endif; // app('laratrust')->ability ?>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="<?php echo e(url('/logout')); ?>">
              <i class="fas fa-sign-out-alt"></i>&nbsp;&nbsp;<?php echo app('translator')->get('common.logout'); ?>
            </a>
          </div>
        </li>
      <?php endif; ?>

    </ul>
  </div>
</div>
<?php /**PATH /home/jewe0363/promethee/resources/views/layouts/beta/nav.blade.php ENDPATH**/ ?>