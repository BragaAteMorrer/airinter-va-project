<table class="table table-hover table-responsive" id="awards-table">
  <thead>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', 'Name'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('description', 'Description'));?></th>
    <th>Image</th>
    <th class="text-center">Owners</th>
    <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('active', 'Active'));?></th>
    <th class="text-right">Action</th>
  </thead>
  <tbody>
    <?php $__currentLoopData = $awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td>
          <a href="<?php echo e(route('admin.awards.edit', [$award->id])); ?>"><?php echo e($award->name); ?></a>
        </td>
        <td>
          <?php echo e($award->description); ?>

        </td>
        <td>
          <?php if($award->image_url): ?>
            <img src="<?php echo e($award->image_url); ?>" name="<?php echo e($award->name); ?>" alt="No Image Available" style="height: 100px"/>
          <?php else: ?>
            -
          <?php endif; ?>
        </td>
        <td class="text-center"><?php echo e($counts[$award->id]); ?></td>
        <td class="text-center">
          <?php if($award->active): ?>
            <i class="fas fa-check-circle fa-2x text-success"></i>
          <?php else: ?> 
            <i class="fas fa-times-circle fa-2x text-danger"></i>
          <?php endif; ?>
        </td>
        <td class="text-right">
          <?php echo e(Form::open(['route' => ['admin.awards.destroy', $award->id], 'method' => 'delete'])); ?>

          <a href="<?php echo e(route('admin.awards.edit', [$award->id])); ?>" class='btn btn-sm btn-success btn-icon'>
            <i class="fas fa-pencil-alt"></i>
          </a>
          <?php echo e(Form::button('<i class="fa fa-times"></i>', [
                  'type' => 'submit',
                  'class' => 'btn btn-sm btn-danger btn-icon',
                  'onclick' => "return confirm('Are you sure you want to delete this award?')"
          ])); ?>

          <?php echo e(Form::close()); ?>

        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/awards/table.blade.php ENDPATH**/ ?>