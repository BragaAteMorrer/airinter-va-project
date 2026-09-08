<div class="table-responsive ">
   <table class="table table-striped table-hover">
      <thead>
         <tr>
            <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('id', 'ID'));?></th>
            <th class="text-start"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', __('common.name')));?></th>
            <th class="text-start"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('rank_id', __('sptheme.rank')));?></th>
            <th class="text-end"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flights', trans_choice('common.flight', 2)));?></th>
            <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('home_airport_id', __('airports.home')));?></th>
            <?php if(Auth::check()): ?>
            <th class="text-center"><?php echo app('translator')->get('common.status'); ?></th>
            <?php endif; ?>
         </tr>
      </thead>
      <tbody>
         <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
         <tr class="align-middle">
            <td class="text-center">
               <?php if($user->avatar == null): ?>
               <img src="<?php echo e(public_asset('SPTheme/images/noavatar.png')); ?>" width="42" height="42" alt="<?php echo app('translator')->get('profile.avatar'); ?>" class="rounded-circle bg-primary img-fluid">
               <?php else: ?>
               <img src="<?php echo e($user->avatar->url); ?>" width="42" height="42" alt="<?php echo app('translator')->get('profile.avatar'); ?>" class="rounded-circle img-fluid">
               <?php endif; ?>
            </td>
            <td class="text-start">
               <?php if(filled($user->country) && strlen($user->country) === 2): ?>
               <span class="fi fi-<?php echo e($user->country); ?> shadow-img me-1" title="<?php echo e($country->alpha2($user->country)['name']); ?>"></span>
               <?php endif; ?>
               <a href="<?php echo e(route('frontend.users.show.public', [$user->id])); ?>" title="<?php echo e($user->ident); ?>" class="tooltiptop"><?php if(!Auth::check()): ?><?php echo e($user->name_private); ?><?php else: ?><?php echo e($user->name); ?><?php endif; ?></a>
               <?php if(!is_null($sp_settings['staff'])): ?>
               <?php if($user->hasRole($sp_settings['staff'])): ?> <span class="badge badge-warning tooltiptop" title="<?php echo app('translator')->get('sptheme.staff'); ?>"><i class="ph-fill ph-wrench"></i></span> <?php endif; ?>
               <?php endif; ?>
               <br>
               <span class="text-muted small">
                  <?php if($user->last_pirep): ?> <?php echo app('translator')->get('sptheme.lastflight'); ?> <?php echo e($user->last_pirep->submitted_at->diffForHumans()); ?>. <?php else: ?> <?php echo app('translator')->get('sptheme.noflight'); ?> <?php endif; ?>
               </span>
            </td>
            <td class="text-start">
               <?php if(!empty($user->rank) && !empty($user->rank->image_url)): ?>
               <img src="<?php echo e($user->rank->image_url); ?>" width="45" alt="<?php echo e($user->rank->name); ?>" class="float-start mt-2 me-2">
               <?php endif; ?>
               <?php echo e(optional($user->rank)->name); ?><br>
               <span clasS="small text-muted"><?php echo app('translator')->get('sptheme.base'); ?>: <a href="<?php echo e(route('frontend.airports.show', [$user->curr_airport_id ?? ''])); ?>" class="tooltipright" title="<?php echo app('translator')->get('sptheme.oai'); ?>"><?php echo e($user->curr_airport_id); ?></a></span>
            </td>
            <td class="text-end"><i class="ph-fill ph-airplane-takeoff align-text-bottom fs-20"></i> <?php echo e(number_format($user->flights)); ?></td>
            <td class="text-center">
               <?php if(filled($user->home_airport_id)): ?>
               <a href="<?php echo e(route('frontend.airports.show', [$user->home_airport_id ?? ''])); ?>" class="badge badge-rounded badge-primary tooltiptop" title="<?php echo app('translator')->get('sptheme.oai'); ?>"><i class="ph-fill ph-house"></i>
                  <?php echo e($user->home_airport_id); ?>

               </a>
               <?php endif; ?>
            </td>
            <?php if(Auth::check()): ?>
            <td class="text-center">
               <?php if($user->state == 0): ?>
               <span class="badge badge-info"><i class="ph-fill ph-question"></i> <?php echo app('translator')->get('user.state.pending'); ?></span>
               <?php endif; ?>
               <?php if($user->state == 1): ?>
               <span class="badge badge-success"><i class="ph-fill ph-check-fat"></i> <?php echo app('translator')->get('common.active'); ?></span>
               <?php endif; ?>
               <?php if($user->state == 2): ?>
               <span class="badge badge-danger"><i class="ph-fill ph-stop-circle"></i> <?php echo app('translator')->get('user.state.rejected'); ?></span>
               <?php endif; ?>
               <?php if($user->state == 3): ?>
               <span class="badge badge-warning"><i class="ph-fill ph-pause-circle"></i> <?php echo app('translator')->get('user.state.on_leave'); ?></span>
               <?php endif; ?>
               <?php if($user->state == 4): ?>
               <span class="badge badge-danger"><i class="ph-fill ph-warning-circle"></i> <?php echo app('translator')->get('user.state.suspended'); ?></span>
               <?php endif; ?>
               <?php if($user->state == 5): ?>
               <span class="badge badge-danger"><i class="ph-fill ph-x-circle"></i> <?php echo app('translator')->get('user.state.deleted'); ?></span>
               <?php endif; ?>
            </td>
            <?php endif; ?>
         </tr>
         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </tbody>
   </table>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/roster/table.blade.php ENDPATH**/ ?>