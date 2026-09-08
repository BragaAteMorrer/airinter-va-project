<?php $__env->startSection('title', __('sppassport::common.title')); ?>
<?php $__env->startSection('content'); ?>

<?php if(isset($sppassport_css)): ?>
<link rel="stylesheet" href="<?php echo e($sppassport_css); ?>">
<?php endif; ?>

<?php
    $rankImages = [
        asset('/SPTheme/images/placed_1.png'),
        asset('/SPTheme/images/placed_2.png'),
        asset('/SPTheme/images/placed_3.png'),
        asset('/SPTheme/images/placed_4.png'),
        asset('/SPTheme/images/placed_5.png'),
    ];
?>

<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-trophy align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.compare_with'); ?></h4>
                    <div class="form-group form-bg-grey rounded mb-3">
                        <div class="row">
                        <label class="col-1 control-label"><i class="ph-fill ph-info align-middle fs-20 me-1"></i></label>
                        <div class="col-11">
                            <div class="input-group input-group-lg">
                                <select name="user" id="compare-user" class="form-select">
                                    <option value=""><?php echo app('translator')->get('sppassport::common.select_pilot'); ?></option>
                                    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php if($u->id !== auth()->id()): ?>
                                            <option value="<?php echo e($u->id); ?>"><?php echo e($u->name_private); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>   
        </div>
    </div>   
</div>

