<?php $__env->startSection('title', 'modules'); ?>
<?php $__env->startSection('actions'); ?>
  <li>
    <a href="<?php echo e(route('admin.modules.create')); ?>">
      <i class="ti-plus"></i>
      Add New</a>
  </li>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom">
    <div class="content">
      <div class="row">
        <div class="col-lg-12">
          <h5>Installed Modules</h5>
          <hr>
          <table class="table table-bordered table-primary">
            <thead>
              <th>Module</th>
              <th>Status</th>
              <th>Actions</th>
            </thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
              <tr>
                <td><?php echo e($module->name); ?></td>
                <td>
                  <?php if($module->enabled == 1): ?>
                    Enabled
                  <?php else: ?>
                    Disabled
                  <?php endif; ?>
                </td>
                <td>
                  <a class="btn btn-primary" href="<?php echo e(route('admin.modules.edit', $module->id)); ?>">Edit Module</a>
                  <a class="btn btn-danger" href="/admin/<?php echo e(strtolower($module->name)); ?>">View Admin Module</a>
                  <a class="btn btn-success" target="_blank" href="/<?php echo e(strtolower($module->name)); ?>">View Frontend Module</a>
                </td>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
              <tr>
                <td colspan="3" class="text-center">
                  No Modules Installed Yet!
                </td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>

          <?php if($new_modules): ?>
            <h5>Not Installed Modules</h5>
            <hr>
            <table class="table table-bordered table-primary">
              <thead>
              <th>Module</th>
              <th>Status</th>
              <th>Actions</th>
              </thead>
              <tbody>
              <?php $__currentLoopData = $new_modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                  <td><?php echo e($module); ?></td>
                  <td>Disabled</td>
                  <td>
                    <?php echo e(Form::open(['route' => ['admin.modules.enable']])); ?>

                    <?php echo e(Form::hidden('name', $module)); ?>

                    <?php echo e(Form::button('Activate Module', ['type' => 'submit', 'class' => 'btn btn-success'])); ?>

                    <?php echo e(Form::close()); ?>

                  </td>
                </tr>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/modules/index.blade.php ENDPATH**/ ?>