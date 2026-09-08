<?php $__env->startSection('title', 'Missions'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/39.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-siren align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.missions'); ?>
               <span class="float-end fw-normal small"><?php echo app('translator')->get('sptheme.availmission'); ?>: <?php echo e(count($my_missions)); ?></span>
            </h4>
            <p><?php echo app('translator')->get('sptheme.missionstext'); ?></p>
            <?php if($my_missions->count() > 0): ?>
            <div class="table-responsive">
               <table class="table table-striped table-hover mb-0">
                  <thead>
                     <tr>
                        <th></th>
                        <th></th>
                        <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('aircraft.registration', __('common.aircraft')));?></th>
                        <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', __('flights.flightnumber')));?></th>
                        <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
                        <th class="text-center">STD</th>
                        <th class="text-center">STA</th>
                        <th class="text-end"><?php echo app('translator')->get('DBasic::common.expire'); ?></th>
                        <th></th>
                        <th></th>
                     </tr>
                     <?php $__currentLoopData = $my_missions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $miss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <tr class="align-middle">
                        <td><?php echo e($miss->mission_order); ?></td>
                        <td><img src="<?php echo e(optional($miss->aircraft->airline)->logo); ?>" width="90" alt="<?php echo e(optional($miss->aircraft->airline)->name); ?>"></td>
                        <td><span class="tooltiptop" title="<?php echo e($miss->aircraft->name); ?>"><?php echo e($miss->aircraft->registration); ?> (<?php echo e($miss->aircraft->icao); ?>)</span></td>
                        <td>
                           <?php if($miss->flight): ?>
                           <?php echo e($miss->flight->airline->code.' '.$miss->flight->flight_number); ?>

                           <?php else: ?>
                           <?php echo e('-'); ?>

                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <a href="<?php echo e(route('frontend.airports.show', [$miss->dpt_airport_id])); ?>" title="<?php echo e(optional($miss->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($miss->dpt_airport_id); ?></a>
                           <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                           <a href="<?php echo e(route('frontend.airports.show', [$miss->arr_airport_id])); ?>" title="<?php echo e(optional($miss->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($miss->arr_airport_id); ?></a>
                        </td>
                        <td class="text-center">
                           <?php if($miss->flight): ?>
                           <?php echo e(DS_FormatScheduleTime($miss->flight->dpt_time)); ?>

                           <?php else: ?>
                           <?php echo e('-'); ?>

                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <?php if($miss->flight): ?>
                           <?php echo e(DS_FormatScheduleTime($miss->flight->arr_time)); ?>

                           <?php else: ?>
                           <?php echo e('-'); ?>

                           <?php endif; ?>
                        </td>
                        <td class="text-end"><?php echo e($miss->mission_valid->format('d. F Y - H:i')); ?></td>
                        <td class="text-end">
                           <?php if($miss->flight): ?>
                           <a href="<?php echo e(route('frontend.flights.show', [$miss->flight->id])); ?>" class="btn btn-success">Flight Details</a>
                           <?php else: ?>
                           <a href="<?php echo e(route('DSpecial.freeflight')); ?>" class="btn btn-success"><?php echo app('translator')->get('sptheme.freeflight'); ?></a>
                           <?php endif; ?>
                        </td>
                        <td class="text-end">
                           <form action=<?php echo e(route('DSpecial.missions.store')); ?> method="POST">
                              <?php echo csrf_field(); ?>
                              <input type="hidden" name="remove_id" value="<?php echo e($miss->id); ?>">
                              <input type="hidden" name="action" value="remove">
                              <button type="submit" class="btn btn-danger"><?php echo app('translator')->get('sptheme.remmission'); ?></button>
                           </form>
                        </td>
                     </tr>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.persnomissions'); ?></div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-screwdriver align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.repositionmaint'); ?>
               <span class="float-end fw-normal small"><?php echo app('translator')->get('sptheme.availmission'); ?>: <?php echo e(count($mt_missions)); ?></span>
            </h4>
            <?php if($mt_missions): ?>
            <div class="table-responsive">
               <table class="table table-striped table-hover mb-0">
                  <thead>
                     <th></th>
                     <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('aircraft.registration', __('common.aircraft')));?></th>
                     <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', __('flights.flightnumber')));?></th>
                     <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
                     <th class="text-end"><?php echo app('translator')->get('DBasic::common.expire'); ?></th>
                     <th></th>
                  </thead>
                  <tbody>
                     <?php $__currentLoopData = $mt_missions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $miss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <tr class="align-middle">
                        <td><img src="<?php echo e(optional($miss['ac']->airline)->logo); ?>" width="90" alt="<?php echo e(optional($miss['ac']->airline)->name); ?>"></td>
                        <td><span class="tooltiptop" title="<?php echo e($miss['ac']->name); ?>"><?php echo e($miss['ac']->registration); ?> (<?php echo e($miss['ac']->icao); ?>)</span></td>
                        <td>
                           <?php if(filled($miss['flt'])): ?>
                           <?php echo e($miss['flt']->airline->code.' '.$miss['flt']->flight_number); ?>

                           <?php else: ?>
                           <?php echo e('-'); ?>

                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <a href="<?php echo e(route('frontend.airports.show', [$miss['dep']->id])); ?>" title="<?php echo e($miss['dep']->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($miss['dep']->id); ?></a>
                           <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                           <a href="<?php echo e(route('frontend.airports.show', [$miss['arr']->id])); ?>" title="<?php echo e($miss['arr']->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($miss['arr']->id); ?></a>
                        </td>
                        <td class="text-end"><?php echo e($miss['end']->diffForHumans()); ?></td>
                        <td class="text-end">
                           <form action=<?php echo e(route('DSpecial.missions.store')); ?> method="POST">
                              <?php echo csrf_field(); ?>
                              <input type="hidden" name="aircraft_id" value="<?php echo e($miss['ac']->id); ?>">
                              <input type="hidden" name="flight_id" value="<?php echo e(optional($miss['flt'])->id); ?>">
                              <input type="hidden" name="dpt_airport_id" value="<?php echo e($miss['dep']->id); ?>">
                              <input type="hidden" name="arr_airport_id" value="<?php echo e($miss['arr']->id); ?>">
                              <input type="hidden" name="mission_type" value="2">
                              <input type="hidden" name="mission_valid" value="<?php echo e($miss['end']); ?>">
                              <button type="submit" class="btn btn-success"><?php echo app('translator')->get('sptheme.acceptmission'); ?></button>
                           </form>
                        </td>
                     </tr>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tbody>
               </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.nomissions'); ?></div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-in-flight align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.repositionparked'); ?>
               <span class="float-end fw-normal small"><?php echo app('translator')->get('sptheme.availmission'); ?>: <?php echo e(count($sc_missions)); ?></span>
            </h4>
            <?php if($sc_missions): ?>
            <div class="table-responsive">
               <table class="table table-striped table-hover mb-0">
                  <thead>
                     <tr>
                        <th></th>
                        <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('aircraft.registration', __('common.aircraft')));?></th>
                        <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', __('flights.flightnumber')));?></th>
                        <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
                        <th class="text-end"><?php echo app('translator')->get('DBasic::common.expire'); ?></th>
                        <th></th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php $__currentLoopData = $sc_missions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $miss): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <tr class="align-middle">
                        <td><img src="<?php echo e(optional($miss['ac']->airline)->logo); ?>" width="90" alt="<?php echo e(optional($miss['ac']->airline)->name); ?>"></td>
                        <td><span class="tooltiptop" title="<?php echo e($miss['ac']->name); ?>"><?php echo e($miss['ac']->registration); ?> (<?php echo e($miss['ac']->icao); ?>)</span></td>
                        <td>
                           <?php if(filled($miss['flt'])): ?>
                           <?php echo e($miss['flt']->airline->code.' '.$miss['flt']->flight_number); ?>

                           <?php else: ?>
                           <?php echo e('-'); ?>

                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <a href="<?php echo e(route('frontend.airports.show', [$miss['dep']->id])); ?>" title="<?php echo e($miss['dep']->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e($miss['dep']->id); ?></a>
                           <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                           <a href="<?php echo e(route('frontend.airports.show', [$miss['arr']->id])); ?>" title="<?php echo e($miss['arr']->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e($miss['arr']->id); ?></a>
                        </td>
                        <td class="text-end"><?php echo e($miss['end']->diffForHumans()); ?></td>
                        <td class="text-end">
                           <form action=<?php echo e(route('DSpecial.missions.store')); ?> method="POST">
                              <?php echo csrf_field(); ?>
                              <input type="hidden" name="aircraft_id" value="<?php echo e($miss['ac']->id); ?>">
                              <input type="hidden" name="flight_id" value="<?php echo e(optional($miss['flt'])->id); ?>">
                              <input type="hidden" name="dpt_airport_id" value="<?php echo e($miss['dep']->id); ?>">
                              <input type="hidden" name="arr_airport_id" value="<?php echo e($miss['arr']->id); ?>">
                              <input type="hidden" name="mission_type" value="1">
                              <input type="hidden" name="mission_valid" value="<?php echo e($miss['end']); ?>">
                              <button type="submit" class="btn btn-success"><?php echo app('translator')->get('sptheme.acceptmission'); ?></button>
                           </form>
                        </td>
                     </tr>
                     <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </tbody>
               </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.nomissions'); ?></div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/missions/index.blade.php ENDPATH**/ ?>