<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('content'); ?>
  <div class="content">
    <?php if($cron_problem_exists): ?>
      <div class="alert alert-danger" role="alert">
        The cron has not run in more than 12 hours; make sure it's setup and check logs at
        <span class="text-monospace bg-gradient-dark">storage/logs/cron.log</span>.
        <a href="<?php echo e(docs_link('cron')); ?>" target="_blank">See the docs</a>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-md-9">
        <?php echo $__env->make('admin.dashboard.news', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      </div>
      <div class="col-md-3">
        <?php $__env->startComponent('admin.components.infobox'); ?>
          <?php $__env->slot('icon', 'pe-7s-users'); ?>
          <?php $__env->slot('type', 'Pilots'); ?>
          <?php $__env->slot('pending', $pending_users); ?>
          <?php $__env->slot('link', route('admin.users.index').'?state='.UserState::PENDING); ?>
        <?php echo $__env->renderComponent(); ?>

        <?php $__env->startComponent('admin.components.infobox'); ?>
          <?php $__env->slot('icon', 'pe-7s-cloud-upload'); ?>
          <?php $__env->slot('type', 'PIREPs'); ?>
          <?php $__env->slot('pending', $pending_pireps); ?>
          <?php $__env->slot('link', route('admin.pireps.index').'?search=state:'.PirepState::PENDING); ?>
        <?php echo $__env->renderComponent(); ?>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        
      </div>
      <div class="col-md-6">
        
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        
      </div>
      <div class="col-md-6">
        
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
  <?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
  <script>
    $(document).ready(function () {
      $(document).on('submit', 'form.pjax_news_form', function (event) {
        event.preventDefault();
        $.pjax.submit(event, '#pjax_news_wrapper', {push: false});
      });

      /*$(document).on('pjax:complete', function () {
          $(".select2").select2();
      });*/
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/admin/dashboard/index.blade.php ENDPATH**/ ?>