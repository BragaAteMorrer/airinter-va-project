<table class="table table-striped table-hover mb-0">
   <thead>
      <tr>
         <th class="text-left"><?php echo app('translator')->get('airports.ident'); ?></th>
         <th class="text-left"><?php echo app('translator')->get('airports.arrival'); ?></th>
         <th><?php echo app('translator')->get('flights.dep'); ?></th>
         <th><?php echo app('translator')->get('flights.arr'); ?></th>
      </tr>
   </thead>
   <?php $__currentLoopData = $flights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
   <tr>
      <td class="text-left">
         <a href="<?php echo e(route('frontend.flights.show', [$flight->id])); ?>" title="<?php echo app('translator')->get('pireps.flightinformations'); ?>" class="tooltiptop">
            <i class="ph-fill ph-hash align-middle fs-20 me-1"></i><?php echo e($flight->ident); ?>

         </a>
      </td>
      <td class="text-left"><?php echo e($flight->arr_airport->name); ?>

         (<a href="<?php echo e(route('frontend.airports.show', ['id' => $flight->arr_airport->icao])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e($flight->arr_airport->icao); ?></a>)
      </td>
      <td><?php echo e($flight->dpt_time); ?></td>
      <td><?php echo e($flight->arr_time); ?></td>
   </tr>
   <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</table><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/flights/table.blade.php ENDPATH**/ ?>