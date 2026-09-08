<?php $__env->startSection('scripts'); ?>
  <script>
    function setEditable() {
      const token = $('meta[name="csrf-token"]').attr('content');
      const api_key = $('meta[name="api-key"]').attr('content');

      <?php if(isset($aircraft)): ?>
      $('#expenses a.text').editable({
        emptytext: '0',
        url: '<?php echo e(url('/admin/aircraft/'.$aircraft->id.'/expenses')); ?>',
        title: 'Enter override value',
        ajaxOptions: {
          type: 'post',
          headers: {
            'x-api-key': api_key,
            'X-CSRF-TOKEN': token,
          }
        },
        params: function (params) {
          return {
            _method: 'put',
            expense_id: params.pk,
            name: params.name,
            value: params.value
          }
        }
      });

      $('#expenses a.dropdown').editable({
        type: 'select',
        emptytext: '0',
        source: <?php echo json_encode(list_to_editable(\App\Models\Enums\ExpenseType::select())); ?>,
        url: '<?php echo e(url('/admin/aircraft/'.$aircraft->id.'/expenses')); ?>',
        title: 'Enter override value',
        ajaxOptions: {
          type: 'post',
          headers: {
            'x-api-key': api_key,
            'X-CSRF-TOKEN': token,
          }
        },
        params: function (params) {
          return {
            _method: 'put',
            expense_id: params.pk,
            name: params.name,
            value: params.value
          }
        }
      });
      <?php endif; ?>
    }

    $(document).ready(function () {

      setEditable();

      $(document).on('submit', 'form.modify_expense', function (event) {
        event.preventDefault();
        console.log(event);
        $.pjax.submit(event, '#expenses-wrapper', {push: false});
      });

      $(document).on('pjax:complete', function () {
        initPlugins();
        setEditable();
      });
    });
  </script>

  <?php echo $__env->make('admin.scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/aircraft/script.blade.php ENDPATH**/ ?>