<?php
  $speed_margin = 20;
  $msl = $pirep->acars->where('gs', '>', $speed_margin)->pluck('altitude_msl');
  $agl = $pirep->acars->where('gs', '>', $speed_margin)->pluck('altitude_agl');
  $spd = $pirep->acars->where('gs', '>', $speed_margin)->pluck('gs');
  $ias = $pirep->acars->where('gs', '>', $speed_margin)->pluck('ias');
  $terr = [];
  foreach ($pirep->acars->where('gs', '>', $speed_margin) as $a) {
    $terr[] = ($a->altitude_msl - $a->altitude_agl) > 0 ? $a->altitude_msl - $a->altitude_agl : 0;
  }
?>
<div name="chart_spd" id="chart_spd" class="p-1"></div>
<div name="chart_alt" id="chart_alt" class="p-1"></div>
<script src="<?php echo e(public_asset('SPTheme/js/plugins/apexcharts.min.js')); ?>"></script>
<script>
  var options = {
    series: [      
      {
         name: "<?php echo app('translator')->get('sptheme.speed'); ?> (GS)",
         data: <?php echo json_encode($spd); ?>

      },
      {
         name: "<?php echo app('translator')->get('sptheme.speed'); ?> (IAS)",
         data: <?php echo json_encode($ias); ?>

      }
    ],
    chart: {
      fontFamily: 'Roboto, sans-serif',
      height: 'auto',
      width: '100%',
      type: 'area',
      height: 350,
      stacked: true
    },
    colors: ['#008FFB', '#00E396', '#CED4DC'],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth'
    },
    fill: {
      type: 'gradient',
      gradient: {
         opacity: 0.6
      }
    },
    legend: {
      show: true,
      horizontalAlign: 'center',
      position: 'top',
    },
    tooltip: {
      x: {
        show: false,
      },
      fixed: {
        enabled: false,
        position: 'topLeft',
        offsetX: 60,
        offsetY: 80,
      }
    },
    xaxis: {
      labels: {
        show: false,
      },
      tooltip: {
        enabled: false,
      },
      decimalsInFloat: 0
    },
    yaxis: {
      forceNiceScale: true,
      decimalsInFloat: 0,
    }
  };
  var chart_spd = new ApexCharts(document.querySelector("#chart_spd"), options);
  chart_spd.render();

  var options = {
    series: [
      {
         name: "<?php echo app('translator')->get('sptheme.altitude'); ?> (MSL)",
         type: 'area',
         data: <?php echo json_encode($msl); ?>

      },
      {
         name: "<?php echo app('translator')->get('sptheme.altitude'); ?> (AGL)",
         data: <?php echo json_encode($agl); ?>

      },
      {
         name: "<?php echo app('translator')->get('sptheme.televation'); ?>",
         type: 'area',
         data: <?php echo json_encode($terr); ?>

      }
    ],
    chart: {
      fontFamily: 'Roboto, sans-serif',
      height: 'auto',
      width: '100%',
      type: 'area',
      height: 350,
      stacked: true
    },
    colors: ['#008FFB', '#00E396', '#CED4DC'],
    dataLabels: {
      enabled: false
    },
    stroke: {
      curve: 'smooth'
    },
    fill: {
      type: 'gradient',
      gradient: {
         opacity: 0.6
      }
    },
    legend: {
      show: true,
      horizontalAlign: 'center',
      position: 'top',
    },
    tooltip: {
      x: {
        show: false,
      },
      fixed: {
        enabled: false,
        position: 'topLeft',
        offsetX: 60,
        offsetY: 80,
      }
    },
    xaxis: {
      labels: {
        show: false,
      },
      tooltip: {
        enabled: false,
      },
      decimalsInFloat: 0
    },
    yaxis: {
      forceNiceScale: true,
      decimalsInFloat: 0,
    }
  };
  var chart_alt = new ApexCharts(document.querySelector("#chart_alt"), options);
  chart_alt.render();
</script>
<?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/analytics.blade.php ENDPATH**/ ?>