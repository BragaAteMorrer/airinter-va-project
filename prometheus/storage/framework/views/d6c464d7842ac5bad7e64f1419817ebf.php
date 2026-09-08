<?php $__env->startSection('title', 'Tours'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/39.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-bounding-box align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.tours'); ?></h4>
            <?php if(!$tours->count()): ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.notours'); ?></div>
            <?php else: ?>
            <form method="GET" action="<?php echo e(route('DSpecial.tours')); ?>">
               <div class="form-group form-bg-grey rounded mb-0">
                  <div class="row">
                     <label class="col-2 control-label mt-1"><i class="ph-fill ph-airplane-tilt align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.subfleet'); ?></label>
                     <div class="col-8">
                        <div class="input-group input-group-lg">
                           <select name="sfid" id="sfid" class="form-select select2">
                              <option value=""><?php echo app('translator')->get('sptheme.plchoose'); ?>...</option>
                              <?php $__currentLoopData = $tour_subfleets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($sf->id); ?>" <?php if($sf->id == @request()->input('sfid')): ?> selected <?php endif; ?>><?php echo e($sf->name.' | '.optional($sf->airline)->code); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                     <div class="col-2 text-end">
                        <button class="btn btn-success" type="submit"><?php echo app('translator')->get('flights.search'); ?></button>
                     </div>
                  </div>
               </div>
            </form>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php if($tours->count()): ?>
<div class="tab-default">
   <div role="tabpanel">
      <div class="card border">
         <div class="card-body">
            <ul class="nav nav-tabs nav-justified mb-0" role="tablist">
               <li class="nav-item" role="presentation">
                  <a class="nav-link active" data-bs-toggle="tab" href="#current" role="tab" aria-selected="true">
                     <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.current'); ?>
                  </a>
               </li>
               <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#future" role="tab" aria-selected="false" tabindex="-1">
                     <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.future'); ?>
                  </a>
               </li>
               <li class="nav-item" role="presentation">
                  <a class="nav-link" data-bs-toggle="tab" href="#past" role="tab" aria-selected="false" tabindex="-1">
                     <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.past'); ?>
                  </a>
               </li>
               <li class="nav-item" role="presentation">
                  <a class="nav-link me-0" data-bs-toggle="tab" href="#trules" role="tab" aria-selected="false" tabindex="-1">
                     <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.trules'); ?>
                  </a>
               </li>
            </ul>
         </div>
      </div>
      <div class="tab-content text-muted">
         <div class="tab-pane active show" id="current" role="tabpanel">
            <div class="row row-cols-lg-3" id="activet">
               <?php if($tours->count()): ?>
                  <?php $__currentLoopData = $tours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <?php if($carbon_now >= $tour->start_date && $carbon_now <= $tour->end_date): ?>
                        <?php echo $__env->make('DSpecial::tours.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               <?php else: ?>
                  <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
                     <div class="card border mb-0">
                        <div class="card-body">
                           <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.current'); ?></h4>
                           <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.notours'); ?></div>
                        </div>
                     </div>
                  </div>
               <?php endif; ?>
            </div>
         </div>
         <div class="tab-pane" id="future" role="tabpanel">
            <div class="row row-cols-lg-3" id="futuret">
               <?php if($carbon_now > $tour->start_date): ?>
                  <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
                     <div class="card border mb-0">
                        <div class="card-body">
                           <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.future'); ?></h4>
                           <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.notours'); ?></div>
                        </div>
                     </div>
                  </div>
               <?php else: ?>
                  <?php $__currentLoopData = $tours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <?php if($carbon_now < $tour->start_date): ?>
                        <?php echo $__env->make('DSpecial::tours.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               <?php endif; ?>
            </div>
         </div>
         <div class="tab-pane" id="past" role="tabpanel">
            <div class="row row-cols-lg-3" id="closedt">
               <?php if($carbon_now < $tour->end_date): ?>
                  <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
                     <div class="card border mb-0">
                        <div class="card-body">
                           <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.past'); ?></h4>
                           <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.notours'); ?></div>
                        </div>
                     </div>
                  </div>
               <?php else: ?>
                  <?php $__currentLoopData = $tours; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tour): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <?php if($carbon_now > $tour->end_date): ?>
                        <?php echo $__env->make('DSpecial::tours.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               <?php endif; ?>
            </div>
         </div>
         <div class="tab-pane" id="trules" role="tabpanel">
            <div class="row row-cols-lg-3" id="rulest">
               <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
                  <div class="card border mb-0">
                     <div class="card-body">
                        <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.tourrules'); ?></h4>
                        <ul class="list-styled">
                           <li class="list-item mb-3">Tours can be flown and reported either manually or with ACARS support. For ACARS-supported tour flights, pilots can either bid/load a flight from the list or manually enter the required information in the New Flight window of our ACARS software. When submitting a manual PIREP or entering flight details manually in ACARS, please ensure that you include the correct route code and leg number in your reports. Omitting this information may cause issues with route leg validation and award eligibility.</li>
                           <li class="list-item mb-3">Open Tours can be flown with any airline and aircraft of the pilot’s choice—there are no airline or aircraft restrictions. In contrast, Airline Tours must be flown using the correct airline callsign and, if specified, the assigned subfleet for each leg.</li>
                           <li class="list-item mb-3">To qualify for awards, all tour legs must be completed within the designated validity period.</li>
                           <li class="list-item mb-3">To view a tour’s details and legs, simply click on the tour name.</li>
                           <li class="list-item">Safe flights!</li>
                        </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/tours/index.blade.php ENDPATH**/ ?>