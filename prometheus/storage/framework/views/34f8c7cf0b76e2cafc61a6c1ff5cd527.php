<div id="pirep_comments_wrapper" class="col-12">
  <table class="table table-responsive" id="pireps-comments-table">
    <thead>
    <th></th>
    <th></th>
    <th></th>
    </thead>
    <tbody>
    <?php $__currentLoopData = $pirep->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <tr>
        <td width="1%" nowrap="" style="vertical-align: text-top">
          <a href="<?php echo e(route('admin.users.show', [$comment->user_id])); ?>">
            <?php echo e($comment->user->name); ?>

          </a>
        </td>
        <td>
          <p><?php echo e($comment->comment); ?></p>
          <p class="small"><?php echo e(show_datetime($comment->created_at)); ?></p>
        </td>
        <td align="right">
          <?php echo e(Form::open(['url' => url('/admin/pireps/'.$pirep->id.'/comments'),
                      'method' => 'delete', 'class' => 'pjax_form form-inline'])); ?>

          <?php echo e(Form::hidden('comment_id', $comment->id)); ?>

          <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit',
                           'class' => 'btn btn-danger btn-small',
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
        <?php echo e(Form::open(['url' => url('/admin/pireps/'.$pirep->id.'/comments'),
                        'method' => 'post', 'class' => 'pjax_form form-inline'])); ?>

        <?php echo e(Form::input('text', 'comment', null, ['class' => 'form-control input-sm'])); ?>

        <?php echo e(Form::button('<i class="fa fa-plus"></i> Add', ['type' => 'submit',
                         'class' => 'btn btn-success btn-small'])); ?>

        <?php echo e(Form::close()); ?>

      </div>
    </div>
  </div>
</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pireps/comments.blade.php ENDPATH**/ ?>