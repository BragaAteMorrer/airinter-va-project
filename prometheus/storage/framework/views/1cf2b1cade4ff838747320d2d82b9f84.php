<!doctype html>
<html lang="<?php echo e(app()->getLocale()); ?>">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>"><title><?php echo e(config('app.name')); ?> · Connexion</title>
    <script>try{document.documentElement.dataset.loginEra=localStorage.getItem('promethee-era')||'modern'}catch(e){document.documentElement.dataset.loginEra='modern'}</script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo e(public_asset('/promethee-assets/login.css')); ?>">
</head>
<body class="promethee-auth">
<main class="login-shell">
    <header class="login-topbar"><span class="login-wordmark">AIR INTER · PROMÉTHÉE</span><label class="login-era"><?php echo e(__('promethee.display')); ?> <select id="login-era" aria-label="<?php echo e(__('promethee.display_style')); ?>"><option value="modern"><?php echo e(__('promethee.modern')); ?></option><option value="2000"><?php echo e(__('promethee.year_2000')); ?></option><option value="minitel"><?php echo e(__('promethee.minitel')); ?></option></select></label></header>
    <div class="login-layout">
        <aside class="login-intro"><div><small><?php echo e(__('promethee.operations_centre')); ?></small><h1><?php echo e(__('promethee.welcome_aboard')); ?></h1><p><?php echo e(__('promethee.login_welcome')); ?></p></div><div class="login-route"><span><?php echo e(__('promethee.airline_slogan')); ?></span></div></aside>
        <section class="login-panel"><?php echo $__env->make('flash.message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php echo $__env->yieldContent('content'); ?></section>
    </div>
    <footer class="login-footer">AIR INTER · PROMÉTHÉE · <?php echo e(__('promethee.french_domestic_network')); ?></footer>
</main>
<script src="<?php echo e(public_asset('/promethee-assets/login.js')); ?>" defer></script>
<?php echo $__env->yieldContent('scripts'); ?>
</body>
</html>
<?php /**PATH /home/jewe0363/promethee/resources/views/layouts/beta/auth/login_layout.blade.php ENDPATH**/ ?>