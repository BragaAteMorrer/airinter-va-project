<div class="row">
  <!-- Code Field -->
  <div class="form-group col-sm-12">
    <div class="form-container">
      <h6>
        <i class="fas fa-users mr-2"></i>
        <?php if($users_count > 0): ?> <?php echo e($users_count.' users are assigned to this role'); ?> <?php else: ?> No Users <?php endif; ?>
      </h6>
      <div class="form-container-body">
        <?php if($users_count > 0): ?>
          <div class="row">
            <div class="col-sm-12">
              <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                &nbsp;&bull;&nbsp;<a href="<?php echo e(route('admin.users.edit', [$u->id])); ?>"><?php echo e($u->ident.' '.$u->name); ?></a>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/roles/users.blade.php ENDPATH**/ ?>