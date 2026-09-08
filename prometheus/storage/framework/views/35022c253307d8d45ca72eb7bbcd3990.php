<?php if($is_visible): ?>
<div class="card border">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-ticket align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::widgets.js_travel'); ?></h4>
      <form class="form" method="post" action="<?php echo e(route($form_route)); ?>">
         <?php echo csrf_field(); ?>
         <?php if(empty($fixed_dest)): ?>
         <?php elseif($fixed_dest && $is_possible): ?>
         <button class="btn btn-success" type="submit" title="<?php echo e($icon_title); ?>"><?php echo app('translator')->get('DBasic::widgets.js_buttonf'); ?></button>
         <input type="hidden" name="newloc" value="<?php echo e($fixed_dest); ?>">
         <?php endif; ?>
         <div class="form-group form-bg-grey rounded mb-3">
            <div class="row">
               <label class="col-1 control-label"><i class="ph-fill ph-info align-middle fs-20 me-1"></i></label>
               <div class="col-11">
                  <div class="input-group input-group-lg">
                     <select name="newloc" id="newloc" class="form-select <?php echo e($hubs_only); ?> airport_search" placeholder="<?php echo app('translator')->get('sptheme.typetosearch'); ?>" onchange="Check_Airport_Selection()"></select>
                  </div>
               </div>
            </div>
         </div>         
         <input type="hidden" name="price" value="<?php echo e($price); ?>">
         <input type="hidden" name="basep" value="<?php echo e($base_price); ?>">
         <input type="hidden" name="croute" value="<?php echo e(url()->current()); ?>">
         <?php if($price === 'auto'): ?>
         <button class="btn btn-info float-end" type="submit" name="interim_price" value="1"><?php echo app('translator')->get('DBasic::widgets.js_check'); ?></button>
         <?php endif; ?>
         <button class="btn btn-success float-end mx-2" type="submit"><?php echo app('translator')->get('DBasic::widgets.js_button'); ?></button>
      </form>
   </div>
</div>
<?php echo $__env->make('DBasic::scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/jumpseat_travel.blade.php ENDPATH**/ ?>