<div id="pjax_news_wrapper">
  <div class="card border-blue-bottom" id="add_news">
    <div class="content">
      <div class="header">
        <h4 class="title">Add News</h4>
      </div>
      <?php echo e(Form::open(['route' => 'admin.dashboard.news', 'method' => 'post', 'class' => 'pjax_news_form'])); ?>

        <table class="table">
          <tr>
            <td><?php echo e(Form::label('subject', 'Subject:')); ?></td>
            <td><?php echo e(Form::text('subject', '', ['class' => 'form-control'])); ?></td>
          </tr>
          <tr>
            <td><?php echo e(Form::label('body', 'Body:')); ?></td>
            <td><?php echo Form::textarea('body', '', ['id' => 'news_editor', 'class' => 'editor']); ?></td>
          </tr>
          <tr>
        </table>
        <div style="display:flex; align-items: center; justify-content: space-between;">
          <div class="checkbox">
            <label class="checkbox-inline">
              <?php echo e(Form::label('send_notifications', 'Send notifications:')); ?>

              <input name="send_notifications" type="hidden" value="0"/>
              <?php echo e(Form::checkbox('send_notifications')); ?>

            </label>
          </div>
          <div>
            <?php echo e(Form::button('<i class="fas fa-plus-circle"></i>&nbsp;add', ['type' => 'submit', 'class' => 'btn btn-success btn-s'])); ?>

          </div>
        </div>
      <?php echo e(Form::close()); ?>

    </div>
  </div>
  <div class="card border-blue-bottom" id="edit_news" style="display:none;">
    <div class="content">
      <div class="header">
        <h4 class="title" id="edit_title">Edit News</h4>
      </div>
      <?php echo e(Form::open(['route' => 'admin.dashboard.news', 'method' => 'patch', 'class' => 'pjax_news_form'])); ?>

        <?php echo e(Form::hidden('id', '', ['id' => 'edit_id'])); ?>

        <table class="table">
          <tr>
            <td><?php echo e(Form::label('subject', 'Subject:')); ?></td>
            <td><?php echo e(Form::text('subject', '', ['id' => 'edit_subject', 'class' => 'form-control'])); ?></td>
          </tr>
          <tr>
            <td><?php echo e(Form::label('body', 'Body:')); ?></td>
            <td><?php echo Form::textarea('body', '', ['id' => 'edit_body', 'class' => 'editor']); ?></td>
          </tr>
        </table>
        <div style="display:flex; align-items: center; justify-content: space-between;">
          <div class="checkbox">
            <label class="checkbox-inline">
              <?php echo e(Form::label('send_notifications', 'Send notifications:')); ?>

              <input name="send_notifications" type="hidden" value="0"/>
              <?php echo e(Form::checkbox('send_notifications')); ?>

            </label>
          </div>
          <div>
            <button type="button" class="btn btn-warning btn-s" onclick="closeEdit()">Cancel</button>
            <?php echo e(Form::button('<i class="fas fa-pencil-alt"></i>&nbsp;edit', ['type' => 'submit', 'class' => 'btn btn-success btn-s'])); ?>

          </div>
        </div>
      <?php echo e(Form::close()); ?>

    </div>
  </div>
  <div class="card border-blue-bottom">
    <div class="content">
      <?php if($news->count() === 0): ?>
        <div class="text-center text-muted" style="padding: 30px;">
          No news items
        </div>
      <?php else: ?>
        <table class="table">
          <tr>
            <th>Subject</th>
            <th>Body</th>
            <th>Poster</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
          <?php $__currentLoopData = $news; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
              <th><?php echo e($item->subject); ?></th>
              <td><?php echo $item->body; ?></td>
              <td><?php echo e(optional($item->user)->name_private); ?></td>
              <td><?php echo e($item->created_at->format('d.M.y')); ?></td>
              <td style="display: flex;gap: .5rem;">
                <button class="btn btn-primary btn-xs text-small" onclick="editNews(<?php echo e($item->toJson()); ?>)">Edit</button>
                <?php echo e(Form::open(['route' => 'admin.dashboard.news', 'method' => 'delete', 'class' => 'pjax_news_form'])); ?>

                <?php echo e(Form::hidden('news_id', $item->id)); ?>

                <?php echo e(Form::button('Delete', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs text-small', 'onclick' => "return confirm('Are you sure?')"])); ?>

                <?php echo e(Form::close()); ?>

              </td>
            </tr>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
  <script>
    $(document).ready(function () { CKEDITOR.replace('news_editor'); });
    if (typeof $('input').iCheck !== 'undefined') {
      $('input').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'icheckbox_square-blue'
      });
    }
  </script>
</div>
<?php $__env->startSection('scripts'); ?>
  <?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
  <script src="<?php echo e(public_asset('assets/vendor/ckeditor4/ckeditor.js')); ?>"></script>
  <script>
    function editNews(news) {
      CKEDITOR.replace('edit_body')
      $('#edit_title').html('Edit News: ' + news.subject);
      $('#edit_subject').val(news.subject)
      CKEDITOR.instances.edit_body.setData(news.body)
      $('#edit_id').val(news.id)
      $('#add_news').hide();
      $('#edit_news').show();
    }

    function closeEdit() {
      $('#edit_news').hide();
      $('#add_news').show()
    }

  </script>
<?php $__env->stopSection(); ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/dashboard/news.blade.php ENDPATH**/ ?>