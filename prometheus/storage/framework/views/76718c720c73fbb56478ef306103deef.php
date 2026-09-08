<form method="get" action="<?php echo e(route('frontend.flights.search')); ?>" class="form-horizontal">
   <?php echo csrf_field(); ?>
   <div class="card border mb-0">
      <div class="card-body">
         <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-magnifying-glass fs-20 me-1"></i><?php echo app('translator')->get('sptheme.flightcriteria'); ?></h4>
         <div class="row">
            <div class="col-xxl-6 col-xl-6 col-lg-12 col-md-12 col-sm-12 mt-3">
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-building align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.airline'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="airline_id" id="airline_id" class="form-select select2">
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
                     <label for="flight_number" class="col-5 control-label"><i class="ph-fill ph-list-numbers align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.flightnumber'); ?></label>
                     <div class="col-7">
                        <input type="text" name="flight_number" id="flight_number" class="form-control form-control-sm border">
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-airplane-takeoff align-middle fs-20 me-1"></i><?php echo app('translator')->get('airports.departure'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="dep_icao" id="dep_icao" class="form-select airport_search" placeholder="<?php echo app('translator')->get('sptheme.typetosearch'); ?>"></select>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-airplane align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.subfleet'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="subfleet_id" id="subfleet_id" class="form-select select2">
                              <?php $__currentLoopData = $subfleets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subfleet_id => $subfleet_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($subfleet_id); ?>" <?php if(request()->get('subfleet_id') == $subfleet_id): ?> selected <?php endif; ?>><?php echo e($subfleet_label); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-12 col-md-12 col-sm-12 mt-3">
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-text-t align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.flighttype'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="flight_type" id="flight_type" class="form-select select2">
                              <?php $__currentLoopData = $flight_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight_type_id => $flight_type_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($flight_type_id); ?>" <?php if(request()->get('flight_type') == $flight_type_id): ?> selected <?php endif; ?>><?php echo e($flight_type_label); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label for="input-placeholder" class="col-5 control-label"><i class="ph-fill ph-hash align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.code'); ?></label>
                     <div class="col-7">
                        <input type="text" name="route_code" id="route_code" class="form-control form-control-sm border">
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i><?php echo app('translator')->get('airports.arrival'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="arr_icao" id="arr_icao" class="form-select airport_search" placeholder="<?php echo app('translator')->get('sptheme.typetosearch'); ?>"></select>
                        </div>
                     </div>
                  </div>
               </div>
               <?php if(filled($icao_codes)): ?>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-airplane-tilt align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.icaotype'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="icao_type" id="icao_type" class="form-select select2">
                              <option value=""></option>
                              <?php $__currentLoopData = $icao_codes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $icao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($icao); ?>" <?php if(request()->get('icao_type') == $icao): ?> selected <?php endif; ?>><?php echo e($icao); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endif; ?>
            </div>
            <?php if(filled($type_ratings)): ?>
            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-4 control-label"><i class="ph-fill ph-airplane-taxiing align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.typerating'); ?></label>
                     <div class="col-8">
                        <div class="input-group input-group-lg">
                           <select name="type_rating_id" id="type_rating_id" class="form-select select2">
                              <option value=""></option>
                              <?php $__currentLoopData = $type_ratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($tr->id); ?>" <?php if(request()->get('type_rating_id') == $tr->id): ?> selected <?php endif; ?>><?php echo e($tr->type.' | '.$tr->name); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <?php endif; ?>
         </div>
         <div class="card-footer p-0">
            <div class="d-flex justify-content-between p-3">
               <button type="submit" class="btn btn-success"><?php echo app('translator')->get('common.find'); ?></button>
               <a href="<?php echo e(route('frontend.flights.index')); ?>" title="<?php echo app('translator')->get('common.reset'); ?>" class="btn btn-warning"><?php echo app('translator')->get('common.reset'); ?></a>
            </div>
         </div>
      </div>
   </div>
</form><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flights/search.blade.php ENDPATH**/ ?>