<li>
  <a href="<?php echo e(url('/admin/dashboard')); ?>"><i class="pe-7s-display1"></i>dashboard</a>
</li>

<li>
  <a data-toggle="collapse" href="#operations_menu" class="menu operations_menu" aria-expanded="true">
    <h5>operations&nbsp;<b class="pe-7s-angle-right"></b></h5>
  </a>

  <div class="collapse" id="operations_menu" aria-expanded="true">
    <ul class="nav">
      <?php if (app('laratrust')->ability('admin', 'pireps')) : ?>
      <li><a href="<?php echo e(url('/admin/pireps')); ?>"><i class="pe-7s-cloud-upload"></i>pireps
          <span data-toggle="tooltip" title="3 New" class="badge bg-light-blue pull-right">3</span>
        </a>
      </li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'flights')) : ?>
      <li><a href="<?php echo e(url('/admin/flights')); ?>"><i class="pe-7s-vector"></i>flights</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'fleet')) : ?>
      <li><a href="<?php echo e(url('/admin/subfleets')); ?>"><i class="pe-7s-plane"></i>fleet</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'fares')) : ?>
      <li><a href="<?php echo e(url('/admin/fares')); ?>"><i class="pe-7s-graph2"></i>fares</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'finances')) : ?>
      <li><a href="<?php echo e(url('/admin/finances')); ?>"><i class="pe-7s-display1"></i>finances</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'users')) : ?>
      <li><a href="<?php echo e(url('/admin/users')); ?>"><i class="pe-7s-users"></i>users</a></li>
      <?php endif; // app('laratrust')->ability ?>
    </ul>
  </div>
</li>

<li>
  <a data-toggle="collapse" href="#config_menu" class="menu config_menu" aria-expanded="true">
    <h5>config&nbsp;<b class="pe-7s-angle-right"></b></h5>
  </a>

  <div class="collapse" id="config_menu" aria-expanded="true">
    <ul class="nav">
      <?php if (app('laratrust')->ability('admin', 'airlines')) : ?>
      <li><a href="<?php echo e(url('/admin/airlines')); ?>"><i class="pe-7s-paper-plane"></i>airlines</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'aircraft', 'fleet')) : ?>
      <li><a href="<?php echo e(url('/admin/airframes')); ?>"><i class="pe-7s-plane"></i>sb airframes</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'airports')) : ?>
      <li><a href="<?php echo e(url('/admin/airports')); ?>"><i class="pe-7s-map-marker"></i>airports</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'finances')) : ?>
      <li><a href="<?php echo e(url('/admin/expenses')); ?>"><i class="pe-7s-cash"></i>expenses</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'ranks')) : ?>
      <li><a href="<?php echo e(url('/admin/ranks')); ?>"><i class="pe-7s-graph1"></i>ranks</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'typeratings')) : ?>
      <li><a href="<?php echo e(url('/admin/typeratings')); ?>"><i class="pe-7s-plane"></i>type ratings</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'awards')) : ?>
      <li><a href="<?php echo url('/admin/awards'); ?>"><i class="pe-7s-diamond"></i>awards</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'users')) : ?>
      <li><a href="<?php echo url('/admin/roles'); ?>"><i class="pe-7s-network"></i>roles</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'pages')) : ?>
      <li><a href="<?php echo url('/admin/pages'); ?>"><i class="pe-7s-note"></i>pages/links</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'modules')) : ?>
      <li><a href="<?php echo url('/admin/modules'); ?>"><i class="pe-7s-box2"></i>addons/modules</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'maintenance')) : ?>
      <li><a href="<?php echo e(url('/admin/maintenance')); ?>"><i class="pe-7s-tools"></i>maintenance</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if(config('activitylog.enabled', true) === true): ?>
        <?php if (app('laratrust')->ability('admin', 'admin-access')) : ?>
        <li><a href="<?php echo e(url('/admin/activities')); ?>"><i class="pe-7s-news-paper"></i>activities</a></li>
        <?php endif; // app('laratrust')->ability ?>
      <?php endif; ?>

      <?php if (app('laratrust')->ability('admin', 'logs')) : ?>
      <li><a href="<?php echo e(url('/admin/log-viewer')); ?>"><i class="pe-7s-note2"></i>logs</a></li>
      <?php endif; // app('laratrust')->ability ?>

      <?php if (app('laratrust')->ability('admin', 'settings')) : ?>
      <li><a href="<?php echo e(url('/admin/settings')); ?>"><i class="pe-7s-config"></i>settings</a></li>
      <?php endif; // app('laratrust')->ability ?>
    </ul>
  </div>
</li>

<li>
  <a data-toggle="collapse" href="#addons_menu" class="menu addons_menu" aria-expanded="true">
    <h5>addons&nbsp;<b class="pe-7s-angle-right"></b></h5>
  </a>

  <div class="collapse" id="addons_menu" aria-expanded="true">
    <ul class="nav">
      <?php if (app('laratrust')->ability('admin', 'addons')) : ?>
      <?php $__currentLoopData = $moduleSvc->getAdminLinks(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as &$link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li><a href="<?php echo e(url($link['url'])); ?>"><i class="<?php echo e($link['icon']); ?>"></i><?php echo e($link['title']); ?></a></li>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <?php endif; // app('laratrust')->ability ?>
    </ul>
  </div>
</li>

<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/menu.blade.php ENDPATH**/ ?>