<?php if($metar): ?>
<table class="table table-striped table-hover mb-0">
   <tbody>
      <tr>
         <td class="col-3 fw-bold"><?php echo app('translator')->get('widgets.weather.conditions'); ?></td>
         <td><?php if($metar['category'] === 'VFR'): ?> VMC <?php elseif($metar['category'] === 'IFR'): ?> IMC <?php else: ?> <?php echo e($metar['category']); ?> <?php endif; ?></td>
      </tr>
      <tr>
         <td class="fw-bold"><?php echo app('translator')->get('widgets.weather.wind'); ?></td>
         <td>
            <?php if($metar['wind_speed'] < '1' ): ?>
               Calm
               <?php else: ?>
               <?php echo e($metar['wind_speed']); ?> kts <?php echo app('translator')->get('common.from'); ?> <?php echo e($metar['wind_direction_label']); ?>

               (<?php echo e($metar['wind_direction']); ?>&deg;)
               <?php endif; ?>
               <?php if($metar['wind_gust_speed']): ?>
               <?php echo app('translator')->get('widgets.weather.guststo'); ?> <?php echo e($metar['wind_gust_speed']); ?>

               <?php endif; ?>
               </td>
      </tr>
      <?php if($metar['visibility']): ?>
      <tr>
         <td class="col-3 fw-bold"><?php echo app('translator')->get('sptheme.visibility'); ?></td>
         <td><?php echo e($metar['visibility_report']); ?></td>
      </tr>
      <?php endif; ?>
      <?php if($metar['runways_visual_range']): ?>
      <tr>
         <td class="col-3 fw-bold"><?php echo app('translator')->get('sptheme.rwyvisual'); ?></td>
         <td>
            <?php $__currentLoopData = $metar['runways_visual_range']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rvr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <b><?php echo e('RWY'.$rvr['runway'].'; '); ?></b> <?php echo e($rvr['report']); ?><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
         </td>
      </tr>
      <?php endif; ?>
      <?php if($metar['present_weather_report'] && $metar['present_weather_report'] <> 'Dry'): ?>
         <tr>
            <td class="col-3 fw-bold"><?php echo app('translator')->get('sptheme.phenomena'); ?></td>
            <td><?php echo e($metar['present_weather_report']); ?></td>
         </tr>
         <?php endif; ?>
         <?php if($metar['clouds'] || $metar['cavok']): ?>
         <tr>
            <td class="col-3 fw-bold"><?php echo app('translator')->get('widgets.weather.clouds'); ?></td>
            <td>
               <?php if($unit_alt === 'ft'): ?>
               <?php echo e($metar['clouds_report_ft']); ?>

               <?php else: ?>
               <?php echo e($metar['clouds_report']); ?>

               <?php endif; ?>
               <?php if($metar['cavok'] == 1): ?>
               <?php echo app('translator')->get('sptheme.cvok'); ?>
               <?php endif; ?>
            </td>
         </tr>
         <?php endif; ?>
         <?php if($metar['temperature']): ?>
         <tr>
            <td class="col-3 fw-bold"><?php echo app('translator')->get('widgets.weather.temp'); ?></td>
            <td>
               <?php if($metar['temperature'][$unit_temp]): ?>
               <?php echo e($metar['temperature'][$unit_temp]); ?>

               <?php else: ?>
               0
               <?php endif; ?> &deg;<?php echo e(strtoupper($unit_temp)); ?>

               <?php if($metar['dew_point']): ?>
               , <?php echo app('translator')->get('widgets.weather.dewpoint'); ?>
               <?php if($metar['dew_point'][$unit_temp]): ?>
               <?php echo e($metar['dew_point'][$unit_temp]); ?>

               <?php else: ?>
               0
               <?php endif; ?> &deg;<?php echo e(strtoupper($unit_temp)); ?>

               <?php endif; ?>
               <?php if($metar['humidity']): ?>
               , <?php echo app('translator')->get('widgets.weather.humidity'); ?> <?php echo e($metar['humidity']); ?>&#37;
               <?php endif; ?>
            </td>
         </tr>
         <?php endif; ?>
         <?php if($metar['barometer']): ?>
         <tr>
            <td class="col-3 fw-bold"><?php echo app('translator')->get('sptheme.pressure'); ?></td>
            <td><?php echo e(number_format($metar['barometer']['hPa'])); ?> hPa / <?php echo e(number_format($metar['barometer']['inHg'], 2)); ?> inHg</td>
         </tr>
         <?php endif; ?>
         <?php if($metar['recent_weather_report']): ?>
         <tr>
            <td class="fw-bold"><?php echo app('translator')->get('sptheme.recentph'); ?></td>
            <td><?php echo e($metar['recent_weather_report']); ?></td>
         </tr>
         <?php endif; ?>
         <?php if($metar['runways_report']): ?>
         <tr>
            <td class="col-3 fw-bold"><?php echo app('translator')->get('sptheme.rwycond'); ?></td>
            <td>
               <?php $__currentLoopData = $metar['runways_report']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $runway): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
               <?php if($runway['runway'] == '88'): ?>
               <b><?php echo app('translator')->get('sptheme.allrwy'); ?>;</b>
               <?php else: ?>
               <b><?php echo e('RWY'.$runway['runway'].'; '); ?></b>
               <?php endif; ?>
               <?php echo e($runway['report']); ?>

               <br>
               <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </td>
         </tr>
         <?php endif; ?>
         <?php if($metar['remarks']): ?>
         <tr>
            <td class="col-3 fw-bold"><?php echo app('translator')->get('widgets.weather.remarks'); ?></td>
            <td><?php echo e($metar['remarks']); ?></td>
         </tr>
         <?php endif; ?>
         <tr>
            <td class="fw-bold"><?php echo app('translator')->get('widgets.weather.updated'); ?></td>
            <td><?php echo e($metar['observed_time']); ?> (<?php echo e($metar['observed_age']); ?>)</td>
         </tr>
         <tr>
            <td class="col-3 fw-bold"><?php echo app('translator')->get('common.metar'); ?></td>
            <td class="text-wrap">
               <?php if($metar): ?>
               <?php echo e($metar['raw']); ?>

               <?php else: ?>
               <?php echo app('translator')->get('widgets.weather.nometar'); ?>
               <?php endif; ?>
            </td>
         </tr>
         <tr>
            <td class="col-3 fw-bold">TAF</td>
            <td class="text-wrap">
               <?php if($taf): ?>
               <?php echo e($taf['raw']); ?>

               <?php else: ?>
               <?php echo app('translator')->get('widgets.weather.nometar'); ?>
               <?php endif; ?>
            </td>
         </tr>
   </tbody>
</table>
<?php else: ?>
<div class="alert alert-danger"><i class="ph-fill ph-warning-circle align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.nodata'); ?></div>
<?php endif; ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/widgets/weather.blade.php ENDPATH**/ ?>