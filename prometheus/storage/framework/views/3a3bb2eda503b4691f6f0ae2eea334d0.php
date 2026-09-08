<div id="user_typeratings_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
  <table id="user_typeratings" class="table table-hover">
    <?php if(count($user->typeratings)): ?>
      <thead>
        <tr>
          <th>Type</th>
          <th>Name</th>
          <th></th>
        </tr>
      </thead>
    <?php endif; ?>
    <tbody>
      <?php $__currentLoopData = $user->typeratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td class="sorting_1"><?php echo e($tr->type); ?></td>
          <td><?php echo e($tr->name); ?></td>
          <td style="text-align: right; width:3%;">
            <?php echo e(Form::open(['url' => '/admin/users/'.$user->id.'/typeratings', 'method' => 'delete', 'class' => 'modify_typerating'])); ?>

            <?php echo e(Form::hidden('typerating_id', $tr->id)); ?>

            <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon'])); ?>

            <?php echo e(Form::close()); ?>

          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
  <hr/>
  <div class="row">
    <div class="col-xs-12">
      <div class="text-right">
        <?php echo e(Form::open(['url' => '/admin/users/'.$user->id.'/typeratings', 'method' => 'post', 'class' => 'modify_typerating form-inline'])); ?>

        <?php echo e(Form::select('typerating_id', $avail_ratings, null, ['placeholder' => 'Select Type Rating', 'class' => 'ac-fare-dropdown form-control input-lg select2'])); ?>

        <?php echo e(Form::button('<i class="glyphicon glyphicon-plus"></i> add', ['type' => 'submit', 'class' => 'btn btn-success btn-s'])); ?>

        <?php echo e(Form::close()); ?>

      </div>
    </div>
  </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/type_ratings.blade.php ENDPATH**/ ?>