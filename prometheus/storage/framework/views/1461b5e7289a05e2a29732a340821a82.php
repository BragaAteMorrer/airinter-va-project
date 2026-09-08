<?php $__env->startSection('title', trans_choice('common.flight', 2)); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/12.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <span class="badge text-bg-light badge-sm float-end"><i class="ph-fill ph-binoculars align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.choose_flights', ['count' => $flights->total()]); ?></span>
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-airplane-tilt fs-20 me-1"></i><?php echo e(trans_choice('common.flight', 2)); ?></h4>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-9 col-xl-9 col-lg-8 col-md-12 col-sm-12 mb-3">
      <?php echo $__env->make('flights.search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
   </div>
   <div class="col-xxl-3 col-xl-3 col-lg-4 col-md-12 col-sm-12 mb-3">
      <div class="card h-100 border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-info fs-20 me-1"></i><?php echo app('translator')->get('pireps.flightinformations'); ?></h4>
            <div class="d-grid flex-wrap align-items-center gap-2 my-4">
               <a href="<?php echo e(route('frontend.flights.bids')); ?>" class="btn btn-lg btn-secondary" title="<?php echo app('translator')->get('sptheme.flights-t'); ?>"><?php echo app('translator')->get('flights.mybid'); ?></a>
            </div>
            <p class="card-footer rounded h3 text-center p-4">
               <img src="<?php echo e(public_asset('/SPTheme/images/navigraph-sm.png')); ?>" alt="Navigraph"> Navigraph data
            <div class="d-grid flex-wrap align-items-center gap-2 my-4">
               <a href="https://navigraph.com" title="Navigraph" target="_blank" class="btn btn-lg btn-primary">Cycle <?php echo e(date('ym')); ?></a>
            </div>
            </p>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-3 header-title border-bottom"><i class="ph-fill ph-pencil-simple-line fs-20 me-1"></i><?php echo app('translator')->get('sptheme.readybooking'); ?></h4>
            <table class="table align-middle text-nowrap mb-2">
               <tr class="text-center">
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('airline_id', __('common.airline')));?></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_number', __('flights.flightnumber')));?></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('airports.departure')));?></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('airports.arrival')));?></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_time', 'STD'));?></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_time', 'STA'));?></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('distance', 'Distance'));?></th>
                  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_time', 'Flight Time'));?></th>
               </tr>
            </table>
            <?php echo $__env->make('flights.table', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
         </div>
         <div class="card-footer">
            <?php echo e($flights->withQueryString()->links('pagination.bootstrap-5')); ?>

         </div>
      </div>
   </div>
</div>
<?php if(setting('bids.block_aircraft', false)): ?>
<?php echo $__env->make('flights.bids_aircraft', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('flights.scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flights/index.blade.php ENDPATH**/ ?>