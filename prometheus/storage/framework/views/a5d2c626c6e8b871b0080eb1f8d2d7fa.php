<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-bounding-box align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::tours.tptitle'); ?>
         <span class="fw-normal small float-end">
            <a href="<?php echo e(route('DSpecial.tours')); ?>" title="Show all tours" class="tooltiptop"><i class="ph-fill ph-eye fs-3"></i></a>
         </span>
      </h4>
      <?php if($counts['user'] > 0): ?>
      <table class="table table-hover table-striped mb-0">
         <thead>
            <th><?php echo app('translator')->get('common.name'); ?></th>
            <th><?php echo app('translator')->get('pireps.state.in_progress'); ?></th>
         </thead>
         <tbody>
            <?php $__currentLoopData = $prog; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
               <td><img src="<?php echo e(public_asset('SPTheme/images/tours/')); ?>/<?php echo e(strtolower($tp['code'])); ?>.jpg" width="50" class="me-2"><a href="<?php echo e(route('DSpecial.tour', [$tp['code']])); ?>" class="tooltiptop" title="<?php echo e($tp['name'].' '.$tp['remd']); ?>"><?php echo e($tp['name']); ?></a> - Leg <?php echo e($tp['comp'].' / '.$tp['legs']); ?></td>
               <td>
                  <div class="progress" style="height: 20px;">
                     <div class="progress-bar <?php echo e($tp['barc'].' '.$tp['warn']); ?> progress-bar-striped progress-bar-animatedx text-white fw-bold" role="progressbar" aria-valuenow="<?php echo e($tp['prog']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo e($tp['prog']); ?>%"><?php echo e($tp['prog']); ?>%</div>
                  </div>
               </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         </tbody>
      </table>
      <p class="mb-0 mt-3 text-end"><?php echo app('translator')->get('sptheme.total_users', ['user' => $counts['user'], 'tour' => $counts['tour']]); ?></p>
      <?php else: ?>
      <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.noflights'); ?></div>
      <?php endif; ?>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/widgets/tour_progress.blade.php ENDPATH**/ ?>