<?php
$custom_fields_exist = (isset($pirep) && $pirep->fields && $pirep->fields->isNotEmpty()) || (!isset($pirep) && $pirep_fields && $pirep_fields->isNotEmpty());
$form_width = $custom_fields_exist ? 'col-lg-8' : 'col-lg-12';
?>
<?php if(!empty($pirep) && $pirep->read_only): ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body">
            <?php $__env->startComponent('components.info'); ?>
            <?php echo app('translator')->get('pireps.fieldsreadonly'); ?>
            <?php echo $__env->renderComponent(); ?>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body">
            <div class="btn-group w-100">
               <input type="hidden" name="flight_id" value="<?php echo e(!empty($pirep) ? $pirep->flight_id : ''); ?>">
               <input type="hidden" name="sb_id" value="<?php echo e($simbrief_id); ?>">
               <?php if(isset($pirep) && !$pirep->read_only): ?>
               <button name="submit" type="submit" class="btn btn-warning" value="Delete"><?php echo app('translator')->get('pireps.deletepirep'); ?></button>
               <?php endif; ?>
               <button name="submit" type="submit" class="btn btn-info" value="Save"><?php echo app('translator')->get('pireps.savepirep'); ?></button>
               <?php if(!isset($pirep) || (filled($pirep) && !$pirep->read_only)): ?>
               <button name="submit" type="submit" class="btn btn-success" value="Submit"><?php echo app('translator')->get('pireps.submitpirep'); ?></button>
               <?php endif; ?>
            </div>
            <?php if($errors->any()): ?>
            <div class="alert alert-danger mt-3">
               <ul>
                  <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li><?php echo e($error); ?></li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </ul>
            </div>
            <?php endif; ?>
         </div>
      </div>
      <div class="row">
         <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
            <div class="card border">
               <div class="card-body">
                  <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('pireps.flightinformations'); ?></h4>
                  <div class="row">
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label class="col-5 control-label"><?php echo app('translator')->get('common.airline'); ?></label>
                              <div class="col-7">
                                 <div class="input-group input-group-lg">
                                    <?php if(!empty($pirep) && $pirep->read_only): ?>
                                    <p><?php echo e($pirep->airline->name); ?></p>
                                    <input type="hidden" name="airline_id" value="<?php echo e($pirep->airline_id); ?>">
                                    <?php else: ?>
                                    <select name="airline_id" id="airline_id" class="form-select select2">
                                       <?php $__currentLoopData = $airline_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline_id => $airline_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                       <option value="<?php echo e($airline_id); ?>" <?php if(!empty($pirep) && $airline_id===$pirep->airline_id): ?> selected <?php endif; ?>><?php echo e($airline_label); ?></option>
                                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label class="col-6 control-label"><?php echo app('translator')->get('pireps.flightident'); ?></label>
                              <div class="col-6">
                                 <?php if(!empty($pirep) && $pirep->read_only): ?>
                                 <p><?php echo e($pirep->ident); ?>

                                    <input type="hidden" name="flight_number" value="<?php echo e($pirep->flight_number); ?>" />
                                    <input type="hidden" name="flight_code" value="<?php echo e($pirep->flight_code); ?>" />
                                    <input type="hidden" name="flight_leg" value="<?php echo e($pirep->flight_leg); ?>" />
                                 </p>
                                 <?php else: ?>
                                 <div class="input-group">
                                    <input type="text" name="flight_number" id="flight_number" class="form-control" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?> value="<?php echo e(!empty($pirep) ? $pirep->flight_number : old('flight_number')); ?>" placeholder="<?php echo app('translator')->get('flights.flightnumber'); ?>">
                                    <input type="text" name="route_code" id="route_code" class="form-control" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?> value="<?php echo e(!empty($pirep) ? $pirep->route_code : old('route_code')); ?>" placeholder="<?php echo app('translator')->get('pireps.codeoptional'); ?>">
                                    <input type="text" name="route_leg" id="route_leg" class="form-control" <?php if(!empty($pirep) && $pirep->route_leg): ?> readonly <?php endif; ?> value="<?php echo e(!empty($pirep) ? $pirep->route_leg : old('route_leg')); ?>" placeholder="<?php echo app('translator')->get('pireps.legoptional'); ?>">
                                 </div>
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label class="col-5 control-label"><?php echo app('translator')->get('flights.flighttype'); ?></label>
                              <div class="col-7">
                                 <div class="input-group input-group-lg">
                                    <?php if(!empty($pirep) && $pirep->read_only): ?>
                                    <p><?php echo e($flight_type_id); ?> (<?php echo e(\App\Models\Enums\FlightType::label($pirep->flight_type)); ?>)</p>
                                    <input type="hidden" name="flight_type" value="<?php echo e($pirep->flight_type); ?>" />
                                    <?php else: ?>
                                    <select name="flight_type" id="flight_type" class="form-select select2">
                                       <?php $__currentLoopData = \App\Models\Enums\FlightType::select(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight_type_id => $flight_type_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                       <option value="<?php echo e($flight_type_id); ?>" <?php if(!empty($pirep) && $pirep->flight_type == $flight_type_id): ?> selected <?php endif; ?>><?php echo e($flight_type_id); ?> (<?php echo e($flight_type_label); ?>)</option>
                                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label class="col-5 control-label"><?php echo app('translator')->get('flights.flighttime'); ?></label>
                              <div class="col-7">
                                 <div class="input-group input-group-lg">
                                    <?php if(!empty($pirep) && $pirep->read_only): ?>
                                    <p><?php echo e($pirep->hours.' '.trans_choice('common.hour', $pirep->hours)); ?>, <?php echo e($pirep->minutes.' '.trans_choice('common.minute', $pirep->minutes)); ?>

                                       <input type="hidden" name="hours" value="<?php echo e($pirep->hours); ?>">
                                       <input type="hidden" name="minutes" value="<?php echo e($pirep->minutes); ?>">
                                    </p>
                                    <?php else: ?>
                                    <div class="input-group">
                                       <input type="number" name="hours" id="hours" class="form-control" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?> placeholder="<?php echo e(trans_choice('common.hour', 2)); ?>" min="0" value="<?php echo e(!empty($pirep) ? $pirep->hours : old('hours')); ?>">
                                       <input type="number" name="minutes" id="minutes" class="form-control" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?> placeholder="<?php echo e(trans_choice('common.minute', 2)); ?>" min="0" value="<?php echo e(!empty($pirep) ? $pirep->minutes : old('minutes')); ?>">
                                    </div>
                                    <?php endif; ?>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="distance" class="col-5 control-label"><?php echo app('translator')->get('flights.distance'); ?> (<?php echo e(config('phpvms.internal_units.distance')); ?>)</label>
                              <div class="col-7">
                                 <input type="number" name="distance" id="distance" class="form-control" min="0" step="0.01" value="<?php echo e(optional(optional($pirep)->distance)->internal(2)); ?>"
       <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?>>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="level" class="col-5 control-label"><?php echo app('translator')->get('flights.level'); ?> (<?php echo e(config('phpvms.internal_units.altitude')); ?>)</label>
                              <div class="col-7">
                                 <?php if(!empty($pirep) && $pirep->read_only): ?>
                                 <p><?php echo e($pirep->level); ?></p>
                                 <?php else: ?>
                                 <input type="number" name="level" id="level" class="form-control" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?> min="0" step="0.01" value="<?php echo e(!empty($pirep) ? $pirep->level : old('level')); ?>">
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="card border">
               <div class="card-body">
                  <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-taxiing align-middle fs-20 me-1"></i><?php echo app('translator')->get('pireps.deparrinformations'); ?></h4>
                  <div class="row">
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="dpt_airport_id" class="col-5 control-label mt-2"><?php echo app('translator')->get('airports.departure'); ?></label>
                              <div class="col-7">
                                 <?php if(!empty($pirep) && ($pirep->read_only || request()->has('flight_id'))): ?>
                                 <?php echo e($pirep->dpt_airport->name); ?>

                                 (<a href="<?php echo e(route('frontend.airports.show', ['id' => $pirep->dpt_airport->icao])); ?>"><?php echo e($pirep->dpt_airport->icao); ?></a>)
                                 <input type="hidden" name="dpt_airport_id" value="<?php echo e($pirep->dpt_airport_id); ?>">
                                 <?php else: ?>
                                 <select name="dpt_airport_id" id="dpt_airport_id" class="form-select airport_search">
                                    <?php $__currentLoopData = $airport_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dpt_airport_id => $dpt_airport_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($dpt_airport_id); ?>" <?php if(!empty($pirep) && $pirep->dpt_airport_id == $dpt_airport_id): ?> selected <?php endif; ?>><?php echo e($dpt_airport_label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                 </select>
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="arr_airport_id" class="col-5 control-label mt-2"><?php echo app('translator')->get('airports.arrival'); ?></label>
                              <div class="col-7">
                                 <?php if(!empty($pirep) && ($pirep->read_only || request()->has('flight_id'))): ?>
                                 <?php echo e($pirep->arr_airport->name); ?>

                                 (<a href="<?php echo e(route('frontend.airports.show', ['id' => $pirep->arr_airport->icao])); ?>"><?php echo e($pirep->arr_airport->icao); ?></a>)
                                 <input type="hidden" name="arr_airport_id" value="<?php echo e($pirep->arr_airport_id); ?>">
                                 <?php else: ?>
                                 <select name="arr_airport_id" id="arr_airport_id" class="form-select airport_search">
                                    <?php $__currentLoopData = $airport_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arr_airport_id => $arr_airport_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($arr_airport_id); ?>" <?php if(!empty($pirep) && $pirep->arr_airport_id == $arr_airport_id): ?> selected <?php endif; ?>><?php echo e($arr_airport_label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                 </select>
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="card border">
               <div class="card-body">
                  <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane align-middle fs-20 me-1"></i><?php echo app('translator')->get('pireps.aircraftinformations'); ?></h4>
                  <div class="row">
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="fob" class="col-5 control-label mt-1"><?php echo app('translator')->get('common.aircraft'); ?></label>
                              <div class="col-7">
                                 <?php if(!empty($pirep) && $pirep->read_only): ?>
                                 <p><?php echo e($pirep->aircraft->name); ?></p>
                                 <input type="hidden" name="aircraft_id" value="<?php echo e($pirep->aircraft_id); ?>">
                                 <?php else: ?>
                                 <select name="aircraft_id" id="aircraft_select" class="form-select select2">
                                    <?php $__currentLoopData = $aircraft_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subfleet => $sf_aircraft): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($subfleet === ''): ?>
                                    <option value=""></option>
                                    <?php else: ?>
                                    <?php $__currentLoopData = $sf_aircraft; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aircraft_id => $aircraft_label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($aircraft_id); ?>" <?php if(!empty($pirep) && $pirep->aircraft_id == $aircraft_id): ?> selected <?php endif; ?>><?php echo e($aircraft_label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                 </select>
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="fob" class="col-5 control-label mt-1"><?php echo app('translator')->get('pireps.block_fuel'); ?> (<?php echo e(setting('units.fuel')); ?>)</label>
                              <div class="col-7">
                                 <?php if(!empty($pirep) && $pirep->read_only): ?>
                                 <p><?php echo e($pirep->block_fuel); ?></p>
                                 <?php else: ?>
                                 <input type="number" name="block_fuel" id="block_fuel" class="form-control" min="0" step="0.01" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?> value="<?php echo e(!empty($pirep) ? $pirep->block_fuel : old('block_fuel')); ?>">
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="fob" class="col-5 control-label mt-1"><?php echo app('translator')->get('pireps.fuel_used'); ?> (<?php echo e(setting('units.fuel')); ?>)</label>
                              <div class="col-7">
                                 <?php if(!empty($pirep) && $pirep->read_only): ?>
                                 <p><?php echo e($pirep->fuel_used); ?></p>
                                 <?php else: ?>
                                 <input type="number" name="fuel_used" id="fuel_used" class="form-control" min="0" step="0.01" <?php if(!empty($pirep) && $pirep->read_only): ?> readonly <?php endif; ?> value="<?php echo e(!empty($pirep) ? $pirep->fuel_used : old('fuel_used')); ?>">
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <div id="fares_container">
               <?php echo $__env->make('pireps.fares', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            <div class="card border">
               <div class="card-body">
                  <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-line-segments align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.route'); ?> & <?php echo e(trans_choice('common.remark', 2)); ?></h4>
                  <div class="row">
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="route" class="col-4 control-label mt-1"><?php echo app('translator')->get('flights.route'); ?></label>
                              <div class="col-8">
                                 <textarea name="route" id="route" placeholder="<?php echo app('translator')->get('flights.route'); ?>" class="form-control"><?php if(!empty($pirep)): ?><?php echo e($pirep->route); ?><?php else: ?><?php echo e(old('route')); ?><?php endif; ?></textarea>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="row">
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label for="notes" class="col-4 control-label mt-1"><?php echo e(trans_choice('common.remark', 1)); ?></label>
                              <div class="col-8">
                                 <textarea name="notes" id="notes" placeholder="<?php echo e(trans_choice('common.note', 2)); ?>" class="form-control"><?php if(!empty($pirep)): ?><?php echo e($pirep->notes); ?><?php else: ?><?php echo e(old('notes')); ?><?php endif; ?></textarea>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <?php if($custom_fields_exist): ?>
            <div class="card border">
               <div class="card-body">
                  <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-note-pencil align-middle fs-20 me-1"></i><?php echo e(trans_choice('common.field', 2)); ?></h4>
                  <div class="row">
                     <div class="col">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <div class="col-8">
                                 <table class="table table-striped">
                                    <?php if(isset($pirep) && $pirep->fields): ?>
                                    <?php echo $__env->renderEach('pireps.custom_fields', $pirep->fields, 'field'); ?>
                                    <?php else: ?>
                                    <?php echo $__env->renderEach('pireps.custom_fields', $pirep_fields, 'field'); ?>
                                    <?php endif; ?>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/fields.blade.php ENDPATH**/ ?>