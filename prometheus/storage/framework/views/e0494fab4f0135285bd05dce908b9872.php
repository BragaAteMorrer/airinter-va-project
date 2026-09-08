<?php $__env->startSection('title', __('profile.editprofile')); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/16.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <?php if($user->opt_in == 1): ?>
            <span class="badge text-bg-success badge-sm float-end"><i class="ph-fill ph-signature align-middle fs-4 me-1"></i><?php echo app('translator')->get('profile.opt-in'); ?>: <?php echo e($user->opt_in ? __('common.yes') : __('common.no')); ?></span>
            <?php else: ?>
            <span class="badge text-bg-danger badge-sm float-end"><i class="ph-fill ph-signature align-middle fs-4 me-1"></i><?php echo app('translator')->get('profile.opt-in'); ?>: <?php echo e($user->opt_in ? __('common.yes') : __('common.no')); ?></span>
            <?php endif; ?>
            <h4 class="mt-0 mb-0 header-title border-bottom"><i class="ph-fill ph-pencil fs-20 me-1"></i><?php echo app('translator')->get('profile.edityourprofile'); ?></h4>
            <form method="post" action="<?php echo e(route('frontend.profile.update', $user->id)); ?>" enctype="multipart/form-data" class="form-horizontal">
               <?php echo csrf_field(); ?>
               <?php echo method_field('PATCH'); ?>
               <?php echo $__env->make('profile.fields', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </form>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<?php echo $__env->make('scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<script>
  $("#new_API_key").on( "click", function() {
   Swal.fire({
      toast: true,
      icon: "info",
      title: "<?php echo app('translator')->get('profile.newapikey'); ?>",
      text: "<?php echo app('translator')->get('sptheme.confirmapi'); ?>",
      animation: true,
      position: 'top-end',
      showConfirmButton: true,
      showCancelButton: true,
      showDenyButton: false,
      confirmButtonText: "<?php echo app('translator')->get('sptheme.ok'); ?>",
      cancelButtonText: "<?php echo app('translator')->get('sptheme.cancel'); ?>",
      iconColor: 'white',
      customClass: {
         popup: 'colored-toast',
         confirmButton: 'btn btn-success mx-2',
         cancelButton: 'btn btn-warning mx-2',
      },
   }).then((result) => {
      if (result.isConfirmed) {
         window.location = "<?php echo e(route('frontend.profile.regen_apikey')); ?>";
      }
   });
});
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/profile/edit.blade.php ENDPATH**/ ?>