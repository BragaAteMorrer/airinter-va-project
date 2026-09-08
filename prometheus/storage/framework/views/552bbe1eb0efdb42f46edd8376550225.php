
<div id="airport-files-wrapper" class="col-12">
  <div class="header">
    <h3>files</h3>
    <?php $__env->startComponent('admin.components.info'); ?>
      Add a download link or upload a file to make available
    <?php echo $__env->renderComponent(); ?>
  </div>

  <?php if(count($model->files) === 0): ?>
    <?php echo $__env->make('admin.common.none_added', ['type' => 'files'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php endif; ?>

  
  <table class="table table-hover table-responsive">
    <?php if(count($model->files)): ?>
      <thead>
      <tr>
        <td>Name</td>
        <td>Direct Link</td>
        <td>Downloads</td>
        <td class="text-right"></td>
      </tr>
      </thead>
    <?php endif; ?>
    <tbody>
    <?php $__currentLoopData = $model->files; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td><?php echo e($file->name); ?></td>
        <td><a href="<?php echo e($file->url); ?>" target="_blank">Link to file</a></td>
        <td><?php echo e($file->download_count); ?></td>
        <td class="text-right">
          <?php echo e(Form::open(['route' => ['admin.files.delete', $file->id], 'method' => 'delete'])); ?>

          <?php echo e(Form::hidden('id', $file->id)); ?>

          <?php echo e(Form::button('<i class="fa fa-times"></i>', [
                'type' => 'submit',
                'class' => 'btn btn-sm btn-danger btn-icon',
                'onclick' => "return confirm('Are you sure?')"])); ?>

          <?php echo e(Form::close()); ?>

        </td>
      </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
  </table>
  <hr>
  <div class="row">
    <div class="col-sm-12">
      <div class="text-right">
        <?php echo e(Form::open([
            'url' => route('admin.files.store'),
            'method' => 'POST',
            'class' => 'form-inline',
            'files' => true
           ])); ?>


        <?php echo e(Form::token()); ?>


        <span class="required">*</span>
        <?php echo e(Form::text('file_name', null, ['class' => 'form-control', 'placeholder' => 'Name'])); ?>

        <?php echo e(Form::text('file_description', null, ['class' => 'form-control', 'placeholder' => 'Description'])); ?>

        <?php echo e(Form::text('url', null, ['class' => 'form-control', 'placeholder' => 'URL'])); ?>

        <?php echo e(Form::file('file', ['class' => 'form-control'])); ?>


        
        <?php echo e(Form::hidden('ref_model', get_class($model))); ?>

        <?php echo e(Form::hidden('ref_model_id', $model->id)); ?>


        <?php echo e(Form::submit('Save', [
            'id'    => 'save_file_upload',
            'class' => 'btn btn-success'
           ])); ?>

        <div class="text-danger" style="padding-top: 10px;">
          <span><?php echo e($errors->first('filename')); ?></span>
          <span><?php echo e($errors->first('url')); ?></span>
          <span><?php echo e($errors->first('file')); ?></span>
        </div>

        <?php echo e(Form::close()); ?>

      </div>
    </div>
  </div>

</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/common/file_upload.blade.php ENDPATH**/ ?>