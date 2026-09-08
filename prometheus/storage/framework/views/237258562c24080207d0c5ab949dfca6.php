<div id="aircraft_fares_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
  <div class="header">
    <h3>fares</h3>
    <?php $__env->startComponent('admin.components.info'); ?>
      Fares assigned to the current subfleet. These can be overridden,
      otherwise, the value used is the default, which comes from the fare.
      The pay can be set as a fixed amount, or a percentage of the base fare
    <?php echo $__env->renderComponent(); ?>
  </div>
  <br/>
  <table id="aircraft_fares" class="table table-hover">
    <?php if(count($subfleet->fares)): ?>
      <thead>
      <tr>
        <th>Name</th>
        <th style="text-align: center;">Code</th>
        <th style="text-align: center;">Type</th>
        <th style="text-align: center;">Capacity (default)</th>
        <th style="text-align: center;">Price (default)</th>
        <th style="text-align: center;">Cost (default)</th>
        <th></th>
      </tr>
      </thead>
    <?php endif; ?>
    <tbody>
    <?php $__currentLoopData = $subfleet->fares; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td class="sorting_1 text-center"><?php echo e($atf->name); ?></td>
        <td class="text-center"><?php echo e($atf->code); ?></td>
        <td class="sorting_1 text-center"><?php echo e(\App\Models\Enums\FareType::label($atf->type)); ?></td>
        <td class="text-center">
          <a href="#" data-pk="<?php echo e($atf->id); ?>" data-name="capacity"><?php echo e($atf->pivot->capacity); ?></a>
          <span class="small background-color-grey-light">(<?php echo e($atf->capacity); ?>)</span>
        </td>
        <td class="text-center">
          <a href="#" data-pk="<?php echo e($atf->id); ?>" data-name="price"><?php echo e($atf->pivot->price); ?></a>
          <span class="small background-color-grey-light">(<?php echo e($atf->price); ?>)</span></td>
        <td class="text-center">
          <a href="#" data-pk="<?php echo e($atf->id); ?>" data-name="cost"><?php echo e($atf->pivot->cost); ?></a>
          <span class="small background-color-grey-light">(<?php echo e($atf->cost); ?>)</span></td>
        <td style="text-align: right; width:3%;">
          <?php echo e(Form::open(['url' => '/admin/subfleets/'.$subfleet->id.'/fares',
                          'method' => 'delete',
                          'class' => 'rm_fare'
                          ])); ?>

          <?php echo e(Form::hidden('fare_id', $atf->id)); ?>

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
        <?php echo e(Form::open(['url' => '/admin/subfleets/'.$subfleet->id.'/fares',
                        'method' => 'post',
                        'class' => 'rm_fare form-inline'
                        ])); ?>

        <?php echo e(Form::select('fare_id', $avail_fares, null, [
                'placeholder' => 'Select Fare',
                'class' => 'ac-fare-dropdown form-control input-lg select2',
            ])); ?>

        <?php echo e(Form::button('<i class="glyphicon glyphicon-plus"></i> add',
                         ['type' => 'submit',
                          'class' => 'btn btn-success btn-s'])); ?>

        <?php echo e(Form::close()); ?>

      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/subfleets/fares.blade.php ENDPATH**/ ?>