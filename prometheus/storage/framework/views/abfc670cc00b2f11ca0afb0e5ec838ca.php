<div id="airports_table_wrapper">
  <table class="table table-hover table-responsive" id="airports-table">
    <thead>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('icao', 'ICAO'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('iata', 'IATA'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', 'Name'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('location', 'Location'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('region', 'Region'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('country', 'Country'));?></th>
    <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('elevation', 'Elevation'));?></th>    
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('hub', 'Hub'));?></th>
    <th style="text-align: center;"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('notes', 'Notes'));?></th>
    <th style="text-align: center;">GH Cost</th>
    <th style="text-align: center;">JetA</th>
    <th style="text-align: center;">100LL</th>
    <th style="text-align: center;">MOGAS</th>
    <th></th>
    </thead>
    <tbody>
    <?php $__currentLoopData = $airports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><a href="<?php echo e(route('admin.airports.edit', [$airport->id])); ?>"><?php echo e($airport->icao); ?></a></td>
        <td><a href="<?php echo e(route('admin.airports.edit', [$airport->id])); ?>"><?php echo e($airport->iata); ?></a></td>
        <td><?php echo e($airport->name); ?></td>
        <td><?php echo e($airport->location); ?></td>
        <td><?php echo e($airport->region); ?></td>
        <td><?php echo e($airport->country); ?></td>
        <td><?php echo e($airport->elevation); ?></td>
        <td style="text-align: center;">
          <?php if($airport->hub === true): ?>
            <span class="label label-success">Hub</span>
          <?php endif; ?>
        </td>
        <td style="text-align: center;">
          <?php if(filled($airport->notes)): ?>
            <span class="label label-info" title="<?php echo e($airport->notes); ?>">Notes</span>
          <?php endif; ?>
        </td>
        <td style="text-align: center;">
          <?php echo e($airport->ground_handling_cost); ?>

        </td>
        <td style="text-align: center;">
          <a class="inline" href="#" data-pk="<?php echo e($airport->id); ?>"
             data-name="fuel_jeta_cost"><?php echo e($airport->fuel_jeta_cost); ?></a>
        </td>
        <td style="text-align: center;">
          <a class="inline" href="#" data-pk="<?php echo e($airport->id); ?>"
             data-name="fuel_100ll_cost"><?php echo e($airport->fuel_100ll_cost); ?></a>
        </td>
        <td style="text-align: center;">
          <a class="inline" href="#" data-pk="<?php echo e($airport->id); ?>"
             data-name="fuel_mogas_cost"><?php echo e($airport->fuel_mogas_cost); ?></a>
        </td>
        <td style="text-align: right;">
          <?php echo e(Form::open(['route' => ['admin.airports.destroy', $airport->id], 'method' => 'delete'])); ?>

          <a href="<?php echo e(route('admin.airports.edit', [$airport->id])); ?>" class='btn btn-sm btn-success btn-icon'><i
              class="fas fa-pencil-alt"></i></a>
          <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

          <?php echo e(Form::close()); ?>

        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/airports/table.blade.php ENDPATH**/ ?>