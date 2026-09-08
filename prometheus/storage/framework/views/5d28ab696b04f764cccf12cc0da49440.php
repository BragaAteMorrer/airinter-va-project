<?php $__env->startSection('title', __('auth.register')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/15.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <?php if($sp_settings['registerrules_on'] === 1): ?>
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-book-bookmark align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.basicrules'); ?></h4>
            <div class="alert alert-danger" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo nl2br(e($sp_settings['registerrules_text'])); ?></div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <?php if($sp_crewtest[0]['registertest_on'] === 1): ?>
   <div class="mb-3" id="crewtest">
      <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
         <div class="card border mb-0">
            <div class="card-body">
               <div id="sptheme_test"></div>
            </div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <form method="post" action="<?php echo e(url('/register')); ?>" class="form-horizontal <?php if($sp_crewtest[0]['registertest_on'] === 1): ?> d-none <?php endif; ?>" <?php if($sp_crewtest[0]['registertest_on']===1): ?> id="regform" <?php endif; ?>>
      <?php echo csrf_field(); ?>
      <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
         <div class="card border mb-0">
            <div class="card-body">
               <?php if($errors->any()): ?>
               <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo implode('', $errors->all(':message')); ?></div>
               <?php endif; ?>
               <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-pencil-simple-line align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.trainee-t'); ?> - <?php echo app('translator')->get('common.register'); ?></h4>
               <div class="row">
                  <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
                     <div class="form-group form-bg-grey rounded mb-3">
                        <div class="row">
                           <label for="name" class="col-5 control-label mt-1"><i class="ph-fill ph-user align-middle fs-20 me-1"></i><?php echo app('translator')->get('auth.fullname'); ?></label>
                           <div class="col-7">
                              <input type="text" name="name" id="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e(old('name')); ?>" required>
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
                                    <option value="<?php echo e($country_id); ?>" <?php if($country_id===old('country')): ?> selected <?php endif; ?>><?php echo e($country_label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                 </select>
                              </div>
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
                     <div class="saprator my-3"></div>
                     <?php if(setting('pilots.allow_transfer_hours') === true): ?>
                     <div class="form-group form-bg-grey rounded mb-3">
                        <div class="row">
                           <label for="name" class="col-5 control-label mt-1"><i class="ph-fill ph-clock-countdown align-middle fs-20 me-1"></i><?php echo app('translator')->get('auth.transferhours'); ?></label>
                           <div class="col-7">
                              <input type="number" name="transfer_time" id="transfer_time" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e(old('transfer_time')); ?>">
                           </div>
                        </div>
                     </div>
                     <?php endif; ?>
                  </div>
                  <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
                     <div class="form-group form-bg-grey rounded mb-3">
                        <div class="row">
                           <label for="email" class="col-5 control-label mt-1"><i class="ph-fill ph-at align-middle fs-20 me-1"></i><?php echo app('translator')->get('auth.emailaddress'); ?></label>
                           <div class="col-7">
                              <input type="email" name="email" id="email" class="form-control <?php $__errorArgs = ['email'];
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
                           <label class="col-5 control-label"><i class="ph-fill ph-house-simple align-middle fs-20 me-1"></i><?php echo app('translator')->get('airports.home'); ?></label>
                           <div class="col-7">
                              <div class="input-group input-group-lg">
                                 <select name="home_airport_id" id="home_airport_id" class="form-select airport_search <?php if($hubs_only): ?> hubs_only <?php endif; ?> <?php $__errorArgs = ['home_airport_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" placeholder="<?php echo app('translator')->get('sptheme.typetosearch'); ?>">
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
                           <label class="col-5 control-label"><i class="ph-fill ph-clock-user align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.timezone'); ?></label>
                           <div class="col-7">
                              <div class="input-group input-group-lg">
                                 <?php echo e(Form::select('timezone', $timezones, null, ['id'=>'timezone', 'class' => 'form-control select2' ])); ?>

                              </div>
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
unset($__errorArgs, $__bag); ?> bg-white border" required>
                           </div>
                        </div>
                     </div>
                     <div class="saprator my-3"></div>
                     <?php if($userFields): ?>
                     <?php $__currentLoopData = $userFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <div class="form-group form-bg-grey rounded mb-3">
                        <div class="row">
                           <label for="field_<?php echo e($field->slug); ?>" class="col-5 control-label mt-1"><i class="ph-fill ph-user align-middle fs-20 me-1"></i><?php echo e($field->name); ?></label>
                           <div class="col-7">
                              <input type="text" name="field_<?php echo e($field->slug); ?>" id="field_<?php echo e($field->slug); ?>" class="form-control <?php $__errorArgs = ['field_'.$field->slug];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> bg-white border" value="<?php echo e(old('field_' .$field->slug)); ?>">
                           </div>
                        </div>
                     </div>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php endif; ?>
                  </div>
                  <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
                     <div class="form-group mb-3">
                        <?php echo $__env->make('auth.toc', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     </div>
                     <h5 class="mt-0 header-title border-bottom" for="h-captcha"></h5>
                     <div class="row">
                        <?php if($invite): ?>
                        <input type="hidden" name="invite" value="<?php echo e($invite->id); ?>" />
                        <input type="hidden" name="invite_token" value="<?php echo e(base64_encode($invite->token)); ?>">
                        <?php endif; ?>
                        <div class="col-xxl-5 col-xl-5 col-lg-6 col-md-12 col-sm-12">
                           <?php if($captcha['enabled'] === true): ?>
                           <div class="form-group">
                              <div class="h-captcha" data-sitekey="<?php echo e($captcha['site_key']); ?>"></div>
                              <?php $__errorArgs = ['h-captcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                              <div class="invalid-feedback"><?php echo e($message); ?></div>
                              <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                           </div>
                           <?php endif; ?>
                        </div>
                        <div class="col-xxl-7 col-xl-7 col-lg-6 col-md-12 col-sm-12">
                           <div class="form-group">
                              <div class="row">
                                 <div class="col-sm-10">
                                    <div class="input-group">
                                       <span class="input-group-text">
                                          <div class="checker"><span><input type="checkbox" name="toc_accepted" id="toc_accepted" value="1"></span></div>
                                       </span>
                                       <span class="mt-1 mb-0 p-2" for="checkbox"><?php echo app('translator')->get('auth.tocaccept'); ?></span>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="form-group">
                              <div class="row">
                                 <div class="col-sm-10">
                                    <div class="input-group">
                                       <span class="input-group-text">
                                          <input class="form-check-input" type="hidden" name="opt_in" value="0">
                                          <div class="checker"><span><input type="checkbox" name="opt_in" id="opt_in" value="1"></span></div>
                                       </span>
                                       <span class="mt-1 mb-0 p-2" for="checkbox"><?php echo app('translator')->get('profile.opt-in-descrip'); ?></span>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="d-grid">
                        <button type="submit" class="btn btn-primary" id="register_button" disabled><?php echo app('translator')->get('auth.register'); ?></button>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </form>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<?php if($captcha['enabled']): ?>
<script src="<?php echo e(public_asset('SPTheme/js/api.js')); ?>" async defer></script>
<?php endif; ?>
<?php if($sp_crewtest[0]['registertest_on'] === 1): ?>
<script src="<?php echo e(public_asset('SPTheme/js/plugins/jquery.quiz.js')); ?>"></script>
<script>
   var sptheme_test = window.localStorage.getItem('sptheme_test');
   if (sptheme_test == 1) {
      $('#regform').removeClass('d-none');
      $('#crewtest').addClass('d-none');
      $('#test_done').addClass('d-none');
   } else {
      $('#regform').addClass('d-none');
   }

   const quizJsonPath = "<?php echo e(asset('SPTheme/js/quiz.json')); ?>";

   $('#sptheme_test').quiz({
      quizJson: quizJsonPath,

      onResults: function (good, total) {
         var perc = good / total;
         var alert = $('<div class="alert" role="alert"></div>').prependTo(this);
         var countdown = 5; // Countdown-Timer in Sekunden

         // Timeranzeige erstellen
         var timerElement = $('<span></span>');

         if (perc >= 0 && perc <= 0.75) {
            alert.addClass('alert-danger').html(
               "<i class='ph-fill ph-warning-circle align-middle fs-4 me-1'></i>" +
               "<?php echo app('translator')->get('sptheme.badresult'); ?> <?php echo app('translator')->get('sptheme.redirecting'); ?> <span class='fw-bold' id='countdown-timer'>" +
               countdown +
               "</span> <?php echo app('translator')->get('sptheme.seconds'); ?>"
            );
            window.localStorage.setItem("sptheme_test", 0);
         } else if (perc >= 0.75 && perc <= 1) {
            alert.addClass('alert-success').html(
               "<i class='ph-fill ph-check-circle align-middle fs-4 me-1'></i>" +
               "<?php echo app('translator')->get('sptheme.goodresult'); ?> <?php echo app('translator')->get('sptheme.redirecting'); ?> <span class='fw-bold' id='countdown-timer'>" +
               countdown +
               "</span> <?php echo app('translator')->get('sptheme.seconds'); ?>"
            );
            window.localStorage.setItem("sptheme_test", 1);
         }

         // Countdown-Funktion
         var countdownInterval = setInterval(function () {
            countdown--; // Countdown verringern
            $('#countdown-timer').text(countdown); // Timeranzeige aktualisieren

            if (countdown <= 0) {
               clearInterval(countdownInterval); // Timer stoppen
               window.location = "/register"; // Redirect durchführen
            }
         }, 1000); // Jede Sekunde aktualisieren
      }
   });

      $.quiz('localization', {
      start: "<?php echo app('translator')->get('sptheme.start'); ?>",
      prev: "<?php echo app('translator')->get('sptheme.back'); ?>",
      next: "<?php echo app('translator')->get('sptheme.forward'); ?>",
      results: "<?php echo app('translator')->get('sptheme.goresults'); ?>",
      restart: "<?php echo app('translator')->get('sptheme.backtop'); ?>",
      goodluck: "<?php echo app('translator')->get('sptheme.goodluck'); ?>",
      error: "<?php echo app('translator')->get('sptheme.error'); ?>",
      errmsg: [
         "<?php echo app('translator')->get('sptheme.chooseanswer'); ?>",
         "<?php echo app('translator')->get('sptheme.answerall'); ?>"
      ]
   });
</script>
<?php endif; ?>
<script>
   document.getElementById('toc_accepted').addEventListener('click', function() {
      var registerButton = document.getElementById('register_button');
      if (this.checked) {
         registerButton.removeAttribute('disabled');
      } else {
         registerButton.setAttribute('disabled', 'true');
      }
   });
</script>
<?php echo $__env->make('scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/auth/register.blade.php ENDPATH**/ ?>