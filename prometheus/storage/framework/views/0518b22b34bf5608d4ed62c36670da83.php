<?php $__env->startSection('title', 'Flight Assignments'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/33.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-file-archive align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::common.fl_assignments'); ?></h4>
            <p><?php echo app('translator')->get('sptheme.assignintro'); ?></p>
            <?php if(count($assignments) === 0): ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('DSpecial::common.no_assignments'); ?></div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php if(count($assignments) > 0): ?>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php $__currentLoopData = $assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $tas): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="card border">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::common.fl_assignments'); ?> | <?php echo e(Carbon::create()->day(1)->month($group)->format('F')); ?></h4>
            <table class="table table-striped table-hover mb-0">
               <thead>
                  <tr>
                     <th class="text-center">#</th>
                     <th class="text-center"><?php echo app('translator')->get('common.airline'); ?></th>
                     <th class="text-center"><?php echo app('translator')->get('DSpecial::common.flight_no'); ?></th>
                     <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('dpt_airport_id', __('common.departure')));?> / <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('arr_airport_id', __('common.arrival')));?></th>
                     <th class="text-end"><?php echo app('translator')->get('DSpecial::common.block_time'); ?></th>
                     <th class="text-end"><?php echo app('translator')->get('common.status'); ?></th>
                  </tr>
               </thead>
               <tbody>
                  <?php $__currentLoopData = $tas->sortBy('assignment_order', SORT_NATURAL); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $as): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if($as->flight): ?>
                     <tr class="align-middle">
                        <td class="text-center"><?php echo e($as->assignment_order); ?></td>
                        <td class="text-center"><img class="<?php echo e(optional($as->flight->airline)->icao); ?>" src="<?php echo e(optional($as->flight->airline)->logo); ?>" width="90" title="<?php echo e(optional($as->flight->airline)->name); ?>" alt="<?php echo e(optional($as->flight->airline)->name); ?>"></td>
                        <td class="text-center">
                           <?php if($as->flight): ?>
                           <a href="<?php echo e(route('frontend.flights.show', [$as->flight->id])); ?>" title="<?php echo app('translator')->get('flights.flightnumber'); ?>" class="tooltiptop"><?php echo e(optional($as->flight->airline)->code.' '.optional($as->flight)->flight_number); ?></a>
                           <?php endif; ?>
                        </td>
                        <td class="text-center">
                           <a href="<?php echo e(route('frontend.airports.show', [$as->flight->dpt_airport_id])); ?>" title="<?php echo e(optional($as->flight->dpt_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-takeoff"></i> <?php echo e(optional($as->flight->dpt_airport)->name ?? $as->flight->dpt_airport_id); ?></a>
                           <i class="ph-fill ph-arrow-fat-lines-right align-text-bottom fs-20 mx-3"></i>
                           <a href="<?php echo e(route('frontend.airports.show', [$as->flight->arr_airport_id])); ?>" title="<?php echo e(optional($as->flight->arr_airport)->name); ?>" class="badge badge-rounded badge-primary tooltiptop"><i class="ph-fill ph-airplane-landing"></i> <?php echo e(optional($as->flight->arr_airport)->name ?? $as->flight->arr_airport_id); ?></a>
                        </td>
                        <td class="text-end">
                           <?php if($as->flight): ?>
                           <?php echo \App\Support\Units\Time::minutesToTimeString($as->flight->flight_time); ?> <i class="ph-fill ph-clock-countdown align-text-bottom fs-20"></i>
                           <?php endif; ?>
                        </td>
                        <td class="text-end">
                           <?php if($as->completed): ?>
                           <?php if(filled($as->pirep_id)): ?>
                           <a href="<?php echo e(route('frontend.pireps.show', [$as->pirep_id])); ?>" class="btn btn-success btn-sm"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('sptheme.completed'); ?></span></a>
                           <?php else: ?>
                           <span class="badge badge-success"><i class="ph-fill ph-check-circle"></i> <?php echo app('translator')->get('sptheme.completed'); ?></span>
                           <?php endif; ?>
                           <?php else: ?>
                           <span class="badge badge-warning"><i class="ph-fill ph-hourglass-medium"></i> <?php echo app('translator')->get('sptheme.open'); ?></span>
                           <?php endif; ?>
                        </td>
                     </tr>
                  <?php endif; ?>
               </tbody>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
         </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
   </div>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <?php echo app('arrilot.widget')->run('DBasic::Map', ['source' => 'assignment']); ?>
      <div class="card border mt-3">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::common.personal_stats'); ?></h4>
            <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($month === 'Overall'): ?>
            <table class="table table-hover table-striped align-middle text-center">
               <tr>
                  <th class="col-4"><?php echo app('translator')->get('DSpecial::common.assignments'); ?></th>
                  <th class="col-4"><?php echo app('translator')->get('DSpecial::common.completed'); ?></th>
                  <th class="col-4"><?php echo app('translator')->get('DSpecial::common.earnings'); ?><span class="small">&sup1;</span></th>
               </tr>
               <tr>
                  <td><?php echo e($stat['total']); ?></td>
                  <td><?php echo e($stat['completed']); ?></td>
                  <td><?php echo e($stat['earnings']); ?></td>
               </tr>
               <tr>
                  <td colspan="3">
                     <div class="progress" height="20px">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($stat['ratio']); ?>%;" aria-valuenow="<?php echo e($stat['ratio']); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo e($stat['ratio'].'%'); ?></div>
                     </div>
                  </td>
               </tr>
            </table>
            <?php elseif($month != 'Overall'): ?>
            <table class="table table-hover table-striped align-middle text-center">
               <tr>
                  <th class="text-start" colspan="3"><?php echo e($month); ?></th>
               </tr>
               <tr>
                  <th class="col-4"><?php echo app('translator')->get('DSpecial::common.assignments'); ?></th>
                  <th class="col-4"><?php echo app('translator')->get('DSpecial::common.completed'); ?></th>
                  <th class="col-4"><?php echo app('translator')->get('DSpecial::common.earnings'); ?><span class="small">&sup1;</span></th>
               </tr>
               <tr>
                  <td><?php echo e($stat['total']); ?></td>
                  <td><?php echo e($stat['completed']); ?></td>
                  <td><?php echo e($stat['earnings']); ?></td>
               </tr>
               <tr>
                  <td colspan="3">
                     <div class="progress" height="20px">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($stat['ratio']); ?>%;" aria-valuenow="<?php echo e($stat['ratio']); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo e($stat['ratio'].'%'); ?></div>
                     </div>
                  </td>
               </tr>
            </table>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <p class="small mb-0"><?php echo app('translator')->get('DSpecial::common.earning_note'); ?></p>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<?php if (app('laratrust')->ability('admin', 'admin-user')) : ?>
   <div class="row">
      <?php if(!$sys_check): ?>
      <div class="col-12">
         <form class="form-horizontal" method="post" action="<?php echo e(route('DSpecial.assignments_manual')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="curr_page" value="<?php echo e(url()->full()); ?>">
            <button class="btn btn-success" type="submit">Assign Monthly Flights</button>
         </form>
      </div>
      <?php else: ?>
      <div class="col-12">
         <form class="form-horizontal" method="post" action="<?php echo e(route('DSpecial.assignments_manual')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="curr_page" value="<?php echo e(url()->full()); ?>">
            <a href="javascript:void(0);" class="btn btn-warning float-start" type="submit" id="reassignMonth"><?php echo app('translator')->get('sptheme.reassign'); ?></a>
         </form>
         <form class="form-horizontal" method="post" action="<?php echo e(route('DSpecial.assignments_manual')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="curr_page" value="<?php echo e(url()->full()); ?>">
            <input type="hidden" name="resetmonth" value="true">
            <button class="btn btn-danger float-end" type="button" id="deleteMonth"><?php echo app('translator')->get('sptheme.delassign'); ?></button>
         </form>
      </div>
      <?php endif; ?>
   </div>
