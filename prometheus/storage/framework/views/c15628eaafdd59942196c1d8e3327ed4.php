<?php if($pilots->count() > 0): ?>
<table class="table table-striped table-hover mb-0">
  <thead>
    <tr>
      <th class="text-start text-nowrap"><?php echo e(trans_choice('common.pilot', 1)); ?></th>      
      <?php for($x = 1; $x <= $tour->legs_count; $x++): ?>
      <th class="text-center fw-normal"><?php echo e('Leg '.$x); ?></th>
      <?php endfor; ?>
    </tr>
  </thead>
  <tbody>
    <?php $__currentLoopData = $pilots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pilot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr class="align-middle">
      <td class="text-start text-nowrap">
        <span class="fi fi-<?php echo e($pilot->country); ?> shadow-img me-1" title="<?php echo app('translator')->get('sptheme.country'); ?>"></span><a href="<?php echo e(route('frontend.profile.show', [$pilot->id])); ?>" class="tooltiptop" title="<?php echo e($pilot->ident); ?>"><?php echo e($pilot->name); ?></a>
        <?php if($tour_report[$pilot->id]['order'] === false): ?>
          <span class="float-end"><i class="ph-fill ph-warning-circle text-danger tooltiptop" title="Flown order: <?php echo e($tour_report[$pilot->id]['flown']); ?>"></i></span>
        <?php endif; ?>
      </td>
      <?php for($y = 1; $y <= $tour->legs_count; $y++): ?>
      <td class="text-center">
        <?php if(isset($tour_report[$pilot->id][$y]) && $tour_report[$pilot->id][$y] === true): ?>
        <i class="ph-fill ph-check-square fs-20 tooltiptop <?php if($tour_report[$pilot->id]['order'] === true): ?> text-success <?php else: ?> text-danger <?php endif; ?>" title="Leg <?php echo e($y); ?>"></i>
        <?php else: ?>
        <i class="ph-fill ph-square text-muted fs-20 tooltiptop" title="Leg <?php echo e($y); ?>"></i>
        <?php endif; ?>
      </td>
      <?php endfor; ?>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php else: ?>
<div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.noreports'); ?></div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/tours/report_table.blade.php ENDPATH**/ ?>