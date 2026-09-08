<?php $__env->startSection('title', 'Airline Transfer'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/11.jpg')); ?>" class="img-fluid card-img-top rounded rounded" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
         </div>
      </div>
   </div>
</div>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-3 header-title border-bottom"><i class="ph-fill ph-list-magnifying-glass fs-20 me-1"></i><?php echo app('translator')->get('SPTransfer::common.title-a'); ?></h4>
            <?php echo $__env->make('flash::message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php if($state === 0): ?>
            <h5 class="my-3"><?php echo app('translator')->get('SPTransfer::common.reqform'); ?></h5>
            <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('SPTransfer::common.reqis'); ?> <?php echo e($status); ?>.</div>
            <?php else: ?>
            <?php if($limit): ?>
            <div class="alert alert-info" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('SPTransfer::common.limited-a'); ?> <?php echo e($daysLimit); ?> <?php echo app('translator')->get('SPTransfer::common.days'); ?>.</div>
            <?php else: ?>
            <form method="post" action="<?php echo e(route('sptransfer.airline.store')); ?>" class="form-horizontal">
               <?php echo csrf_field(); ?>
               <h5 class="my-3"><?php echo app('translator')->get('SPTransfer::common.based-a'); ?> <?php echo e($current_airline_name); ?> (<?php echo e(strtoupper($current_airline)); ?>)</h5>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-airplane-takeoff align-middle fs-20 me-1"></i><?php echo app('translator')->get('SPTransfer::common.desired-a'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="airline_request_id" id="airline_request_id" class="form-select select2" placeholder="<?php echo app('translator')->get('SPTransfer::common.selectplace'); ?>" required>
                            <?php $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline_request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option value="<?php echo e($airline_request->id); ?>"><?php echo e($airline_request->icao); ?> - <?php echo e($airline_request->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>   
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label for="email" class="col-5 control-label mt-3"><i class="ph-fill ph-text-indent align-middle fs-20 me-1"></i><?php echo app('translator')->get('SPTransfer::common.reason-a'); ?></label>
                     <div class="col-7">
                        <textarea name="reason" id="reason" class="form-control bg-white border" maxlength="100" placeholder="<?php echo app('translator')->get('SPTransfer::common.chars'); ?>" required></textarea>
                     </div>
                  </div>
               </div>
               <?php if($spfinance): ?>
               <?php if($charge_type === 0): ?>
               <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('SPTransfer::common.charged'); ?> <?php echo e($spvalue); ?> <?php echo app('translator')->get('SPTransfer::common.onrequest'); ?>.</div>
               <?php else: ?>
               <div class="alert alert-warning" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('SPTransfer::common.charged'); ?> <?php echo e($spvalue); ?> <?php echo app('translator')->get('SPTransfer::common.ontransfer'); ?>.</div>
               <?php endif; ?>
               <?php endif; ?>
               <button type="submit" class="btn btn-primary"><?php echo app('translator')->get('SPTransfer::common.request'); ?></button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 mb-3 header-title border-bottom"><i class="ph-fill ph-info fs-20 me-1"></i><?php echo app('translator')->get('SPTransfer::common.lasttitle'); ?></h4>
            <?php if(empty($lasttransfer)): ?>
            <div class="alert alert-info" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('SPTransfer::common.didnot'); ?></div>
            <?php else: ?>
            <div class="table-responsive">
               <table class="table table-striped table-hover mb-0">
                  <tbody>
                     <tr>
                        <td class="fw-bold"><?php echo app('translator')->get('SPTransfer::common.transferid'); ?></td>
                        <td><?php echo e($lasttransfer->id); ?></td>
                     </tr>
                     <tr>
                        <td class="fw-bold"><?php echo app('translator')->get('SPTransfer::common.reqhub-a'); ?></td>
                        <td><?php echo e($request_airline->icao); ?> - <?php echo e($request_airline->name); ?></td>
                     </tr>
                     <tr>
                        <td class="fw-bold"><?php echo app('translator')->get('SPTransfer::common.reqdate'); ?></td>
                        <td><?php echo e($lasttransfer->created_at->format('d. F Y - H:i')); ?> UTC</td>
                     </tr>
                     <tr>
                        <td class="fw-bold"><?php echo app('translator')->get('SPTransfer::common.reqstatus'); ?></td>
                        <td><?php echo e($status); ?><?php if($status == 'Rejected'): ?>: <?php echo e($lasttransfer->reject_reason ?? '-'); ?> <?php endif; ?> </td>
                     </tr>
                     <tr>
                        <td class="fw-bold"><?php echo app('translator')->get('SPTransfer::common.reqreason'); ?></td>
                        <td><?php echo e($lasttransfer->reason); ?></td>
                     </tr>
                  </tbody>
               </table>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('sptransfer::layouts.frontend', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/SPTransfer/Providers/../Resources/views/airline.blade.php ENDPATH**/ ?>