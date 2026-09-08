<?php $__env->startSection('title', 'Activity Details'); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <div class="row">

        <div class="col-xl-12" style="padding: 0 15px;">
          <div class="form-container">
            <h6><i class="fas fa-info-circle"></i>&nbsp;Causer Information</h6>
            <div class="form-container-body">
              <div class="row">
                <div class="form-group col-sm-4">
                  <label>Causer Type</label>
                  <p><?php echo e(class_basename($activity->causer_type)); ?></p>
                </div>
                <div class="form-group col-sm-4">
                  <label>Causer</label>
                  <p>
                    <?php if(class_basename($activity->causer_type) === 'User'): ?>
                      <a href="<?php echo e(route('admin.users.edit', [$activity->causer_id])); ?>">
                        <?php echo e($activity->causer_id .' | '. $activity->causer->name_private); ?>

                      </a>
                    <?php else: ?>
                      <?php echo e($activity->causer_id.' | '. class_basename($activity->causer_type)); ?>

                    <?php endif; ?>
                  </p>
                </div>
                <div class="form-group col-sm-4">
                  <label>Caused At</label>
                  <p><?php echo e($activity->created_at->diffForHumans() . ' | ' .$activity->created_at->format('d.M')); ?></p>

                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-xl-12" style="padding: 0 15px;">
          <div class="form-container">
            <h6><i class="fas fa-info-circle"></i>&nbsp;Subject Information</h6>
            <div class="form-container-body">
              <div class="row">
                <div class="form-group col-sm-3">
                  <label>Subject Type</label>
                  <p><?php echo e(class_basename($activity->subject_type)); ?></p>
                </div>
                <div class="form-group col-sm-3">
                  <label>Subject Id</label>
                  <p>
                    <?php echo e($activity->subject_id); ?>

                  </p>
                </div>
                <div class="form-group col-sm-3">
                  <label>Subject Name</label>
                  <p>
                    <?php echo e($activity->subject->name ?? 'N/A'); ?>

                  </p>
                </div>
                <div class="form-group col-sm-3">
                  <label>Event Type</label>
                  <p><?php echo e($activity->event); ?></p>
                </div>
              </div>

              <?php if(isset($activity->changes['attributes']) && is_array($activity->changes['attributes'])): ?>
                <div class="table-responsive table-full-width">
                  <table class="table table-hover" id="flights-table">
                    <thead>
                    <th>Field</th>
                    <th>New Value</th>
                    <th>Old Value</th>
                    </thead>
                    <tbody>
                    
                    <?php $__currentLoopData = $activity->changes['attributes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $newValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                       <?php if(!is_array($newValue)): ?>
                        <tr>
                          <td><?php echo e($field); ?></td>
                          <td><?php echo e($newValue); ?></td>
                          
                          <td><?php echo e($activity->changes['old'][$field] ?? 'N/A'); ?></td>
                        </tr>
                       <?php else: ?>
                         <?php $__currentLoopData = $newValue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subField => $newSubFieldValue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <td><?php echo e($field.'.'.$subField); ?></td>
                           <td>
                             <?php echo e($newSubFieldValue); ?>

                           </td>
                           
                           <td><?php echo e($activity->changes['old'][$field][$subField] ?? 'N/A'); ?></td>
                         <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                       <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>

            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/activities/show.blade.php ENDPATH**/ ?>