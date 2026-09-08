<div id="subfleet_ranks_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
  <div class="header">
    <h3>ranks</h3>
    <?php $__env->startComponent('admin.components.info'); ?>
      These ranks are allowed to fly aircraft in this subfleet. The pay can be
      set as a fixed amount, or a percentage of the rank's base payrate
    <?php echo $__env->renderComponent(); ?>
  </div>
  <br/>
  <table id="subfleet_ranks" class="table table-hover">
    <?php if(count($subfleet->ranks)): ?>
      <thead>
      <tr>
        <th>Name</th>
        <th style="text-align: center;">Base rate</th>
        <th style="text-align: center;">ACARS pay</th>
        <th style="text-align: center;">Manual pay</th>
        <th></th>
      </tr>
      </thead>
    <?php endif; ?>
    <tbody>
    <?php $__currentLoopData = $subfleet->ranks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rank): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td class="sorting_1"><?php echo e($rank->name); ?></td>
        <td style="text-align: center;"><?php echo e($rank->base_pay_rate ?: '-'); ?></td>
        <td style="text-align: center;">
          <a href="#" data-pk="<?php echo e($rank->id); ?>"
             data-name="acars_pay"><?php echo e($rank->pivot->acars_pay); ?></a>
        </td>

        <td style="text-align: center;">
          <a href="#" data-pk="<?php echo e($rank->id); ?>"
             data-name="manual_pay"><?php echo e($rank->pivot->manual_pay); ?></a>
        </td>

        <td style="text-align: right; width:3%;">
          <?php echo e(Form::open(['url' => '/admin/subfleets/'.$subfleet->id.'/ranks',
                          'method' => 'delete',
                          'class' => 'modify_rank'])); ?>

          <?php echo e(Form::hidden('rank_id', $rank->id)); ?>

          <?php echo e(Form::button('<i class="fa fa-times"></i>',
                           ['type' => 'submit',
                            'class' => 'btn btn-sm btn-danger btn-icon'])); ?>

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
        <?php echo e(Form::open(['url' => '/admin/subfleets/'.$subfleet->id.'/ranks',
                        'method' => 'post',
                        'class' => 'modify_rank form-inline'])); ?>

        <label for="rank_ids">Add ranks:</label>
        <?php echo e(Form::select('rank_ids[]', $avail_ranks, null, [
                'multiple' => 'multiple',
                'class' => 'select2 form-control input-lg'])); ?>

        <?php echo e(Form::button('<i class="glyphicon glyphicon-plus"></i> add',
                         ['type' => 'submit',
                          'class' => 'btn btn-success btn-s'])); ?>

        <?php echo e(Form::close()); ?>

      </div>
    </div>
  </div>
</div>

<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/subfleets/ranks.blade.php ENDPATH**/ ?>