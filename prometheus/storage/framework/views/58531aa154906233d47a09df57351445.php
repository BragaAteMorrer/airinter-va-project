<div class="page-right-sidebar-inner">
   <div class="right-sidebar-top border-bottom">
      <div class="right-sidebar-tabs">
         <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
               <button class="nav-link bg-transparent border-0 active" id="wio-tab" data-bs-toggle="tab" data-bs-target="#wio-tab-pane" type="button" role="tab" aria-controls="wio-tab-pane" aria-selected="true">
                  <?php echo e($counter > 0 ? $counter : 0); ?> <?php echo e(trans_choice('common.pilot', 2)); ?>

               </button>
            </li>
         </ul>
      </div>
      <a href="javascript:void(0);" class="right-sidebar-toggle right-sidebar-close" title="<?php echo app('translator')->get('common.close'); ?>" data-sidebar-id="main-right-sidebar">
         <i class="ph-fill ph-minus text-danger fs-20"></i>
      </a>
   </div>
   <div class="right-sidebar-content" data-simplebar>
      <div class="tab-content" id="wioContent">
         <div class="tab-pane fade show active" role="tabpanel" id="wio-tab-pane" aria-labelledby="wio-tab" tabindex="0">
            <div class="chat-list">
               <span class="chat-title mt-0"><?php echo app('translator')->get('sptheme.wio-t'); ?></span>
               <?php if($counter > 0): ?>
                  <?php $__currentLoopData = $wio; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wio_user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <?php if($wio_user->last_seen && $wio_user->last_seen >= \Carbon\Carbon::now()->subMinutes(10)): ?>
                        <?php
                           $name_parts = explode(' ', $wio_user->name);
                           $gdpr_name = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 0, -1)) . ' ' . mb_substr(end($name_parts), 0, 1) : $wio_user->name;
                        ?>
                     <a href="<?php echo e(Auth::check() ? "/profile/{$wio_user->id}" : "/users/{$wio_user->id}"); ?>" class="right-sidebar-toggle chat-item unread border-bottom border-top" data-sidebar-id="chat-right-sidebar">
                        <div class="user-avatar">
                           <img src="<?php echo e($wio_user->avatar ? asset('uploads/' . $wio_user->avatar) : public_asset('SPTheme/images/noavatar.png')); ?>" class="user_img" alt="<?php echo app('translator')->get('profile.avatar'); ?>">
                        </div>
                        <div class="chat-info">
                           <span class="chat-author"><i class="fi fi-<?php echo e($wio_user->country); ?> shadow-img me-1" title="<?php echo app('translator')->get('common.country'); ?>"></i><?php echo e(Auth::check() ? $wio_user->name : $gdpr_name); ?></span>
                           <span class="chat-text"><?php echo e(\Carbon\Carbon::parse($wio_user->last_seen)->diffForHumans()); ?></span>
                           <span class="chat-time"><i class="ph-fill ph-house align-middle me-1"></i><?php echo e($wio_user->curr_airport_id); ?></span>
                        </div>
                     </a>
                     <?php endif; ?>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               <?php else: ?>
               <div class="alert alert-info" role="alert">
                  <i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.offduty'); ?>
               </div>
               <?php endif; ?>
            </div>
            <a href="javascript:void(0);" class="btn btn-primary position-absolute bottom-0 start-50 translate-middle right-sidebar-toggle right-sidebar-close" title="<?php echo app('translator')->get('common.close'); ?>" data-sidebar-id="main-right-sidebar"><?php echo app('translator')->get('common.close'); ?></a>
         </div>
      </div>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/wio.blade.php ENDPATH**/ ?>