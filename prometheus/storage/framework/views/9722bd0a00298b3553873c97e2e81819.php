<ul class="list-group">
  <?php $__currentLoopData = $files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <li class="list-group-item">
      <a href="<?php echo e(route('frontend.downloads.download', [$file->id])); ?>" class="btn btn-primary tooltiptop" title="Download" target="_blank" <?php if($file->isExternalFile): ?> data-external-redirect="<?php echo e($file->url); ?>" <?php endif; ?>><i class="ph-fill ph-file-arrow-down align-middle fs-20 me-1"></i><?php echo e($file->name); ?></a>
      <?php if($file->description): ?>
        - <?php echo e($file->description); ?>

      <?php endif; ?>
      <?php if($file->download_count > 0): ?>
        <span> - <?php echo e($file->download_count.' '.trans_choice('common.download', $file->download_count)); ?></span>
      <?php endif; ?>
    </li>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</ul><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/downloads/table.blade.php ENDPATH**/ ?>