<table class="table table-hover table-responsive" id="expenses-table">
  <thead>
  <th>Name</th>
  <th>Type</th>
  <th style="text-align: center;">Amount</th>
  <th>Airline</th>
  <th class="text-center">Active</th>
  <th></th>
  </thead>
  <tbody>
  <?php $__currentLoopData = $expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><a href="<?php echo e(route('admin.expenses.edit', [$expense->id])); ?>">
          <?php echo e($expense->name); ?></a>
      </td>
      <td><?php echo e(\App\Models\Enums\ExpenseType::label($expense->type)); ?></td>
      <td style="text-align: center;"><?php echo e($expense->amount); ?></td>
      <td>
        <?php if(filled($expense->airline)): ?>
          <?php echo e($expense->airline->name); ?>

        <?php else: ?>
          <span class="description">-</span>
        <?php endif; ?>
      </td>
      <td class="text-center">
                <span class="label label-<?php echo e($expense->active?'success':'default'); ?>">
                    <?php echo e(\App\Models\Enums\ActiveState::label($expense->active)); ?>

                </span>
      </td>
      <td class="text-right">
        <?php echo e(Form::open(['route' => ['admin.expenses.destroy', $expense->id], 'method' => 'delete'])); ?>

        <a href="<?php echo e(route('admin.expenses.edit', [$expense->id])); ?>"
           class='btn btn-sm btn-success btn-icon'><i class="fas fa-pencil-alt"></i></a>
        <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/expenses/table.blade.php ENDPATH**/ ?>