<div class="row">
    <?php echo $__env->make('sppassport::stats', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>

<div class="row">
    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                <?php echo $__env->make('sppassport::map', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
                <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-map-trifold align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.visited_countries'); ?></h4>
                <div class="progress" role="progressbar" aria-valuenow="<?php echo e($progress); ?>" aria-valuemin="0" aria-valuemax="100" style="height: 22px;">
                    <div class="progress-bar bg-primary" style="width: <?php echo e($progress); ?>%">
                        <?php echo e($visitedCount); ?> / <?php echo e($totalCountries); ?>

                    </div>
                </div>
                <div class="text-center mt-4">
                    <ul class="list-inline d-flex flex-wrap justify-content-center gap-2">
                        <?php $__currentLoopData = $allCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $iso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $visited = in_array($iso, array_keys($countryData)); ?>
                            <li class="list-inline-item passport-flag">
                                <img data-iso="<?php echo e($iso); ?>" src="<?php echo e(asset('sppassport/flags')); ?>/<?php echo e(strtolower($iso)); ?>.svg"
                                     alt="<?php echo e($iso); ?>" class="rounded shadow-sm <?php echo e($visited ? 'passport-on' : 'passport-off'); ?>"
                                     width="64" height="48">
                                <div class="caption">
                                    <span class="caption-text"><?php echo e($iso); ?></span>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if(isset($recommendations)): ?>
<div class="row">
    <div class="col">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.recommended_destinations'); ?></h4>
                <p><?php echo app('translator')->get('sppassport::common.recommendation_intro'); ?></p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="text-center">
                            <img src="<?php echo e(asset('sppassport/flags/' . strtolower($country) . '.svg')); ?>" width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold"><?php echo e(strtoupper($country)); ?></div>
                            <a href="<?php echo e(route('passport.flights.country', ['country' => strtoupper($country)])); ?>" class="tooltiptop" title="<?php echo app('translator')->get('sppassport::common.flights'); ?>">
                                <?php echo app('translator')->get('sppassport::common.search'); ?>
                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
    <?php if(isset($rival)): ?>
    <div class="col">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-star align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.rival_of_the_week'); ?></h4>
                <p><?php echo app('translator')->get('sppassport::common.countries_to_overtake', ['count' => max(1, ($rival->countries ?? 0) - ($visitedCount ?? 0))]); ?></p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    <li class="text-center"><img src="<?php echo e($rival->user_country ? asset('sppassport/flags/' . strtolower($rival->user_country) . '.svg') : ''); ?>" width="48" height="36" class="rounded shadow-sm mb-1">
                    <div class="fw-bold"><?php echo e($rival->user_country ? strtoupper($rival->user_country) : ''); ?></div>
                    <a href="<?php echo e(route('frontend.users.show.public', [$rival->user_id])); ?>"><?php echo e($rival->user_name); ?></a>
                </ul>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-flag align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.top_visited_countries'); ?></h4>
                <p>
                    <?php echo app('translator')->get('sppassport::common.you_have_visited', [
                        'visited' => $visitedCount,
                        'total' => $totalCountries
                    ]); ?>
                </p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    <?php $__currentLoopData = $topCountries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="text-center">
                            <img src="<?php echo e(asset('sppassport/flags/' . strtolower($country['country']) . '.svg')); ?>"
                                 width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold"><?php echo e(strtoupper($country['country'])); ?></div>
                            <p class="mb-0"><?php echo e($country['flights']); ?> <?php echo app('translator')->get('sppassport::common.flights'); ?></p>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
</div> 
<?php endif; ?>

<?php if(isset($rareAirports)): ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.rare_destinations'); ?></h4>
                <p><?php echo app('translator')->get('sppassport::common.least_flown_destinations'); ?></p>
                <ul class="list-inline d-flex flex-wrap justify-content-center gap-5">
                    <?php $__currentLoopData = $rareAirports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airport): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="text-center">
                            <img src="<?php echo e(asset('sppassport/flags/' . strtolower($airport->country) . '.svg')); ?>" width="48" height="36" class="rounded shadow-sm mb-1">
                            <div class="fw-bold"><?php echo e(strtoupper($airport->country)); ?></div>
                            <a href="<?php echo e(route('frontend.airports.show', [$airport->icao])); ?>" class="tooltiptop" title="<?php echo e($airport->icao); ?>">
                                <?php echo e($airport->icao); ?>

                            </a>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        </div>
    </div>
</div> 
<?php endif; ?>

<?php if(isset($weeklyTop) && $weeklyTop->isNotEmpty()): ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-medal-military align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.rising_stars'); ?></h4>
                <table class="table table-striped table-hover  table-responsive">
                    <thead>
                        <tr>
                            <th><?php echo app('translator')->get('sppassport::common.rank'); ?></th>
                            <th><?php echo app('translator')->get('sppassport::common.pilot'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('sppassport::common.flights'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('sppassport::common.flight_time'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('sppassport::common.distance'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $weeklyTop; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pilot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <?php if($loop->iteration <= 5): ?>
                                    <img src="<?php echo e($rankImages[$loop->iteration - 1]); ?>" alt="<?php echo e($loop->iteration); ?>. Platz">
                                <?php else: ?>
                                    <?php echo e($loop->iteration); ?>.
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fi fi-<?php echo e(strtolower($pilot->country)); ?> shadow-img me-1" title="<?php echo e(strtolower($pilot->country)); ?>"></span>
                                <a href="<?php echo e(route('frontend.users.show.public', [$pilot->id])); ?>" class="tooltiptop" title="<?php echo e($loop->iteration); ?>."><?php echo e($pilot->name); ?></a>
                            </td>
                            <td class="text-center"><?php echo e($pilot->flights); ?> <?php echo app('translator')->get('sppassport::common.new_flights'); ?></td>
                            <td class="text-center"><?php echo \App\Support\Units\Time::minutesToTimeString($pilot->flight_time); ?></td>
                            <td class="text-center"><?php echo e($pilot->distance->local(0).' '.setting('units.distance')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if(isset($leaderboard)): ?>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
        <div class="card border">
            <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-medal-military align-middle fs-20 me-1"></i><?php echo app('translator')->get('sppassport::common.world_leaderboard'); ?></h4>
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th><?php echo app('translator')->get('sppassport::common.rank'); ?></th>
                            <th><?php echo app('translator')->get('sppassport::common.pilot'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('sppassport::common.countries'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('sppassport::common.flights'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('sppassport::common.flight_time'); ?></th>
                            <th class="text-center"><?php echo app('translator')->get('sppassport::common.distance'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <?php if($loop->iteration <= 5): ?>
                                    <img src="<?php echo e($rankImages[$loop->iteration - 1]); ?>" alt="<?php echo e($loop->iteration); ?>. Platz">
                                <?php else: ?>
                                    <?php echo e($loop->iteration); ?>.
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fi fi-<?php echo e($entry->user_country); ?> shadow-img me-1" title="<?php echo e($entry->user_country); ?>"></span>
                                <a href="<?php echo e(route('frontend.users.show.public', [$entry->user_id])); ?>" class="tooltiptop" title="<?php echo e($loop->iteration); ?>."><?php echo e($entry->user_name); ?></a>
                            </td>
                            <td class="text-center"><?php echo e($entry->countries); ?></td>
                            <td class="text-center"><?php echo e($entry->flights); ?></td>
                            <td class="text-center"><?php echo \App\Support\Units\Time::minutesToTimeString($entry->flight_time); ?></td>
                            <td class="text-center"><?php echo e($entry->distance->local(0).' '.setting('units.distance')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
           </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const compareSelect = new TomSelect('#compare-user', {
        placeholder: "<?php echo app('translator')->get('flights.search'); ?>",
        create: false,
        sortField: { field: 'text', direction: 'asc' },
        onChange(value) {
            if (value) {
                window.location = '/passport/compare/' + value;
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const countryData = <?php echo json_encode($countryData ?? [], 15, 512) ?>;
    const waitForMapAndMarkers = (callback) => {
        const interval = setInterval(() => {
            if (window.sppassportMap && window.airportMarkers) {
                clearInterval(interval);
                callback(window.sppassportMap, window.airportMarkers);
            }
        }, 300);
    };

    waitForMapAndMarkers((sppassportMap, airportMarkers) => {
        const countryLayers = window.countryLayers || {};
        document.addEventListener('click', (event) => {
            const flagElement = event.target.closest('.passport-flag');
            if (!flagElement) return;

            const iso = flagElement.querySelector('img')?.dataset.iso?.toUpperCase();
            if (!iso) return;

            const countryInfo = countryData[iso];
            if (!countryInfo?.first_airport) return;

            const marker = airportMarkers[countryInfo.first_airport.toUpperCase()];
            const layer = countryLayers[iso];
            if (!marker || !layer) return;

            sppassportMap.flyTo(marker.getLatLng(), 6, { duration: 1.2 });

            sppassportMap.once('moveend', () => {
                const popup = layer.getPopup();
                if (popup) {
                    popup.setLatLng(marker.getLatLng());
                    sppassportMap.openPopup(popup);
                    layer.setStyle({ weight: 3, color: '#1abc9c', fillOpacity: 0.7 });
                    setTimeout(() => {
                        layer.setStyle({ weight: 1, color: '#2ecc71', fillOpacity: 0.5 });
                    }, 2000);
                }
            });
        });
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('sppassport::layouts.frontend', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/modules/SPPassport/Providers/../Resources/views/index.blade.php ENDPATH**/ ?>