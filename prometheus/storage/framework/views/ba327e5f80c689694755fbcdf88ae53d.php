<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold"><?php echo e($progress); ?>%</h4>
                <p class="mb-0"><?php echo app('translator')->get('sppassport::common.world_coverage'); ?></p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div>
<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold"><?php echo \App\Support\Units\Time::minutesToTimeString($totalFlightMinutes); ?></h4>
                <p class="mb-0"><?php echo app('translator')->get('sppassport::common.flight_time'); ?></p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div>
<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold"><?php echo e($totalDistance->local(0).' '.setting('units.distance')); ?></h4>
                <p class="mb-0"><?php echo app('translator')->get('sppassport::common.total_distance'); ?></p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div>
<div class="col-xxl-3 col-xl-3 col-lg-3 col-md-3 col-sm-12">
    <div class="card border">
        <div class="card-body widget-desk">
            <div class="text-end">
                <h4 class="mt-0 mb-0 fw-bold"><?php echo e($uniqueAirports); ?></h4>
                <p class="mb-0"><?php echo app('translator')->get('sppassport::common.airports_visited'); ?></p>
            </div>
            <div class="widget-icon">
                <i class="ph-fill ph-info"></i>
            </div>
            <div class="clearfix"></div>
       </div>
    </div>
</div><?php /**PATH /home/jewe0363/prometheus/modules/SPPassport/Providers/../Resources/views/stats.blade.php ENDPATH**/ ?>