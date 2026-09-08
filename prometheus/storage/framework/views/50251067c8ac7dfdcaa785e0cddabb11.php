<table class="table table-hover table-responsive" id="pages-table">
  <thead>
  <th>Name</th>
  <th></th>
  </thead>
  <tbody>
  <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($page->name); ?></td>
      <td class="text-right">
        <?php echo e(Form::open(['route' => ['admin.pages.destroy', $page->id], 'method' => 'delete'])); ?>

        <a href="<?php echo e(route('admin.pages.edit', [$page->id])); ?>"
           class='btn btn-sm btn-success btn-icon'><i class="fas fa-pencil-alt"></i></a>
        <?php echo e(Form::button('<i class="fa fa-times"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-danger btn-icon', 'onclick' => "return confirm('Are you sure?')"])); ?>

        <?php echo e(Form::close()); ?>

      </td>
    </tr>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>
<?php /**PATH /home/jewe0363/prometheus/resources/views/admin/pages/table.blade.php ENDPATH**/ ?>