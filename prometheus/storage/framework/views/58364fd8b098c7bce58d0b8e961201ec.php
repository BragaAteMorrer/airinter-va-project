<?php $__env->startSection('title','Opérations'); ?>
<?php $__env->startSection('content'); ?>
<div class="ops-header compact">
    <div>
        <span class="eyebrow">CENTRE OPÉRATIONS AIR INTER</span>
        <h1>Salle opérations.</h1>
        <p>Vue de suivi pour la journée : départs, PIREP, télémétrie, rendez-vous et lignes actives.</p>
    </div>
    <div class="toolbar"><?php if (app('laratrust')->ability('admin','admin-access')) : ?><a class="button outline" href="<?php echo e(route('admin.promethee.catalogue')); ?>">Gérer le catalogue</a><?php endif; // app('laratrust')->ability ?><a class="button" href="<?php echo e(route('promethee.acars')); ?>">Ouvrir ACARS ↗</a></div>
</div>

<section class="control-strip">
    <article><span>Vols en cours</span><strong><?php echo e($activeFlights); ?></strong><small>ACARS / PIREP ouverts</small></article>
    <article><span>PIREP en attente</span><strong><?php echo e($pendingPireps); ?></strong><small>Validation admin</small></article>
    <article><span>Mois courant</span><strong><?php echo e($acceptedMonth); ?></strong><small>Vols acceptés</small></article>
    <article><span>Télémétrie jour</span><strong><?php echo e(number_format($telemetrySamples,0,',',' ')); ?></strong><small>Échantillons reçus</small></article>
    <article><span>Tarifs Prométhée</span><strong><?php echo e($pricingRules); ?></strong><small>Lignes tarifées</small></article>
</section>

<div class="ops-layout">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">DÉPARTS À SURVEILLER</span><h2>File programme</h2></div></div>
        <div class="ops-timeline">
            <?php $__empty_1 = true; $__currentLoopData = $upcoming; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $flight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article>
                    <time><?php echo e($flight->dpt_time ?: '--:--'); ?></time>
                    <strong><?php echo e($flight->ident); ?></strong>
                    <span><?php echo e($flight->dpt_airport_id); ?> → <?php echo e($flight->arr_airport_id); ?></span>
                    <small>LF <?php echo e($flight->load_factor ?? setting('flights.default_load_factor')); ?> %</small>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="empty">Aucun départ programmé.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">RETEX</span><h2>Derniers rapports</h2></div></div>
        <div class="report-feed">
            <?php $__empty_1 = true; $__currentLoopData = $recentPireps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pirep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article>
                    <strong><?php echo e($pirep->ident); ?></strong>
                    <span><?php echo e($pirep->dpt_airport_id); ?> → <?php echo e($pirep->arr_airport_id); ?></span>
                    <small><?php echo e(optional($pirep->submitted_at)->setTimezone('Europe/Paris')->format('d/m H:i')); ?> · <?php echo e($pirep->landing_rate ? number_format($pirep->landing_rate,0,',',' ') . ' ft/min' : 'LDG n/a'); ?></small>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="empty">Aucun rapport récent.</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="two-columns">
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">AXES ACTIFS</span><h2>Routes du mois</h2></div></div>
        <div class="route-board">
            <?php $__empty_1 = true; $__currentLoopData = $topRoutes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $route): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article><b><?php echo e($route->dpt_airport_id); ?></b><i></i><b><?php echo e($route->arr_airport_id); ?></b><span><?php echo e($route->total); ?> vols</span></article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="empty">Les routes apparaîtront après acceptation des PIREP.</p>
            <?php endif; ?>
        </div>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><span class="eyebrow">CALENDRIER</span><h2>Prochains rendez-vous</h2></div><a href="<?php echo e(route('promethee.calendar')); ?>">Calendrier ↗</a></div>
        <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <article class="event">
                <time><?php echo e(\Carbon\Carbon::parse($event->starts_at)->setTimezone('Europe/Paris')->format('d/m H:i')); ?></time>
                <h3><?php echo e($event->title); ?></h3>
                <p><?php echo e($event->departure ?: 'Interne'); ?> <?php echo e($event->arrival ? '→ '.$event->arrival : ''); ?></p>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="empty">Aucun rendez-vous programmé.</p>
        <?php endif; ?>
    </section>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('promethee::layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/promethee/modules/Promethee/Providers/../Resources/views/operations.blade.php ENDPATH**/ ?>