<?php endif; // app('laratrust')->ability ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<?php echo $__env->make('scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<script>
  $("#deleteMonth").on("click", function(event) {
    event.preventDefault();
    Swal.fire({
      toast: true,
      icon: "info",
      title: "<?php echo app('translator')->get('sptheme.delassign'); ?>",
      text: "<?php echo app('translator')->get('sptheme.delassign-t'); ?>",
      animation: true,
      position: 'top-end',
      showConfirmButton: true,
      showCancelButton: true,
      showDenyButton: false,
      confirmButtonText: "<?php echo app('translator')->get('sptheme.ok'); ?>",
      cancelButtonText: "<?php echo app('translator')->get('sptheme.cancel'); ?>",
      iconColor: 'white',
      customClass: {
        popup: 'colored-toast',
        confirmButton: 'btn btn-success mx-2',
        cancelButton: 'btn btn-warning mx-2',
      },
    }).then((result) => {
      if (result.isConfirmed) {
        $(event.target).closest('form').submit();
      }
    });
  });
  $("#reassignMonth").on("click", function(event) {
    event.preventDefault();
    Swal.fire({
      toast: true,
      icon: "info",
      title: "<?php echo app('translator')->get('sptheme.reassign'); ?>",
      text: "<?php echo app('translator')->get('sptheme.reassign-t'); ?>",
      animation: true,
      position: 'top-end',
      showConfirmButton: true,
      showCancelButton: true,
      showDenyButton: false,
      confirmButtonText: "<?php echo app('translator')->get('sptheme.ok'); ?>",
      cancelButtonText: "<?php echo app('translator')->get('sptheme.cancel'); ?>",
      iconColor: 'white',
      customClass: {
        popup: 'colored-toast',
        confirmButton: 'btn btn-success mx-2',
        cancelButton: 'btn btn-warning mx-2',
      },
    }).then((result) => {
      if (result.isConfirmed) {
        $(event.target).closest('form').submit();
      }
    });
  });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/assignments/index.blade.php ENDPATH**/ ?>