<?php if($news->count() === 0): ?>
<div class="card border mb-0">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-article-ny-times fs-20 me-1"></i><?php echo app('translator')->get('widgets.latestnews.news'); ?></h4>
      <div class="alert alert-danger mb-0" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('widgets.latestnews.nonewsfound'); ?></div>
   </div>
</div>
<?php else: ?>
<?php $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="card border mb-3">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-article-ny-times fs-20 me-1"></i><?php echo e($item->subject); ?></h4>
      <p class="small text-muted"><?php if(Auth::check()): ?> <?php echo e($item->user->name); ?> <?php else: ?> <?php echo e($item->user->name_private); ?> <?php endif; ?>- <?php echo e(show_datetime($item->created_at)); ?></p>
      <p><?php echo $item->body; ?></p>
   </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/widgets/latest_news.blade.php ENDPATH**/ ?>