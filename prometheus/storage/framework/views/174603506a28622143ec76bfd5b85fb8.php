<div class="card border-blue-bottom">
  <div class="content">
    <div class="row">
      <div class="col-xs-5">
        <div class="icon-big icon-info text-center">
          <i class="<?php echo e($icon); ?>"></i>
        </div>
      </div>
      <div class="col-xs-7">
        <div class="numbers">
          <p><?php echo e($type); ?></p>
          <?php if(isset($link)): ?>
            <a href="<?php echo e($link); ?>">
              <?php endif; ?>
              <?php echo e($pending); ?> pending
              <?php if(isset($link)): ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="footer">
      <hr>
      <?php if(isset($total)): ?>
        <div class="stats">
          <i class="ti-medall"></i> <?php echo e($total); ?> total
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/components/infobox.blade.php ENDPATH**/ ?>