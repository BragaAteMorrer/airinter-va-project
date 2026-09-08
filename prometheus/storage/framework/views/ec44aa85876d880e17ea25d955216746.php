<?php $__currentLoopData = collect(session('flash_notification', collect()))->toArray(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php if(is_string($message)): ?>
    <div class="alert alert-error"><?php echo $message; ?></div>
  <?php else: ?>
    <?php if($message['overlay']): ?>
      <?php echo $__env->make('flash::modal', [
          'modalClass' => 'flash-modal',
          'title'      => $message['title'],
          'body'       => $message['message']
      ], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
      <?php
        $icon = match ($message['level']) {
          'danger', 'warning' => 'exclamation-triangle-fill',
          'success' => 'check-circle-fill',
          default => 'info-circle-fill'
        }
      ?>

      <div class="alert alert-<?php echo e($message['level']); ?> d-flex align-items-center <?php echo e($message['important'] ? 'alert-dismissible' : ''); ?>" role="alert">
        <i class="bi bi-<?php echo e($icon); ?> flex-shrink-0 me-2" role="img" aria-label="Info: "></i>

        <?php echo $message['message']; ?>


        <?php if($message['important']): ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php echo e(session()->forget('flash_notification')); ?>

<?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/seven/flash/message.blade.php ENDPATH**/ ?>