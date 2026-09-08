<?php if(filled($leader_board)): ?>
<?php $i=0; $arr_image=explode(",","/SPTheme/images/placed_1.png,/SPTheme/images/placed_2.png,/SPTheme/images/placed_3.png,/SPTheme/images/placed_4.png,/SPTheme/images/placed_5.png"); ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-list-star align-middle fs-20 me-1"></i><?php echo e($header_title); ?></h4>
      <table class="table table-hover table-striped mb-0">
         <thead>
            <?php if($count > 1): ?>
            <tr>
               <th></th>
               <th><?php echo app('translator')->get('DBasic::common.name'); ?></th>
               <th class="text-end"><?php echo e($footer_type); ?></th>
            </tr>
            <?php endif; ?>
         </thead>
         <tbody>
            <?php $__currentLoopData = $leader_board; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $board): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>       
            <tr>
               <td class="text-center"><img src="<?php echo e($arr_image[$i]); ?>" alt="<?php echo e($i+1); ?>. Place"></td>
               <td><a href="<?php echo e(route($board['route'], $board['id'])); ?>" class="tooltiptop" title="<?php echo e(substr($board['pilot_ident'],0,-3)); ?>"><?php echo e($board['name']); ?></a></td>
               <td class="text-end"><?php echo e($board['totals']); ?></td>
            </tr>
            <?php $i++; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         </tbody>
      </table>
   </div>
</div>
<?php else: ?>
<div class="card border">
   <div class="card-body table-responsive">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-list-star align-middle fs-20 me-1"></i><?php echo e($header_title); ?></h4>
      <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>There are no statistics.</div>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/leader_board.blade.php ENDPATH**/ ?>