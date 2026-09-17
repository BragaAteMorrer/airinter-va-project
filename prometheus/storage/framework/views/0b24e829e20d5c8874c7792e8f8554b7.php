<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>" data-era="modern">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<title><?php echo $__env->yieldContent('title', __('promethee.operations_centre')); ?> · Prométhée · Air Inter</title>
<link rel="stylesheet" href="<?php echo e(asset('promethee-assets/promethee.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('promethee-assets/promethee-v2.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('promethee-assets/promethee-distinction.css')); ?>">
<script src="<?php echo e(asset('promethee-assets/promethee.js')); ?>" defer></script>
</head>
<body>
<a class="skip" href="#main"><?php echo e(__('promethee.skip_to_content')); ?></a>
<aside class="sidebar">
<a class="brand" href="<?php echo e(route('promethee.dashboard')); ?>"><span class="brand-logo-shell"><img class="brand-logo" src="<?php echo e($branding['url']); ?>" alt="Air Inter"><img class="brand-logo-minitel" src="<?php echo e(asset('promethee-assets/logos/air-inter-minitel.png')); ?>" alt="Air Inter"></span><span class="brand-caption"><?php echo e(__('promethee.virtual_airline')); ?><br><?php echo e(__('promethee.french_domestic_network')); ?></span></a>
<div class="system-name"><span class="eyebrow"><?php echo e(__('promethee.operations_centre')); ?></span><strong>Prométhée<span class="cursor">_</span></strong><small><?php echo e(__('promethee.airline_slogan')); ?></small></div>
<?php if(auth()->guard()->check()): ?>
<?php ($sidebarPilot = Auth::user() ?: new \App\Models\User(['name' => __('promethee.visitor_access'), 'pilot_id' => 'AIR INTER'])); ?>
<section class="pilot-space" aria-label="<?php echo e(__('promethee.pilot_area')); ?>">
<span class="pilot-space-label"><i class="status-dot"></i> <?php echo e(__('promethee.pilot_area')); ?></span>
<a class="pilot-card-link" href="<?php echo e(route('promethee.profile')); ?>" aria-label="<?php echo e(__('promethee.open_profile')); ?>">
<span class="pilot-avatar">
<?php if($sidebarPilot?->avatar): ?><img src="<?php echo e($sidebarPilot->avatar->url); ?>" alt="Photo de <?php echo e($sidebarPilot->name); ?>">
<?php else: ?><img src="<?php echo e($sidebarPilot ? $sidebarPilot->gravatar(96) : asset('promethee-assets/logos/air-inter-1970s.png')); ?>" alt="Avatar Air Inter">
<?php endif; ?>
</span>
<span class="pilot-identity"><strong><?php echo e($sidebarPilot->name); ?></strong><small><?php echo e($sidebarPilot->pilot_id ?: 'ITF---'); ?></small><em><?php echo e($sidebarPilot->rank?->name ?? 'Pilote Air Inter'); ?><?php if($sidebarPilot->home_airport_id): ?> · <?php echo e($sidebarPilot->home_airport_id); ?><?php endif; ?></em></span>
<b class="pilot-open">↗</b>
</a>
<div class="pilot-space-actions"><a href="<?php echo e(route('promethee.profile')); ?>"><?php echo e(__('promethee.view_my_profile')); ?></a><a href="<?php echo e(url('/logout')); ?>"><?php echo e(__('promethee.logout')); ?></a></div>
</section>
<?php else: ?>
<section class="pilot-space" aria-label="<?php echo e(__('promethee.visitor_access')); ?>"><span class="pilot-space-label"><i class="status-dot"></i> <?php echo e(__('promethee.visitor_access')); ?></span><span class="pilot-identity"><strong><?php echo e(__('promethee.public_report')); ?></strong><small>Air Inter VA</small><em><?php echo e(__('promethee.read_only')); ?></em></span><div class="pilot-space-actions"><a href="<?php echo e(route('login')); ?>"><?php echo e(__('promethee.login')); ?></a><a href="<?php echo e(route('register')); ?>"><?php echo e(__('promethee.register')); ?></a></div></section>
<?php endif; ?>
<nav aria-label="Navigation principale">
<?php if(auth()->guard()->check()): ?>
<?php $__currentLoopData = ['dashboard'=>['01','dashboard'],'operations'=>['02','operations'],'flights'=>['03','flight_schedule'],'missions'=>['04','missions_circuits'],'assignments'=>['05','assignments'],'shop'=>['06','shop'],'transfers'=>['07','transfers'],'jumpseat'=>['08','jumpseat'],'acars'=>['09','acars'],'calendar'=>['10','calendar'],'pilots'=>['11','community'],'passport'=>['12','passport'],'pireps'=>['13','latest_pireps'],'safety'=>['14','flight_safety']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if($key === 'pireps'): ?>
<a class="<?php echo \Illuminate\Support\Arr::toCssClasses(['nav-link','selected'=>request()->routeIs('promethee.pireps.*')]); ?>" href="<?php echo e(route('promethee.public.pireps')); ?>"><span><?php echo e($item[0]); ?></span><?php echo e(__('promethee.'.$item[1])); ?><b>↗</b></a>
<?php else: ?>
<a class="<?php echo \Illuminate\Support\Arr::toCssClasses(['nav-link','selected'=>request()->routeIs('promethee.'.$key)]); ?>" href="<?php echo e(route('promethee.'.$key)); ?>"><span><?php echo e($item[0]); ?></span><?php echo e(__('promethee.'.$item[1])); ?><b>↗</b></a>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if (app('laratrust')->ability('admin','admin-access')) : ?>
<a class="<?php echo \Illuminate\Support\Arr::toCssClasses(['nav-link','selected'=>request()->routeIs('admin.promethee.*')]); ?>" href="<?php echo e(route('admin.promethee.dashboard')); ?>"><span>11</span><?php echo e(__('promethee.administration')); ?><b>↗</b></a>
<?php endif; // app('laratrust')->ability ?>
<?php else: ?>
<a class="nav-link" href="<?php echo e(route('promethee.occ')); ?>"><span>01</span><?php echo e(__('promethee.public_home')); ?><b>↗</b></a>
<a class="nav-link" href="<?php echo e(route('promethee.public.pireps')); ?>"><span>02</span><?php echo e(__('promethee.completed_flights')); ?><b>↗</b></a>
<?php endif; ?>
</nav>
</aside>
<div class="workspace">
<header class="topbar"><span class="breadcrumb">AIR INTER <span>/</span> PROMÉTHÉE <span>/</span> <?php echo $__env->yieldContent('title','EXPLOITATION'); ?></span>
<label class="theme-control"><?php echo e(__('promethee.language')); ?> <select aria-label="<?php echo e(__('promethee.language')); ?>" onchange="if(this.value) window.location=this.value"><?php $__currentLoopData = config('languages'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code=>$language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e(route('promethee.language',$code)); ?>" <?php if(app()->getLocale() === $code): echo 'selected'; endif; ?>><?php echo e($language['display']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
<label class="theme-control"><?php echo e(__('promethee.display')); ?> <select id="era" aria-label="<?php echo e(__('promethee.display_style')); ?>"><option value="modern"><?php echo e(__('promethee.modern')); ?></option><option value="2000"><?php echo e(__('promethee.year_2000')); ?></option><option value="minitel"><?php echo e(__('promethee.minitel')); ?></option></select></label>
<time id="utc-clock">UTC</time></header>
<main id="main">
<?php if(session('success')): ?><div class="notice success" role="status"><?php echo e(session('success')); ?></div><?php endif; ?>
<?php if($errors->any()): ?><div class="notice error" role="alert"><strong><?php echo e(__('promethee.input_error')); ?></strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div><?php endif; ?>
<?php echo $__env->yieldContent('content'); ?>
</main>
<footer class="footer"><span>AIR INTER · PROMÉTHÉE</span><span><?php echo e(__('promethee.airline_simulation')); ?> · <?php echo e(date('Y')); ?></span><span class="tricolor"><i></i><i></i><i></i></span></footer>
</div>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body></html>
<?php /**PATH /home/jewe0363/promethee/modules/Promethee/Providers/../Resources/views/layout.blade.php ENDPATH**/ ?>