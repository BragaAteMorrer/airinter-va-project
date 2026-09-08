<?php $__currentLoopData = collect(session('flash_notification', collect()))->toArray(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <?php if(is_string($message)): ?>
      <script>
         Swal.fire({
            toast: true,
            icon: "error",
            title: "<?php echo app('translator')->get('sptheme.error'); ?>!",
            text: "<?php echo $message; ?>",
            position: "top-end",
            showConfirmButton: false,
            timer: 5000,
            animation: true,
            iconColor: 'white',
            customClass: {
               popup: 'colored-toast',
            },
            timerProgressBar: true,
            didOpen: (toast) => {
               toast.onmouseenter = Swal.stopTimer;
               toast.onmouseleave = Swal.resumeTimer;
            }
         });
      </script>
   <?php else: ?>
      <?php if($message['overlay']): ?>
         <?php echo $__env->make('flash.modal', ['modalClass' => 'flash-modal', 'title' => $message['title'], 'body' => $message['message']], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <?php else: ?>
         <script>
            <?php if($message['level'] == 'danger'): ?>
               var iconstates = 'error';
            <?php else: ?>
               var iconstates = '<?php echo e($message['level']); ?>';
            <?php endif; ?>

            Swal.fire({
               toast: true,
               icon: iconstates,
               title: "<?php echo $message['message']; ?>",
               animation: true,
               position: 'top-end',
               showConfirmButton: true,
               showDenyButton: false,
               confirmButtonText: "<?php echo app('translator')->get('sptheme.ok'); ?>",
               iconColor: 'white',
               customClass: {
                  popup: 'colored-toast',
                  confirmButton: 'btn btn-secondary w-100',
               }
            });
         </script>
      <?php endif; ?>
   <?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php echo e(session()->forget('flash_notification')); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/flash/message.blade.php ENDPATH**/ ?>