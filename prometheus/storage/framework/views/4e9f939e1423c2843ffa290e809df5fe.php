<?php if(filled($deleted_airports)): ?>
  <div style="margin-bottom: 5px;">
    <table class="table table-borderless table-hover small text-left" style="margin-bottom: 1px;">
      <tr class="bg-warning">
        <th class="text-center" colspan="7"><b>Soft Deleted Airports</b></th>
      </tr>
      <tr>
        <th>ID</th>
        <th>ICAO</th>
        <th>IATA</th>
        <th>Name</th>
        <th>Country</th>
        <th>Deleted At</th>
        <th class="text-right">Action</th>
      </tr>
      <?php $__currentLoopData = $deleted_airports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
          <td><?php echo e($airport->id); ?></td>
          <td><?php echo e($airport->icao); ?></td>
          <td><?php echo e($airport->iata); ?></td>
          <td><?php echo e($airport->name); ?></td>
          <td><?php echo e($airport->country); ?></td>
          <td><?php echo e($airport->deleted_at->format('d.m.Y H:i')); ?></td>
          <td class="text-right">
            <a href="<?php echo e(route('DAirports.restore_airport', ['id' => $airport->id])); ?>" class="btn btn-success btn-sm mx-1">Restore</a>
            <a href="<?php echo e(route('DAirports.destroy_airport', ['id' => $airport->id])); ?>" class="btn btn-danger btn-sm mx-1" onclick="return confirm('This will delete the airport record !!!\n\n Are you sure ?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </table>
  </div>
<?php else: ?>
  <p>No soft deleted airports found.</p>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/modules/DisposableAirports/Providers/../Resources/views/airports_table.blade.php ENDPATH**/ ?>