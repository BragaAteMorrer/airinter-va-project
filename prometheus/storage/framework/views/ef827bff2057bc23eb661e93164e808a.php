<?php $__env->startSection('title', __('promethee.dashboard_page.title')); ?>
<?php $__env->startSection('content'); ?>
<div class="ops-header">
    <div>
        <span class="eyebrow"><?php echo e(__('promethee.dashboard_page.eyebrow')); ?></span>
        <h1><?php echo e(__('promethee.dashboard_page.heading')); ?></h1>
        <p><?php echo e(__('promethee.dashboard_page.intro')); ?></p>
    </div>
    <div class="ops-clock">
        <span><?php echo e(now('Europe/Paris')->locale(app()->getLocale())->isoFormat('DD MMMM YYYY')); ?></span>
        <strong><?php echo e(now('Europe/Paris')->format('H:i')); ?></strong>
        <small><?php echo e(__('promethee.dashboard_page.paris_time')); ?></small>
    </div>
</div>

<section class="control-strip">
    <article><span><?php echo e(__('promethee.dashboard_page.active_flights')); ?></span><strong><?php echo e($activeFlights); ?></strong><small><?php echo e(__('promethee.dashboard_page.open_pireps')); ?></small></article>
    <article><span><?php echo e(__('promethee.dashboard_page.pending')); ?></span><strong><?php echo e($pendingPireps); ?></strong><small><?php echo e(__('promethee.dashboard_page.admin_queue')); ?></small></article>
    <article><span><?php echo e(__('promethee.dashboard_page.today')); ?></span><strong><?php echo e($acceptedToday); ?></strong><small><?php echo e(__('promethee.dashboard_page.accepted_flights')); ?></small></article>
    <article><span><?php echo e(__('promethee.dashboard_page.telemetry')); ?></span><strong><?php echo e(number_format($telemetrySamples,0,',',' ')); ?></strong><small><?php echo e(__('promethee.dashboard_page.daily_samples')); ?></small></article>
    <article><span><?php echo e(__('promethee.dashboard_page.your_logbook')); ?></span><strong><?php echo e($personal); ?></strong><small><?php echo e(__('promethee.dashboard_page.accepted_flights')); ?></small></article>
</section>

<section class="panel world-clocks" aria-label="<?php echo e(__('promethee.dashboard_page.world_clocks')); ?>">
    <div class="panel-heading"><div><span class="eyebrow"><?php echo e(__('promethee.dashboard_page.international_network')); ?></span><h2><?php echo e(__('promethee.dashboard_page.world_clocks')); ?></h2></div></div>
    <div class="control-strip"><?php $__currentLoopData = ['Paris'=>'Europe/Paris','Lisbonne'=>'Europe/Lisbon','Charlotte'=>'America/New_York','Londres'=>'Europe/London','Berlin'=>'Europe/Berlin','Antalya'=>'Europe/Istanbul','Tokyo'=>'Asia/Tokyo']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $city=>$zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><article data-world-clock="<?php echo e($zone); ?>"><span><?php echo e($city); ?></span><strong>--:--</strong><small><?php echo e($zone); ?></small></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
</section>

<div class="dispatch-grid">
    <section class="panel board-main departure-board">
        <div class="panel-heading">
            <div><span class="eyebrow"><?php echo e(__('promethee.dashboard_page.network_schedule')); ?></span><h2><?php echo e(__('promethee.dashboard_page.upcoming_flights')); ?></h2></div>
            <a href="<?php echo e(route('promethee.flights')); ?>"><?php echo e(__('promethee.dashboard_page.full_schedule')); ?> ↗</a>
        </div>
        <div class="flight-stack">
            <?php $__empty_1 = true; $__currentLoopData = $upcoming; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="dispatch-flight" style="--board-row: <?php echo e($loop->index); ?>">
                    <time class="board-cell" title="<?php echo e(optional($flight->next_departure_at)->locale(app()->getLocale())->isoFormat('dddd D MMMM [à] HH:mm')); ?>"><?php echo e($flight->display_departure_time ?: '----'); ?></time>
                    <span class="board-cell destination" title="<?php echo e($flight->arr_airport?->full_name ?? $flight->arr_airport_id); ?>"><?php echo e($flight->arr_airport_id); ?> · <?php echo e($flight->arr_airport?->location ?? '—'); ?></span>
                    <a class="board-cell flight-ident" href="<?php echo e(route('promethee.flights.show',$flight->id)); ?>"><?php echo e($flight->ident); ?></a>
                    <span class="board-cell duration"><?php echo e($flight->flight_time ? floor($flight->flight_time / 60).'h '.str_pad($flight->flight_time % 60,2,'0',STR_PAD_LEFT) : '—'); ?></span>
                    <span class="board-cell load"><?php echo e($flight->load_factor ?? setting('flights.default_load_factor')); ?> %</span>
                    <div><strong><?php echo e($flight->ident); ?></strong><span><?php echo e($flight->dpt_airport_id); ?> → <?php echo e($flight->arr_airport_id); ?></span></div>
                    <time title="<?php echo e(optional($flight->next_departure_at)->locale(app()->getLocale())->isoFormat('dddd D MMMM [à] HH:mm')); ?>"><?php echo e($flight->display_departure_time ?: '----'); ?></time>
                    <span class="load"><?php echo e($flight->load_factor ?? setting('flights.default_load_factor')); ?> %</span>
                    <a href="<?php echo e(route('promethee.flights.show',$flight->id)); ?>"><?php echo e(__('promethee.dashboard_page.prepare')); ?> ↗</a>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="empty"><?php echo e(__('promethee.dashboard_page.no_upcoming')); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <aside class="panel signal-panel">
        <span class="eyebrow"><?php echo e(__('promethee.dashboard_page.control_post')); ?></span>
        <h2><?php echo e(__('promethee.dashboard_page.quick_actions')); ?></h2>
        <a class="signal" href="<?php echo e(route('promethee.operations')); ?>"><b>OPS</b><span><?php echo e(__('promethee.dashboard_page.operations_room')); ?></span></a>
        <a class="signal" href="<?php echo e(route('promethee.acars')); ?>"><b>ACR</b><span>ACARS Prométhée</span></a>
        <a class="signal" href="<?php echo e(route('promethee.safety')); ?>"><b>SV</b><span><?php echo e(__('promethee.dashboard_page.safety_bulletin')); ?></span></a>
    </aside>
