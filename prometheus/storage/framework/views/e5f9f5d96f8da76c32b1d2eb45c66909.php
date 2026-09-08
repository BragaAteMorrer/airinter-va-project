<?php $__env->startSection('title', __('DBasic::common.ranks')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
      <div class="card border mb-3">
         <div class="card-body table-responsive">
            <h4 class="mt-0 header-title border-bottom">
               <i class="ph-fill ph-medal align-middle fs-20 me-1"></i><?php echo e(config('app.name')); ?> | <?php echo app('translator')->get('DBasic::common.ranks'); ?>
            </h4>
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/27.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <table class="table table-striped table-hover mb-0">
               <thead>
                  <tr>
                     <th></th>
                     <th class="text-center"><?php echo app('translator')->get('DBasic::common.pay_acars'); ?></th>
                     <th class="text-center"><?php echo app('translator')->get('DBasic::common.pay_manual'); ?></th>
                     <th class="text-center">ACARS approval</th>
                     <th class="text-center">MANUAL approval</th>
                     <th class="text-center"><?php echo app('translator')->get('DBasic::common.restrict'); ?></th>
                  </tr>
               </thead>
               <tbody>
                  <?php $__currentLoopData = $ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <tr class="align-middle">
                     <td class="text-start">
                        <div class="d-flex align-items-center">
                           <div class="me-3">
                              <?php if($rank->image_url): ?>
                              <img src="<?php echo e($rank->image_url); ?>" width="45" alt="<?php echo e($rank->name); ?>">
                              <?php endif; ?>
                           </div>
                           <div class="d-flex justify-content-start flex-column">
                              <h6 class="mb-2 card-title"><?php echo e($rank->name); ?></h6>
                              <span class="text-muted d-block fs-7">Minimum Hours: <?php echo e(number_format($rank->hours)); ?></span>
                           </div>
                        </div>
                     </td>
                     <td class="text-center"><?php echo e(number_format($rank->acars_base_pay_rate).' '.$currency); ?></td>
                     <td class="text-center"><?php echo e(number_format($rank->manual_base_pay_rate).' '.$currency); ?></td>
                     <td class="text-center">
                        <?php if($rank->auto_approve_acars == 1 ): ?>
                        <span class="badge badge-success tooltiptop" title="PIREPS submitted through ACARS are automatically accepted"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('common.yes'); ?></span>
                        <?php else: ?>
                        <span class="badge badge-danger tooltiptop" title="PIREPS submitted through ACARS are not automatically accepted"><i class="ph-fill ph-x-circle"></i> <?php echo app('translator')->get('common.no'); ?></span>
                        <?php endif; ?>
                     </td>
                     <td class="text-center">
                        <?php if($rank->auto_approve_manual == 1): ?>
                        <span class="badge badge-success tooltiptop" title="PIREPS submitted manually are automatically accepted"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('common.yes'); ?></span>
                        <?php else: ?>
                        <span class="badge badge-danger tooltiptop" title="PIREPS submitted manually are not automatically accepted"><i class="ph-fill ph-x-circle"></i> <?php echo app('translator')->get('common.no'); ?></span>
                        <?php endif; ?>
                     </td>
                     <td class="text-center">
                        <?php if($rank->subfleets->count() > 0): ?>
                        <?php echo app('translator')->get('DBasic::common.allowed_sf'); ?> <?php echo e($rank->subfleets->count()); ?> <i class="ph-fill ph-caret-double-down fs-18 text-danger tooltiptop" type="button" data-toggle="collapse" aria-expanded="false" data-target="#sfr_<?php echo e($rank->id); ?>" aria-controls="sfr_<?php echo e($rank->id); ?>" title="<?php echo app('translator')->get('DBasic::common.allowed_sh'); ?>"></i>
                        <?php else: ?>
                        <?php echo app('translator')->get('DBasic::common.restrict_no'); ?> <i class="ph-fill ph-check-fat fs-18 text-success"></i> 
                        <?php endif; ?>
                     </td>
                  </tr>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</div>
<div class="row row-cols-lg-3">
   <?php $__currentLoopData = $ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div id="sfr_<?php echo e($rank->id); ?>" class="collapse">
      <?php if($rank->subfleets->count() > 0): ?>
      <div class="col-lg">
         <div class="card border mb-0">
            <div class="card-body">
               <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-medal align-middle fs-20 me-1"></i><?php echo e($rank->name); ?>

                  <span class="fw-normal small float-end"><?php echo app('translator')->get('DBasic::common.minhour'); ?>: <?php echo e($rank->hours); ?></span>
               </h4>
               <ul class="mb-0">
               <?php $__currentLoopData = $rank->subfleets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subfleet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <li><a href="<?php echo e(route('DBasic.subfleet', [$subfleet->type])); ?>" class="tooltiptop" title="View fleet"><?php echo e($subfleet->name.' | '.optional($subfleet->airline)->name); ?></a></li>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </ul>
            </div>
         </div>
      </div>
      <?php endif; ?>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/ranks/index.blade.php ENDPATH**/ ?>