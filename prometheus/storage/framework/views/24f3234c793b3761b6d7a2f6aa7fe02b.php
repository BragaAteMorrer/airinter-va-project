<?php $__env->startSection('title', __('common.dashboard')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/18.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-hand-waving align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.welcome'); ?> <?php echo e($user->rank->name); ?>, <?php echo e($user->name); ?>! <?php if($last_pirep === null): ?> <?php echo app('translator')->get('sptheme.yourlastreport'); ?> <?php endif; ?></h4>
            <?php if(Auth::user()->state === \App\Models\Enums\UserState::ON_LEAVE): ?>
            <div class="alert alert-danger my-3" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.onleave'); ?></div>
            <?php endif; ?>
            <?php if($last_pirep === null): ?>
            <div class="alert alert-info mt-3" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('dashboard.noreportsyet'); ?> <a href="<?php echo e(route('frontend.pireps.create')); ?>"><?php echo app('translator')->get('dashboard.fileonenow'); ?></a></div>
            <?php else: ?>
            <?php echo $__env->make('dashboard.pirep_card', ['pirep' => $last_pirep], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e($user->flights); ?></h4>
               <p class="mb-0"><?php echo e(trans_choice('common.flight', $user->flights)); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-airplane-in-flight"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo \App\Support\Units\Time::minutesToTimeString($user->flight_time); ?></h4>
               <p class="mb-0"><?php echo app('translator')->get('flights.flighthours'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-clock-countdown"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12">
      <?php echo app('arrilot.widget')->run('DBasic::JournalDetails', ['user' => $user->id, 'card' => true, 'limit' => 20]); ?>
   </div>
   <div class="col-xxl-3 col-xl-3 col-lg-6 col-md-6 col-sm-12">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><a href="<?php echo e(route('frontend.airports.show', [$user->current_airport->id ?? ''])); ?>" class="tooltiptop" title="<?php echo app('translator')->get('sptheme.oai'); ?>"><?php echo e($user->current_airport->icao); ?></a></h4>
               <p class="mb-0"><?php echo app('translator')->get('airports.current'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-globe-simple-x"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
      <?php echo app('arrilot.async-widget')->run('DBasic::WhazzUp', ['network' => 'VATSIM', 'field_name' => $sp_settings['fieldvatsim'], 'refresh' => 300]); ?>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
      <?php echo app('arrilot.async-widget')->run('DBasic::WhazzUp', ['network' => 'IVAO', 'field_name' => $sp_settings['fieldivao'], 'refresh' => 300]); ?>
   </div>
</div>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="row">
         <div class="col">
            <?php echo app('arrilot.widget')->run('DBasic::JumpSeat'); ?>
         </div>
         <div class="col">
            <?php echo app('arrilot.widget')->run('DBasic::AirportInfo'); ?>
         </div>
      </div>
      <?php echo app('arrilot.widget')->run('DBasic::ActiveBookings', ['source' => 'bids']); ?>
      <?php echo e(Widget::latestPireps(['count' => 5])); ?>

      <?php echo e(Widget::latestNews(['count' => 5])); ?>      
   </div>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php echo app('arrilot.widget')->run('DBasic::RandomFlights', ['count' => 5]); ?>
      <?php echo app('arrilot.widget')->run('DSpecial::TourProgress', ['user' => $user->id]); ?>
      <?php echo app('arrilot.widget')->run('DSpecial::Assignments'); ?>
      <?php echo e(Widget::latestAwards(['count' => 5])); ?>

      <div class="card border mb-3">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-cloud-sun align-middle fs-20 me-1"></i><?php echo app('translator')->get('dashboard.weatherat', ['ICAO' => $current_airport]); ?></h4>
            <?php echo e(Widget::Weather(['icao' => $current_airport])); ?>

         </div>
      </div>
   </div>
</div>
<div class="row row-cols-1 row-cols-lg-3">
   <div class="col">
      <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['source' => 'pilot', 'period' => 'currentm', 'count' => 5, 'type' => 'flights']); ?>
   </div>
   <div class="col">
      <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['source' => 'pilot', 'period' => 'currentm', 'count' => 5, 'type' => 'time']); ?>
   </div>
   <div class="col">
      <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['source' => 'pilot', 'period' => 'currentm', 'count' => 5, 'type' => 'lrate_low']); ?>
   </div>
   <div class="col">
      <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['source' => 'pilot', 'period' => 'currentm', 'count' => 5, 'type' => 'distance']); ?>
   </div>
   <div class="col">
      <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['source' => 'pilot', 'period' => 'currentm', 'count' => 5, 'type' => 'score']); ?>
   </div>
   <div class="col">
      <?php echo app('arrilot.widget')->run('DBasic::LeaderBoard', ['source' => 'pilot', 'period' => 'currentm', 'count' => 5, 'type' => 'lrate_high']); ?>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php if(session('auth.password_confirmed_at') && (time() - session('auth.password_confirmed_at') <= 6)): ?>
<?php $__env->startSection('scripts'); ?>
<?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
<script>
const Toast = Swal.mixin({
   toast: true,
   position: "top-end",
   showConfirmButton: false,
   timer: 3000,
   animation: true,
   iconColor: 'white',
   customClass: {
      popup: 'colored-toast',
   },
   timerProgressBar: true,
   didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
   }
});
Toast.fire({
   icon: "success",
   title: "<?php echo app('translator')->get('sptheme.welcome'); ?>, <?php echo e($user->name); ?>!"
});
</script>
<?php $__env->stopSection(); ?>
<?php endif; ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/dashboard/index.blade.php ENDPATH**/ ?>