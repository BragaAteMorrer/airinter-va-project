<div class="row">
  <div class="col-sm-12">
    <?php $__env->startComponent('admin.components.info'); ?>
      These are the awards that pilots can earn. Each award is assigned an
      award class, which will be run whenever a pilot's stats are changed,
      including after a PIREP is accepted.
    <?php echo $__env->renderComponent(); ?>
  </div>
</div>
<div class="row">
  <div class="form-group col-sm-6">
    <?php echo Form::label('name', 'Name:'); ?>&nbsp;<span class="required">*</span>
    <div class="callout callout-info">
      <i class="icon fa fa-info">&nbsp;&nbsp;</i>
      This will be the title of the award
    </div>
    <?php echo Form::text('name', null, ['class' => 'form-control']); ?>

    <p class="text-danger"><?php echo e($errors->first('name')); ?></p>
  </div>

  <div class="form-group col-sm-6">
    <?php echo Form::label('image', 'Image:'); ?>

    <div class="callout callout-info">
      <i class="icon fa fa-info">&nbsp;&nbsp;</i>
      This is the image of the award. Be creative!
    </div>
    <?php echo Form::text('image_url', null, ['class' => 'form-control', 'placeholder' => 'Enter the url of the image location']); ?>

    <p class="text-danger"><?php echo e($errors->first('image_url')); ?></p>
  </div>
</div>

<div class="row">
  <div class="form-group col-sm-6">
    <?php echo Form::label('description', 'Description:'); ?>&nbsp
    <div class="callout callout-info">
      <i class="icon fa fa-info">&nbsp;&nbsp;</i>
      This is the description of the award.
    </div>
    <?php echo Form::textarea('description', null, ['class' => 'form-control']); ?>

    <p class="text-danger"><?php echo e($errors->first('description')); ?></p>
  </div>

  <div class="form-group col-sm-6">
    <div>
      <?php echo e(Form::label('ref_model', 'Award Class:')); ?>

      <?php echo e(Form::select('ref_model', $award_classes, null, ['class' => 'form-control select2', 'id' => 'award_class_select'])); ?>

      <p class="text-danger"><?php echo e($errors->first('ref_model')); ?></p>
    </div>
    <div>
      <?php echo e(Form::label('ref_model_params', 'Award Class parameters')); ?>

      <?php echo e(Form::text('ref_model_params', null, ['class' => 'form-control'])); ?>

      <p class="text-danger"><?php echo e($errors->first('ref_model_params')); ?></p>
      <p id="ref_model_param_description"></p>
    </div>
  </div>
</div>

<div class="row">
  
  <div class="form-group col-sm-6 text-left">
    <div class="checkbox">
      <label class="checkbox-inline">
        <?php echo e(Form::label('active', 'Active: ')); ?>

        <?php echo e(Form::hidden('active', false)); ?>

        <?php echo e(Form::checkbox('active')); ?>

      </label>
    </div>
  </div>
  
  <div class="form-group col-sm-6">
    <div class="pull-right">
      <?php echo Form::button('Save', ['type' => 'submit', 'class' => 'btn btn-success']); ?>

      <a href="<?php echo route('admin.awards.index'); ?>" class="btn btn-warn">Cancel</a>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/awards/fields.blade.php ENDPATH**/ ?>