<div id="expenses-wrapper" class="col-12">
  <div class="header">
    <h3>expenses</h3>
    <?php $__env->startComponent('admin.components.info'); ?>
      These expenses are only applied to this aircraft.
    <?php echo $__env->renderComponent(); ?>
  </div>

  <?php if(count($aircraft->expenses) === 0): ?>
    <?php echo $__env->make('admin.common.none_added', ['type' => 'expenses'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php endif; ?>

  <table class="table table-responsive" id="expenses">
    <?php if(count($aircraft->expenses) > 0): ?>
      <thead>
      <th>Name</th>
      <th>Cost&nbsp;<span class="small"><?php echo e(currency(setting('units.currency'))); ?></span></th>
      <th>Type</th>
      <th></th>
      </thead>
    <?php endif; ?>

    <tbody>
    <?php $__currentLoopData = $aircraft->expenses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $expense): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td>
          <p>
            <a class="text" href="#" data-pk="<?php echo e($expense->id); ?>"
               data-name="name"><?php echo e($expense->name); ?></a>
          </p>
        </td>
        <td>
          <p>
            <a class="text" href="#" data-pk="<?php echo e($expense->id); ?>"
               data-name="amount"><?php echo e($expense->amount); ?></a>
          </p>
        </td>
        <td>
          <p>
            <a href="#"
               class="dropdown"
               data-pk="<?php echo e($expense->id); ?>"
               data-name="type"><?php echo e(\App\Models\Enums\ExpenseType::label($expense->type)); ?></a>
          </p>
        </td>
        <td align="right">
          <?php echo e(Form::open(['url' => url('/admin/aircraft/'.$aircraft->id.'/expenses'),
                      'method' => 'delete', 'class' => 'modify_expense form-inline'])); ?>

          <?php echo e(Form::hidden('expense_id', $expense->id)); ?>

          <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit',
                           'class' => 'btn btn-sm btn-danger btn-icon',
                           'onclick' => "return confirm('Are you sure?')",
                           ])); ?>

          <?php echo e(Form::close()); ?>

        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
  <hr/>
  <div class="row">
    <div class="col-sm-12">
      <div class="text-right">
        <?php echo e(Form::open(['url' => url('/admin/aircraft/'.$aircraft->id.'/expenses'),
                        'method' => 'post', 'class' => 'modify_expense form-inline'])); ?>

        <?php echo e(Form::input('text', 'name', null, ['class' => 'form-control input-sm', 'placeholder' => 'Name'])); ?>

        <?php echo e(Form::number('amount', null, ['class' => 'form-control input-sm', 'placeholder' => 'Amount', 'step' => '0.01'])); ?>

        <?php echo e(Form::select('type', \App\Models\Enums\ExpenseType::select(), null, ['class' => 'select2'])); ?>

        <?php echo e(Form::button('<i class="fa fa-plus"></i> Add', ['type' => 'submit',
                         'class' => 'btn btn-success btn-small'])); ?>

        <?php echo e(Form::close()); ?>


        <p class="text-danger"><?php echo e($errors->first('name')); ?></p>
        <p class="text-danger"><?php echo e($errors->first('amount')); ?></p>
      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/aircraft/expenses.blade.php ENDPATH**/ ?>