</div>

<div class="three-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow"><?php echo e(__('promethee.dashboard_page.monthly_network')); ?></span><h2><?php echo e(__('promethee.dashboard_page.most_flown_routes')); ?></h2></div></div>
        <div class="route-list">
            <?php $__empty_1 = true; $__currentLoopData = $topRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div><strong><?php echo e($route->dpt_airport_id); ?> → <?php echo e($route->arr_airport_id); ?></strong><span><?php echo e(__('promethee.dashboard_page.flights_count', ['count' => $route->total])); ?></span></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="empty"><?php echo e(__('promethee.dashboard_page.no_monthly_activity')); ?></p>
            <?php endif; ?>
        </div>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow"><?php echo e(__('promethee.dashboard_page.safety')); ?></span><h2><?php echo e(__('promethee.dashboard_page.current_bulletin')); ?></h2></div></div>
        <?php if($lastBulletin): ?>
            <div class="mini-bulletin"><strong><?php echo e($lastBulletin->month); ?></strong><span><?php echo e(__('promethee.dashboard_page.last_bulletin')); ?></span><a href="<?php echo e(route('promethee.safety',['month'=>$lastBulletin->month])); ?>"><?php echo e(__('promethee.dashboard_page.open')); ?> ↗</a></div>
        <?php else: ?>
            <div class="mini-bulletin"><strong><?php echo e(__('promethee.dashboard_page.draft')); ?></strong><span><?php echo e(__('promethee.dashboard_page.no_bulletin')); ?></span><a href="<?php echo e(route('promethee.safety')); ?>"><?php echo e(__('promethee.dashboard_page.calculate')); ?> ↗</a></div>
        <?php endif; ?>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow"><?php echo e(__('promethee.dashboard_page.your_progress')); ?></span><h2><?php echo e(__('promethee.dashboard_page.pilot_logbook')); ?></h2></div></div>
        <div class="mini-bulletin"><strong><?php echo e($personal); ?></strong><span><?php echo e(__('promethee.dashboard_page.validated_flights')); ?></span><a href="<?php echo e(route('promethee.profile')); ?>"><?php echo e(__('promethee.view_my_profile')); ?> ↗</a></div>
    </section>
</div>

<section class="panel">
    <div class="panel-heading"><div><span class="eyebrow"><?php echo e(__('promethee.dashboard_page.latest_accepted_pireps')); ?></span><h2><?php echo e(__('promethee.dashboard_page.recent_activity')); ?></h2></div><a href="<?php echo e(route('promethee.operations')); ?>"><?php echo e(__('promethee.dashboard_page.view_operations_room')); ?> ↗</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th><?php echo e(__('promethee.flight')); ?></th><th><?php echo e(__('promethee.route')); ?></th><th><?php echo e(__('promethee.aircraft')); ?></th><th><?php echo e(__('promethee.dashboard_page.time')); ?></th><th><?php echo e(__('promethee.landing')); ?></th><th><?php echo e(__('promethee.dashboard_page.airport')); ?></th></tr></thead>
            <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $recentPireps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pirep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><strong><?php echo e($pirep->ident); ?></strong></td>
                    <td><?php echo e($pirep->dpt_airport_id); ?> → <?php echo e($pirep->arr_airport_id); ?></td>
                    <td><?php echo e($pirep->aircraft?->registration ?? '—'); ?></td>
                    <td><?php echo e($pirep->flight_time ? floor($pirep->flight_time / 60).'h '.str_pad($pirep->flight_time % 60,2,'0',STR_PAD_LEFT) : '—'); ?></td>
                    <td><?php echo e($pirep->landing_rate ? number_format($pirep->landing_rate,0,',',' ') . ' ft/min' : '—'); ?></td>
                    <td><?php echo e(optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m H:i')); ?></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="6"><?php echo e(__('promethee.dashboard_page.no_accepted_pireps')); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('promethee::layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/promethee/modules/Promethee/Providers/../Resources/views/dashboard.blade.php ENDPATH**/ ?>