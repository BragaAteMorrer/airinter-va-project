<div id="airframes_table_wrapper">
  <table class="table table-hover table-responsive">
    <thead>
      <th>ICAO</th>
      <th>Name</th>
      <th>SB Airframe ID</th>
      <th>Created At</th>
      <th>Updated At</th>
      <th></th>
    </thead>
    <tbody>
      <?php $__currentLoopData = $airframes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $af): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($af->icao); ?></a></td>
          <td><?php echo e($af->name); ?></td>
          <td><?php echo e($af->airframe_id); ?></td>
          <td><?php echo e($af->created_at->format('d.M.y H:i')); ?></td>
          <td><?php echo e($af->updated_at->format('d.M.y H:i')); ?></td>
          <td class="text-right">
            <?php echo e(Form::open(['route' => ['admin.airframes.destroy', $af->id], 'method' => 'delete'])); ?>

            <a href="<?php echo e(route('admin.airframes.edit', [$af->id])); ?>" class='btn btn-sm btn-success btn-icon'>
              <i class="fas fa-pencil-alt"></i></a>
            <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

            <?php echo e(Form::close()); ?>

          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/airframes/table.blade.php ENDPATH**/ ?>