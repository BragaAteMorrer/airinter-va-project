<?php $__env->startSection('scripts'); ?>
  <script>
      <?php if(isset($award_descriptions)): ?>
    const award_descriptions = <?php echo json_encode($award_descriptions); ?>;
      <?php else: ?>
    const award_descriptions = {};
      <?php endif; ?>

    const changeParamDescription = (award_class) => {
        const descrip = award_descriptions[award_class];
        console.log('Found description: ', descrip);
        $("p#ref_model_param_description").text(descrip);
      };

    $(document).ready(() => {
      const select_id = "select#award_class_select";
      console.log('award descriptions', award_descriptions);
      $(select_id).change((e) => {
        const award_class = $(select_id + " option:selected").val();
        console.log('award class selected: ', award_class);
        changeParamDescription(award_class);
      });

      // on load
      const award_class = $(select_id + " option:selected").val();
      changeParamDescription(award_class);
    });
  </script>

<?php $__env->stopSection(); ?>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/awards/scripts.blade.php ENDPATH**/ ?>