<div class="card border">
   <div class="card-body">
      <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-cell-tower align-middle fs-20 me-1"></i>
         <?php if($network==='VATSIM'): ?>Pilots on <?php echo e($network); ?><?php endif; ?>
         <?php if($network==='IVAO'): ?>Pilots on <?php echo e($network); ?><?php endif; ?>
         <?php if($checks): ?>
         <span class="float-end small fw-normal">Online check enabled <?php if(isset($dltime)): ?> <?php echo e(' - ' . $dltime->diffForHumans()); ?> <?php endif; ?>
            <?php if($network==='VATSIM'): ?><a href="https://vatsim.net" title="International Online Aviation Network" target="_blank" class="float-end ms-2 tooltiptop"><img src="<?php echo e(public_asset('/SPTheme/images/vatsim-logo-xs.png')); ?>" alt="VATSIM Logo" width="30" height="30"></a><?php endif; ?>
            <?php if($network==='IVAO'): ?><a href="https://www.ivao.aero" title="International Virtual Aviation Organisation" target="_blank" class="float-end ms-2 tooltiptop"><img src="<?php echo e(public_asset('/SPTheme/images/ivao-logo-xs.png')); ?>" alt="IVAO Logo" width="30" height="30"></a><?php endif; ?>
         </span>
         <?php endif; ?>
      </h4>
      <?php if(isset($pilots)): ?>
      <?php if(count($pilots) > 0): ?>
      <table class="table table-hover table-striped mb-0">
         <thead>
            <tr>
               <th class="text-start">Name</th>
               <th><?php echo e($network); ?> ID</th>
               <th>Callsign</th>
               <th>ATC Flight Plan</th>
               <th>Server</th>
               <th class="text-end">Time Online</th>
            </tr>
         </thead>
         <tbody>
            <?php $__currentLoopData = $pilots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pilot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
               <td class="text-start">
                  <?php if(isset($pilot['user_id'])): ?>
                  <a href="<?php echo e(route('frontend.profile.show', [$pilot['user_id']])); ?>" target="_blank"><?php echo e($pilot['name']); ?></a>
                  <?php else: ?>
                  Deleted User
                  <?php endif; ?>
               </td>
               <td><?php echo e($pilot['network_id']); ?></td>
               <td><?php echo e($pilot['callsign']); ?></td>
               <td><?php echo e($pilot['flightplan'] ?? 'ATC not filed...'); ?></td>
               <td><?php echo e($pilot['server_name']); ?></td>
               <td class="text-end">
                  <?php if($checks): ?>
                  <?php if(!$pilot['airline']): ?>
                  <i class="fa-solid fa-clipboard-user text-danger mx-1" title="Airline not found!"></i>
                  <?php endif; ?>
                  <?php if(!$pilot['pirep']): ?>
                  <i class="fa-solid fa-clipboard-question text-danger mx-1" title="Pirep not found!"></i>
                  <?php elseif($pilot['pirep']): ?>
                  <i class="fa-solid fa-clipboard-check text-success mx-1" title="<?php echo e($pilot['pirep']->aircraft->icao.' | '.$pilot['pirep']->dpt_airport_id.' > '.$pilot['pirep']->arr_airport_id); ?>"></i>
                  <?php endif; ?>
                  <?php if($network === 'IVAO'): ?>
                  <?php if($pilot['vasyscheck'] === true): ?>
                  <i class="fa-solid fa-file text-success mx-1" title="IVAOVA/<?php echo e(Theme::getSetting('gen_ivao_icao')); ?> code found in FPL"></i>
                  <?php elseif($pilot['vasyscheck'] === false): ?>
                  <i class="fa-solid fa-file text-danger mx-1" title="IVAOVA/<?php echo e(Theme::getSetting('gen_ivao_icao')); ?> code NOT found in FPL!"></i>
                  <?php endif; ?>
                  <?php endif; ?>
                  <?php endif; ?>
                  <?php echo e(DB_ConvertMinutes($pilot['online_time'])); ?>

               </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         </tbody>
      </table>
      <?php else: ?>
      <div class="alert alert-info mb-0" role="alert"><i class="ph-fill ph-info align-middle fs-4 me-1"></i>No online flights on <?php echo e($network); ?> found.</div>
      <?php endif; ?>
      <?php elseif(isset($error)): ?>
      <div class="alert alert-danger mb-0" role="alert"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo e($error); ?></div>
      <?php endif; ?>
   </div>
</div><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableBasic/widgets/whazzup.blade.php ENDPATH**/ ?>