<table class="table table-hover table-responsive" id="users-table">
  <thead>
  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('id', 'ID'));?></th>
  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('pilot_id', 'Ident'));?></th>
  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('callsign', 'Callsign'));?></th>
  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('country', 'Country'));?></th>
  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', 'Name'));?></th>
  <th><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('email', 'E-Mail'));?></th>
  
  
  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flights', 'Flights'));?></th>
  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('flight_time', 'Flight Time'));?></th>
  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('transfer_time', 'Transfer Hours'));?></th>
  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('created_at', 'Registered'));?></th>
  <th class="text-center"><?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('state', 'State'));?></th>
  <th class="text-center">Actions</th>
  </thead>
  <tbody>
  <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td>
        <a href="<?php echo e(route('admin.users.edit', [$user->id])); ?>"><?php echo e($user->id); ?></a>
      </td>
      <td><?php echo e($user->pilot_id); ?></td>
      <td><?php echo e($user->callsign); ?></td>
      <td>
        <?php if(filled($user->country)): ?>
          <span class="flag-icon flag-icon-<?php echo e($user->country); ?>" title="<?php echo e($country->alpha2($user->country)['name']); ?>"></span>
        <?php endif; ?>
      </td>
      <td>
        <a href="<?php echo e(route('admin.users.edit', [$user->id])); ?>"><?php echo e($user->name); ?></a>
      </td>
      <td><?php echo e($user->email); ?></td>
      
      
      <td class="text-center"><?php echo e($user->flights); ?></td>
      <td class="text-center"><?php echo \App\Support\Units\Time::minutesToTimeString($user->flight_time); ?></td>
      <td class="text-center"><?php echo \App\Support\Units\Time::minutesToHours($user->transfer_time); ?></td>
      <td class="text-center"><?php echo e(show_date($user->created_at)); ?></td>
      <td class="text-center">
        <?php if($user->state === UserState::ACTIVE): ?>
          <span class="label label-success">
        <?php elseif($user->state === UserState::PENDING): ?>
          <span class="label label-warning">
        <?php else: ?>
          <span class="label label-default">
        <?php endif; ?>
        <?php echo e(UserState::label($user->state)); ?></span>
      </td>
      <td class="text-right">
        <?php echo e(Form::open(['route' => ['admin.users.destroy', $user->id], 'method' => 'delete'])); ?>

        <a href="<?php echo e(route('admin.users.edit', [$user->id])); ?>" class='btn btn-sm btn-success btn-icon'>
          <i class="fas fa-pencil-alt"></i>
        </a>
        <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/table.blade.php ENDPATH**/ ?>