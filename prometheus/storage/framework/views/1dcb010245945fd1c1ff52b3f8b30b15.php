<?php $__env->startSection('title', 'My Sceneries'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/30.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-trolley fs-20 me-1"></i>My Sceneries
               <?php if($sceneries->count() > 0): ?>
               <span class="float-end">
                  <?php echo app('arrilot.widget')->run('DBasic::Map', ['source' => 'scenery']); ?>
               </span>
               <?php endif; ?>
            </h4>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e($flights['deps']); ?></h4>
               <?php if(!setting('pilots.only_flights_from_current')): ?>
               <p class="mb-0"><a href="<?php echo e(route('DBasic.scenery.flights', ['type' => 'departures'])); ?>" class="tooltiptop" title="Search departure flights"><i class="ph-fill ph-magnifying-glass fs-18 me-1"></i>Departures from my sceneries</a></p>
               <?php else: ?>
               <p class="mb-0">Departures from my sceneries</p>
               <?php endif; ?>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-airplane-takeoff"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="col">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e($flights['arrs']); ?></h4>
               <p class="mb-0"><a href="<?php echo e(route('DBasic.scenery.flights', ['type' => 'arrivals'])); ?>" class="tooltiptop" title="Search arrival flights"><i class="ph-fill ph-magnifying-glass fs-18 me-1"></i>Arrivals from my sceneries</a></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-airplane-landing"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <div class="col">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold"><?php echo e($flights['trip']); ?></h4>
               <p class="mb-0"><a href="<?php echo e(route('DBasic.scenery.flights', ['type' => 'trips'])); ?>" class="tooltiptop" title="Search trip flights"><i class="ph-fill ph-magnifying-glass fs-18 me-1"></i>Trips between my sceneries</a></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-repeat"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-6 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-globe-simple align-middle fs-20 me-1"></i>My personal sceneries
               <span class="float-end small fw-normal"><?php echo app('translator')->get('DBasic::common.paginate', ['first' => $sceneries->firstItem(), 'last' => $sceneries->lastItem(), 'total' => $sceneries->total()]); ?>
            </h4>
            <?php if($sceneries->count() > 0): ?>
            <?php echo $__env->make('DBasic::scenery.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php else: ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>There are no sceneries.</div>
            <?php endif; ?>
         </div>
         <?php if($sceneries->count() > 0): ?>
         <div class="card-footer text-center">
            <?php echo e($sceneries->withQueryString()->links('pagination.default')); ?>

         </div>
         <?php endif; ?>
      </div>
   </div>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-6 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-table align-middle fs-20 me-1"></i>Add airport scenery</h4>
            <form action="<?php echo e(route('DBasic.scenery.store')); ?>" method="POST">
               <?php echo csrf_field(); ?>

               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label for="airport_id" class="col-5 control-label mt-1"><i class="ph-fill ph-air-traffic-control align-middle fs-20 me-1"></i>Airport ICAO Code</label>
                     <div class="col-7">
                        <input type="text" name="airport_id" id="formairport" class="form-control" pattern="[A-Za-z]{4}">
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-globe-simple-x align-middle fs-20 me-1"></i>Airport Region</label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="region" id="formregion" class="form-select select2 <?php $__errorArgs = ['airline_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                              <?php $__currentLoopData = $regions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-grid-four align-middle fs-20 me-1"></i>Simulator</label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="simulator" id="formsimulator" class="form-select select2 <?php $__errorArgs = ['airline_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                              <?php $__currentLoopData = $simulators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($key); ?>"><?php echo e($value); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label for="notes" class="col-5 control-label mt-1"><i class="ph-fill ph-note align-middle fs-20 me-1"></i>Notes</label>
                     <div class="col-7">
                        <input type="text" name="notes" id="formnotes" class="form-control">
                     </div>
                  </div>
               </div>
               <input name="user_id" type="hidden" value="<?php echo e($user_id); ?>">
               <button type="submit" class="btn btn-success float-end">Save Scenery</button>
            </form>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/scenery/index.blade.php ENDPATH**/ ?>