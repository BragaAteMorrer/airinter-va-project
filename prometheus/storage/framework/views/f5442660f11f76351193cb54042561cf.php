<?php $__env->startSection('title', 'Market'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <img src="<?php echo e(public_asset('/SPTheme/images/banner/37.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-bag align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.pilotshop'); ?>
               <span class="float-end"><a class="btn btn-primary" href="<?php echo e(route('DSpecial.market.show', [Auth::id()])); ?>"><?php echo app('translator')->get('DSpecial::common.mymarket'); ?></a></span>
            </h4>
            <?php if(!$items->count()): ?>
            <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('DSpecial::common.no_items'); ?></div>
            <?php else: ?>
            <?php if($categories): ?>
            <a class="btn btn-warning" href="<?php echo e(route('DSpecial.market')); ?>">All Items</a>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a class="btn btn-warning" href="?cat=<?php echo e($key); ?>"><?php echo e($name); ?></a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endif; ?>
            <?php if($items->count() > 1): ?>
            <span class="float-end">
               <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('name', null, null, ['class' => 'btn btn-primary']));?>
               <?php echo \Kyslik\ColumnSortable\SortableLink::render(array ('price', null, null, ['class' => 'btn btn-primary']));?>
            </span>
            <?php endif; ?>
            <?php endif; ?>
         </div>
      </div>
   </div>
</div>
<?php if($items->count()): ?>
<div class="row">
   <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-package align-middle fs-20 me-1"></i><?php echo e($item->name); ?></h4>
            <div class="row">
               <div class="col-3">
                  <?php if(filled($item->image_url)): ?>
                  <img src="<?php echo e($item->image_url); ?>" alt="<?php echo e($item->name); ?>" title="<?php echo e($item->name); ?>" class="img-fluid" width="300">
                  <?php endif; ?>
               </div>
               <div class="col-9">
                  <?php echo $item->description; ?>

               </div>
            </div>
         </div>
         <div class="card-footer text-center">
            <div class="row">
               <?php if($item->limit == 0 || $item->owners_count < $item->limit): ?>
                  <div class="col-4">
                     <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#giftModal<?php echo e($item->id); ?>"><?php echo app('translator')->get('sptheme.buygift'); ?></button>
                  </div>
                  <div class="col-4">
                  <?php if(!in_array($item->id, $myitems)): ?>
                     <form class="form" method="post" action="<?php echo e(route('DSpecial.market.buy')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="item_id" value="<?php echo e($item->id); ?>" />
                        <button class="btn btn-primary" type="submit"><?php echo e(__('DSpecial::common.buy')); ?></button>
                     </form>
                  <?php else: ?>
                     <span class="btn btn-danger" type="submit"><?php echo app('translator')->get('sptheme.bought'); ?></span>
                  <?php endif; ?>
                  </div>
                  <div class="col-4">
                     <h3><?php echo e(money($item->price, $units['currency'], $seperation)); ?></h3>
                  </div>
               <?php else: ?>
                  <div class="col-12">
                     <span class="btn btn-danger"><?php echo app('translator')->get('sptheme.sold'); ?></span>
                  </div>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </div>
   <div class="modal fade" id="giftModal<?php echo e($item->id); ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="giftModal<?php echo e($item->id); ?>Label" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
         <div class="modal-content">
            <div class="modal-header pb-0">
               <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-confetti fs-20 me-2"></i><?php echo app('translator')->get('sptheme.gift-t'); ?>: <?php echo e($item->name); ?></h4>
            </div>
            <form class="form-group" method="post" action="<?php echo e(route('DSpecial.market.buy')); ?>">
               <?php echo csrf_field(); ?>
               <div class="modal-body pt-0">
                  <div class="card-body p-0">
                     <input type="hidden" name="item_id" value="<?php echo e($item->id); ?>" />
                     <input type="hidden" name="is_gift" value="true" />
                     <div class="modal-body p-1">
                        <div class="form-group form-bg-grey rounded mb-3">
                           <div class="row">
                              <label class="col-5 control-label"><i class="ph-fill ph-gift align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.selpilot'); ?></label>
                              <div class="col-7">
                                 <div class="input-group input-group-lg">
                                    <select name="gift_id" id="gift_id" class="form-select bg-white">
                                       <option value="0" selected></option>
                                       <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                       <option value="<?php echo e($u->id); ?>"><?php echo e($u->ident.' | '.$u->name_private); ?></option>
                                       <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo app('translator')->get('common.close'); ?></button>
                  <button type="submit" class="btn btn-success" data-bs-dismiss="modal"><?php echo app('translator')->get('DSpecial::common.gift'); ?></button>
               </div>
            </form>
         </div>
      </div>
   </div>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php echo e($items->links('pagination.default')); ?>

<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/market/index.blade.php ENDPATH**/ ?>