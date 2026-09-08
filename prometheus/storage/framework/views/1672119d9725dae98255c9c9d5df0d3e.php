<?php $__env->startSection('scripts'); ?>
<script>
  $(document).ready(function () {
    const destContainer = document.getElementById('fares_container');

    $('#aircraft_select').on('change', function () {
      const aircraft_id = $(this).val();
      const url = '/pireps/fares?aircraft_id=' + aircraft_id;

      console.log('aircraft select change: ', aircraft_id);

      phpvms.request(url).then(response => {
        if (response.data === '') {
          console.log('no fare data');
          destContainer.innerHTML = '';
          return;
        }
        destContainer.innerHTML = response.data;
      }).catch(error => {
        console.error('fares error:', error);
      });
    });
  });
</script>
<?php echo $__env->make('scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/scripts.blade.php ENDPATH**/ ?>