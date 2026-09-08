<?php if($aircraft && $aircraft->subfleet->fares->count() > 0): ?>
<div class="card border">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-box-arrow-down align-middle fs-20 me-1"></i><?php echo e(trans_choice('pireps.fare', 2)); ?></h4>
      <div class="row">
         <?php $__currentLoopData = $aircraft->subfleet->fares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
         <div class="col">
            <div class="form-group form-bg-grey rounded mb-3">
               <div class="row">
                  <label for="fare_<?php echo e($fare->id); ?>" class="col-5 control-label mt-1"><?php echo e($fare->name.' ('. \App\Models\Enums\FareType::label($fare->type).', max: '.optional($fare->pivot)->capacity.')'); ?></label>
                  <div class="col-7">
                     <input type="number" name="fare_<?php echo e($fare->id); ?>" id="fare_<?php echo e($fare->id); ?>" class="form-control" min="0" max="<?php echo e(optional($fare->pivot)->capacity.')'); ?>" value="<?php echo e(old('fare_'.$fare->id)); ?>">
                  </div>
               </div>
            </div>
         </div>
         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
   </div>
</div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/fares.blade.php ENDPATH**/ ?>