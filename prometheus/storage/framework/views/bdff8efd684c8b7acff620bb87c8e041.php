<?php $__env->startSection('title', __('common.login')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/15.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 d-flex justify-content-center align-items-center mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/13.jpg')); ?>" width="320" height="320" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
      <form method="post" action="<?php echo e(url('/login')); ?>" class="form-horizontal">
         <?php echo csrf_field(); ?>
         <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
            <div class="card border mb-0">
               <div class="card-body">
                  <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-lock-key align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.crewlogin'); ?></h4>
                  <p class="text-center "><?php echo app('translator')->get('sptheme.welcome_back', ['app_name' => config('app.name')]); ?></p>
                  <div class="form-group form-bg-grey rounded mb-3">
                     <div class="row">
                        <label for="email" class="col-5 control-label mt-1"><i class="ph-fill ph-user align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.email'); ?> <?php echo app('translator')->get('common.or'); ?> <?php echo app('translator')->get('common.pilot_id'); ?></label>
                        <div class="col-7">
                           <input type="text" name="email" id="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e(old('email')); ?>" required>
                        </div>
                     </div>
                  </div>
                  <div class="form-group form-bg-grey rounded mb-3">
                     <div class="row">
                        <label for="password" class="col-5 control-label mt-1"><i class="ph-fill ph-key align-middle fs-20 me-1"></i><?php echo app('translator')->get('auth.password'); ?></label>
                        <div class="col-7">
                           <input type="password" name="password" id="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" required>
                        </div>
                     </div>
                  </div>
                  <?php if($errors->any()): ?>
                  <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo implode('', $errors->all(':message')); ?></div>
                  <?php endif; ?>
                  <button type="submit" class="btn btn-success w-100"><?php echo app('translator')->get('common.login'); ?></button>
                  <div class="saprator mt-3 mb-1">
                     <span><?php echo app('translator')->get('sptheme.otherlogin'); ?></span>
                  </div>
                  <div class="text-center">
                     <?php if(config('services.discord.enabled')): ?>
                     <a href="<?php echo e(route('oauth.redirect', ['provider' => 'discord'])); ?>" class="btn btn-secondary my-1 w-100"><?php echo app('translator')->get('auth.loginwith', ['provider' => 'Discord']); ?></a>
                     <?php endif; ?>
                     <?php if(config('services.ivao.enabled')): ?>
                     <a href="<?php echo e(route('oauth.redirect', ['provider' => 'ivao'])); ?>" class="btn btn-secondary my-1 w-100"><?php echo app('translator')->get('auth.loginwith', ['provider' => 'IVAO']); ?></a>
                     <?php endif; ?>
                     <?php if(config('services.vatsim.enabled')): ?>
                     <a href="<?php echo e(route('oauth.redirect', ['provider' => 'vatsim'])); ?>" class="btn btn-secondary my-1 w-100"><?php echo app('translator')->get('auth.loginwith', ['provider' => 'VATSIM']); ?></a>
                     <?php endif; ?>
                  </div>
               </div>
               <div class="card-footer d-flex justify-content-between">
                  <a href="<?php echo e(url('/register')); ?>" title="<?php echo app('translator')->get('sptheme.createaccount-t'); ?>" class="tooltiptop"><i class="ph-fill ph-note-pencil align-middle fs-20 me-1"></i><?php echo app('translator')->get('auth.createaccount'); ?></a>
                  <a href="<?php echo e(url('/password/reset')); ?>" title="<?php echo app('translator')->get('sptheme.forgotpassword-t'); ?>" class="tooltiptop"><i class="ph-fill ph-keyhole align-middle fs-20 me-1"></i><?php echo app('translator')->get('auth.forgotpassword'); ?>?</a>
               </div>
            </div>
         </div>
      </form>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/auth/login.blade.php ENDPATH**/ ?>