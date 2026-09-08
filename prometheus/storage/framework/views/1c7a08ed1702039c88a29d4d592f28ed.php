<?php $__env->startSection('scripts'); ?>
  <script>
    function setEditable() {

      const token = $('meta[name="csrf-token"]').attr('content');
      const api_key = $('meta[name="api-key"]').attr('content');
    }

    $(document).ready(function () {

      setEditable();

      $(document).on('submit', 'form.modify_typerating', function (event) {
        event.preventDefault();
        console.log(event);
        $.pjax.submit(event, '#user_typeratings_wrapper', {push: false});
      });

      $(document).on('pjax:complete', function () {
        initPlugins();
        setEditable();
      });
    });
  </script>

  <?php echo $__env->make('admin.scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/users/script.blade.php ENDPATH**/ ?>