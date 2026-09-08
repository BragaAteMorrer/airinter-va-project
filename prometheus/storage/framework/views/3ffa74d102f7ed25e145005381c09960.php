<?php $__env->startSection('title', 'Disposable Market'); ?>

<?php $__env->startSection('content'); ?>
  <div class="card border-blue-bottom" style="margin-left:5px; margin-right:5px; margin-bottom:5px;">
    <div class="content">
      <p>Market items managed here. Disposable Special Discord Webhook is used for notifications</p>
      <p>&nbsp;</p>
      <p><a href="https://github.com/FatihKoz" target="_blank">&copy; B.Fatih KOZ</a></p>
    </div>
  </div>
  <div class="row text-center" style="margin:10px;"><h4 style="margin: 5px; padding:0px;"><b>Disposable Market</b></h4></div>
  <div class="row" style="margin-left:5px; margin-right:5px;">
    <div class="card border-blue-bottom" style="padding:10px;">
      <form class="form" method="post" action="<?php echo e(route('DSpecial.market_store')); ?>">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="item_id" value="<?php echo e($item->id ?? ''); ?>" />
        <?php if($items->count()): ?>
          <div class="row" style="margin-bottom: 10px;">
            <div class="col-sm-5">
              <label class="pl-1 mb-1" for="item_selection">Select an Item for editing</label>
              <select id="item_selection" class="form-control select2" onchange="checkselection()">
                <option value="0">Please Select...</option>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($itm->id); ?>" <?php if($item && $itm->id == $item->id): ?> selected <?php endif; ?>><?php echo e($itm->id.' : '.$itm->name.' | Price: '.$itm->price.' | Owners: '.$itm->owners_count); ?> <?php if($itm->active): ?> (Active) <?php endif; ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
            </div>
            <div class="col-sm-3 text-left align-middle"><br>
              <a id="edit_link" style="visibility: hidden" href="<?php echo e(route('DSpecial.market_admin')); ?>" class="btn btn-primary pl-1 mb-1">Edit</a>
            </div>
            <div class="col-sm-2 text-right align-middle"><br>
              <a id="delete_link" style="visibility: hidden" href="<?php echo e(route('DSpecial.market_admin')); ?>" class="btn btn-danger pl-1 mb-1">Delete</a>
            </div>
          </div>
        <?php endif; ?>
        <div class="row" style="margin-bottom: 5px;">
          <div class="col-sm-4">
            <label class="pl-1 mb-1" for="item_name">Name <span class="small" title="Mandatory">*</span></label>
            <input name="item_name" type="text" class="form-control" placeholder="Mandatory" maxlength="250" value="<?php echo e($item->name ?? ''); ?>">
          </div>
          <div class="col-sm-1">
            <label class="pl-1 mb-1" for="item_price">Price <span class="small" title="Mandatory">*</span></label>
            <input name="item_price" type="number" step="0.01" class="form-control" value="<?php echo e($item->price ?? ''); ?>">
          </div>
          <div class="col-sm-1">
            <label class="pl-1 mb-1" for="item_limit">Limit <span class="small" title="Set 0 (zero) for unlimited owners">?</span></label>
            <input name="item_limit" type="number" step="1" class="form-control" value="<?php echo e($item->limit ?? 0); ?>">
          </div>
          <div class="col-sm-3">
            <?php if($airlines): ?>
              <div class="form-group">
                <label class="pl-1 mb-1" for="item_dealer">Dealer (Airline) <span class="small" title="Mandatory">*</span></label>
                <select name="item_dealer" class="form-control select2">
                  <option value="">Select An Airline</option>
                  <?php $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($airline->id); ?>" <?php if($item && $item->dealer_id === $airline->id): ?> selected <?php endif; ?>><?php echo e($airline->code.' | '.$airline->name); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            <?php endif; ?>
          </div>
          <div class="col-sm-3">
            <?php if($categories): ?>
              <div class="form-group">
                <label class="pl-1 mb-1" for="item_category">Category (Optional)</label>
                <select class="form-control select2" name="item_category">
                  <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php if(optional($item)->category == $key): ?> selected <?php endif; ?>><?php echo e($value); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <label class="pl-1 mb-1" for="item_description">Item Description (Public)</label>
            <textarea id="editor_desc" name="item_description" class="editor"><?php echo $item->description ?? ''; ?></textarea>
          </div>
          <div class="col-sm-6">
            <label class="pl-1 mb-1" for="item_notes">Item Notes (visible to Owners)</label>
            <textarea id="editor_notes" name="item_notes" class="editor"><?php echo $item->notes ?? ''; ?></textarea>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-6">
            <label class="pl-1 mb-1" for="item_image_url">Image URL or PATH</label>
            <input name="item_image_url" type="text" class="form-control mb-1" placeholder="Optional" maxlength="250" value="<?php echo e($item->image_url ?? ''); ?>">
          </div>
          <div class="col-sm-3">
            <label class="pl-1 mb-1" for="item_active">Active</label>
            <input type="hidden" name="item_active" value="0">
            <input name="item_active" type="checkbox" <?php if($item && $item->active == 1): ?> checked="true" <?php endif; ?> class="form-control mb-1" value="1">
          </div>
          <div class="col-sm-3">
            <label class="pl-1 mb-1" for="item_notifications">Notifications</label>
            <input type="hidden" name="item_notifications" value="0">
            <input name="item_notifications" type="checkbox" <?php if($item && $item->notifications == 1): ?> checked="true" <?php endif; ?> class="form-control mb-1" value="1">
          </div>
        </div>
        <div class="row" style="margin-bottom: 10px;">
          <div class="col-sm-12 text-right">
            <button class="btn btn-primary pl-1 mb-1" type="submit"><?php if($item && $item->id): ?> Update <?php else: ?> Save <?php endif; ?></button>
          </div>
        </div>
      </form>
    </div>
  </div>
  <?php if(filled($item) && $item->owners->count() > 0): ?>
    <div class="row" style="margin-left:5px; margin-right:5px;">
      <div class="card border-blue-bottom" style="padding:10px;">
        <b>Owners</b><hr>
        <?php $__currentLoopData = $item->owners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $owner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          &bull; <?php echo e($owner->ident.' | '.$owner->name_private); ?><br>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    </div>
  <?php endif; ?>
  <style>
    ::placeholder { color: indianred !important; opacity: 0.6 !important; }
    :-ms-input-placeholder { color: indianred !important; }
    ::-ms-input-placeholder { color: indianred !important; }
  </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
  <?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
  <script type="text/javascript">
    // Simple Selection With Dropdown Change
    // Also keep button hidden until a valid selection
    const $oldlink = document.getElementById("edit_link").href;

    function checkselection() {
      if (document.getElementById("item_selection").value === "0") {
        document.getElementById('edit_link').style.visibility = 'hidden';
        document.getElementById('delete_link').style.visibility = 'hidden';
      } else {
        document.getElementById('edit_link').style.visibility = 'visible';
        document.getElementById('delete_link').style.visibility = 'visible';
      }
      const selected = document.getElementById("item_selection").value;
      const editlink = "?itemedit=".concat(selected);
      const deletelink = "?itemdelete=".concat(selected);

      document.getElementById("edit_link").href = $oldlink.concat(editlink);
      document.getElementById("delete_link").href = $oldlink.concat(deletelink);
    }
  </script>
  <script src="<?php echo e(public_asset('assets/vendor/ckeditor4/ckeditor.js')); ?>"></script>
  <script>$(document).ready(function () { CKEDITOR.replace('editor_desc'); });</script>
  <script>$(document).ready(function () { CKEDITOR.replace('editor_notes'); });</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/DisposableSpecial/Providers/../Resources/views/admin/market.blade.php ENDPATH**/ ?>