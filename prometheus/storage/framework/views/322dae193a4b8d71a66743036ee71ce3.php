<?php if($is_visible): ?>
<div class="card border">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-air-traffic-control align-middle fs-20 me-1"></i><?php echo app('translator')->get('DBasic::widgets.airport_info'); ?>
         <?php if($config['type'] === 'hubs'): ?><span class="float-end small fw-normal"><?php echo app('translator')->get('DBasic::widgets.hubs_only'); ?></span><?php endif; ?>
      </h4>
      <form class="form-horizontal">
         <?php echo csrf_field(); ?>
         <div class="form-group form-bg-grey rounded mb-0">
            <div class="row">
               <label class="col-1 control-label"><i class="ph-fill ph-info align-middle fs-20 me-1"></i></label>
               <div class="col-11">
                  <div class="input-group input-group-lg">
                     <select name="airport_selector" id="airport_selector" class="form-select <?php echo e($hubs_only); ?> airport_search" placeholder="<?php echo app('translator')->get('sptheme.typetosearch'); ?>" onchange="Check_Airport_Selection()"></select>
                  </div>
               </div>
            </div>
         </div>
         <a id="airport_link" style="visibility: hidden;" href="<?php echo e(route($apt_route, '')); ?>" class="btn btn-success mt-3 float-end"><?php echo app('translator')->get('DBasic::widgets.go'); ?></a>
      </form>
   </div>
</div>
<script type="text/javascript">
   const oldlink = document.getElementById('airport_link').href;

   function Check_Airport_Selection() {
      if (document.getElementById('airport_selector').value === 'ZZZZ') {
         document.getElementById('airport_link').style.visibility = 'hidden';
      } else {
         document.getElementById('airport_link').style.visibility = 'visible';
      }
      const selected_ap = document.getElementById('airport_selector').value;
      const newlink = '/'.concat(selected_ap);
      document.getElementById('airport_link').href = oldlink.concat(newlink);
   }
</script>
<?php echo $__env->make('DBasic::scripts.airport_search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/airport_info.blade.php ENDPATH**/ ?>