<?php
$ivao_id = optional($user->fields->firstWhere('name', $sp_settings['fieldivao']))->value;
$vatsim_id = optional($user->fields->firstWhere('name', $sp_settings['fieldvatsim']))->value;
$discord_id = optional($user->fields->firstWhere('name', $sp_settings['fielddiscord']))->value;
?>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
      <div class="form-group form-bg-grey rounded my-3">
         <div class="row">
            <label for="name" class="col-5 control-label"><i class="ph-fill ph-user align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.name'); ?></label>
            <div class="col-7">
               <input type="text" name="name" id="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e($user->name); ?>">
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded mb-3">
         <div class="row">
            <label class="col-5 control-label"><i class="ph-fill ph-building align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.airline'); ?></label>
            <div class="col-7">
               <div class="input-group input-group-lg">
                  <select name="airline_id" id="airline_id" class="form-select select2 <?php $__errorArgs = ['airline_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                     <?php $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline_id => $airline_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <option value="<?php echo e($airline_id); ?>" <?php if(request()->get('airline_id') == $airline_id): ?> selected <?php endif; ?>><?php echo e($airline_label); ?></option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
               </div>
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded mb-3">
         <div class="row">
            <label class="col-5 control-label"><i class="ph-fill ph-clock-user align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.timezone'); ?></label>
            <div class="col-7">
               <div class="input-group input-group-lg">
                  <select name="timezone" id="timezone" class="form-select select2 <?php $__errorArgs = ['timezone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                     <?php $__currentLoopData = $timezones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group_name => $group_timezones): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <optgroup label="<?php echo e($group_name); ?>">
                        <?php $__currentLoopData = $group_timezones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timezone_id => $timezone_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($timezone_id); ?>" <?php if($timezone_id===$user->timezone): ?> selected <?php endif; ?>>
                           <?php echo $timezone_label; ?>

                        </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     </optgroup>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
               </div>
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded my-3">
         <div class="row">
            <label for="field_vatsim_id" class="col-5 control-label"><i class="ph-fill ph-cell-tower align-middle fs-20 me-1"></i>VATSIM ID</label>
            <div class="col-4">
               <input type="number" name="field_vatsim_id" id="field_vatsim_id" class="form-control <?php $__errorArgs = ['field_vatsim_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e($vatsim_id); ?>">
            </div>
            <div class="col-3 text-center mt-1">
               <?php if(config('services.vatsim.enabled') == true && blank($user->vatsim_id)): ?>
               <a href="<?php echo e(route('oauth.redirect', ['provider' => 'vatsim'])); ?>" class="btn btn-sm btn-success"><i class="ph-fill ph-link align-middle fs-20 mx-1"></i> Link VATSIM</a>
               <?php elseif(config('services.vatsim.enabled') == true && filled($user->vatsim_id)): ?>
               <a href="<?php echo e(route('oauth.logout', ['provider' => 'vatsim'])); ?>" class="btn btn-sm btn-danger"><i class="ph-fill ph-link-break align-middle fs-20 mx-1"></i> Unlink VATSIM</a>
               <?php endif; ?>
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded my-3">
         <div class="row">
            <label for="field_discord_id" class="col-5 control-label"><i class="ph-fill ph-cell-tower align-middle fs-20 me-1"></i>DISCORD ID</label>
            <div class="col-4">
               <input type="number" name="field_discord_id" id="field_discord_id" class="form-control <?php $__errorArgs = ['field_discord_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e($discord_id); ?>">
            </div>
            <div class="col-3 text-center mt-1">
               <?php if(config('services.discord.enabled') == true && blank($user->discord_id)): ?>
               <a href="<?php echo e(route('oauth.redirect', ['provider' => 'discord'])); ?>" class="btn btn-sm btn-success"><i class="ph-fill ph-link align-middle fs-20 mx-1"></i> Link DISCORD</a>
               <?php elseif(config('services.discord.enabled') == true && filled($user->discord_id)): ?>
               <a href="<?php echo e(route('oauth.logout', ['provider' => 'discord'])); ?>" class="btn btn-sm btn-danger"><i class="ph-fill ph-link-break align-middle fs-20 mx-1"></i> Unlink DISCORD</a>
               <?php endif; ?>
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded mb-3">
         <div class="row">
            <label class="col-5 control-label mt-1"><i class="ph-fill ph-image align-middle fs-20 me-1"></i><?php echo app('translator')->get('profile.avatar'); ?></label>
            <div class="col-7">
               <div class="input-group input-group-lg">
                  <input type="file" name="avatar" id="avatar" class="form-control <?php $__errorArgs = ['avatar'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
               </div>
               <p class="small text-muted mb-0"> <?php echo e(__('profile.avatarresize', [ 'width' => config('phpvms.avatar.width'), 'height' => config('phpvms.avatar.height'), ])); ?></p>
            </div>
         </div>
      </div>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
      <div class="form-group form-bg-grey rounded my-3">
         <div class="row">
            <label for="email" class="col-5 control-label"><i class="ph-fill ph-at align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.email'); ?></label>
            <div class="col-7">
               <input type="email" name="email" id="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e($user->email); ?>">
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded mb-3">
         <div class="row">
            <label class="col-5 control-label"><i class="ph-fill ph-house-simple align-middle fs-20 me-1"></i><?php echo app('translator')->get('airports.home'); ?></label>
            <div class="col-7">
               <div class="input-group input-group-lg">
                  <?php if(Module::has('SPTransfer') && Module::isEnabled('SPTransfer')): ?>
                  <select name="home_airport_id" id="home_airport_id" class="form-select select2 <?php $__errorArgs = ['airline_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" readonly>
                     <?php else: ?>
                     <select name="home_airport_id" id="home_airport_id" class="form-select airport_search <?php if($hubs_only): ?> hubs_only <?php endif; ?> <?php $__errorArgs = ['home_airport_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo app('translator')->get('sptheme.typetosearch'); ?>">
                        <?php endif; ?>
                        <?php $__currentLoopData = $airports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport_id => $airport_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($airport_id); ?>"><?php echo e($airport_label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     </select>
               </div>
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded mb-3">
         <div class="row">
            <label class="col-5 control-label"><i class="ph-fill ph-globe-simple align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.country'); ?></label>
            <div class="col-7">
               <div class="input-group input-group-lg">
                  <select name="country" id="country" class="form-select select2 <?php $__errorArgs = ['country'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                     <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country_id => $country_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <option value="<?php echo e($country_id); ?>" <?php if($user->country === $country_id): ?> selected <?php endif; ?>>
                        <?php echo e($country_label); ?>

                     </option>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
               </div>
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded my-3">
         <div class="row">
            <label for="field_ivao_id" class="col-5 control-label"><i class="ph-fill ph-cell-tower align-middle fs-20 me-1"></i>IVAO ID</label>
            <div class="col-4">
               <input type="number" name="field_ivao_id" id="field_ivao_id" class="form-control <?php $__errorArgs = ['field_ivao_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e($ivao_id); ?>">
            </div>
            <div class="col-3 text-center mt-1">
               <?php if(config('services.ivao.enabled') == true && blank($user->ivao_id)): ?>
               <a href="<?php echo e(route('oauth.redirect', ['provider' => 'ivao'])); ?>" class="btn btn-sm btn-success"><i class="ph-fill ph-link align-middle fs-20 mx-1"></i> Link IVAO</a>
               <?php elseif(config('services.ivao.enabled') == true && filled($user->ivao_id)): ?>
               <a href="<?php echo e(route('oauth.logout', ['provider' => 'ivao'])); ?>" class="btn btn-sm btn-danger"><i class="ph-fill ph-link-break align-middle fs-20 mx-1"></i> Unlink IVAO</a>
               <?php endif; ?>
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
unset($__errorArgs, $__bag); ?> bg-white border">
            </div>
         </div>
      </div>
      <div class="form-group form-bg-grey rounded mb-3">
         <div class="row">
            <label for="password" class="col-5 control-label mt-1"><i class="ph-fill ph-key align-middle fs-20 me-1"></i><?php echo app('translator')->get('passwords.confirm'); ?></label>
            <div class="col-7">
               <input type="password" name="password_confirmation" id="password_confirmation" class="form-control <?php $__errorArgs = ['password_confirmation'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border">
            </div>
         </div>
      </div>
      <div class="form-group pt-2">
         <div class="row">
            <div class="col-sm-10">
               <div class="input-group">
                  <span class="input-group-text">
                     <input type="hidden" name="opt_in" value="0" />
                     <div class="checker">
                        <span><input type="checkbox" name="opt_in" id="opt_in" value="1" <?php echo e($user->opt_in ? 'checked' : ''); ?>></span>
                     </div>
                  </span>
                  <span class="mt-1 mb-0 p-2" for="checkbox"><?php echo app('translator')->get('profile.opt-in-descrip'); ?></span>
               </div>
            </div>
         </div>
      </div>
   </div>
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
      <div class="row mb-3">
         <?php $__currentLoopData = $userFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
         <?php if(!($field->name == $sp_settings['fieldvatsim'] || $field->name == $sp_settings['fieldivao'])): ?>
         <div class="col-md-<?php echo e($userFields->count() > 1 ? 6 : 12); ?>">
            <div class="form-group form-bg-grey rounded mb-3">
               <div class="row">
                  <label class="col-5 control-label"><i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo e($field->name); ?> <?php if($field->required === true): ?> <span class="tooltiptop" title="Required"><i class="ph-fill ph-star text-danger"></i></span> <?php endif; ?></label>
                  <div class="col-7">
                     <?php if($field->type === 'select'): ?>
                     <div class="input-group input-group-lg">
                        <select name="field_<?php echo e($field->slug); ?>" id="field_<?php echo e($field->slug); ?>" class="form-select select2 <?php if($errors->has('field_' . $field->slug)): ?> is-invalid <?php endif; ?>">
                           <?php $__currentLoopData = $field->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option_id => $option_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <option value="<?php echo e($option_id); ?>" <?php if($field->value === $option_id): ?> selected <?php endif; ?>>
                              <?php echo e($option_label); ?>

                           </option>
                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                     </div>
                     <?php else: ?>
                     <input type="text" name="field_<?php echo e($field->slug); ?>" id="field_<?php echo e($field->slug); ?>" class="form-control bg-white border <?php if($errors->has('field_' . $field->slug)): ?> is-invalid <?php endif; ?>" value="<?php echo e($field->value); ?>">
                     <?php endif; ?>
                  </div>
               </div>
            </div>
         </div>
         <?php endif; ?>
         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <?php if($errors->any()): ?>
      <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo implode('', $errors->all(':message')); ?></div>
      <?php endif; ?>
      <div class="row">
         <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 m-0">
            <a href="javascript:void(0);" title="<?php echo app('translator')->get('profile.newapikey'); ?>" id="new_API_key" class="btn btn-warning w-100"><i class="ph-duotone ph-key"></i> <?php echo app('translator')->get('profile.newapikey'); ?></a>
         </div>
         <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 m-0">
            <button type="submit" class="btn btn-primary w-100"><?php echo app('translator')->get('profile.updateprofile'); ?></button>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/profile/fields.blade.php ENDPATH**/ ?>