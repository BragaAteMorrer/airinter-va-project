<?php $__env->startSection('title', __('common.profile')); ?>
<?php $__env->startSection('content'); ?>
<?php
$lastseen = new DateTimeImmutable($user->last_seen);
$ivao_id = optional($user->fields->firstWhere('name', $sp_settings['fieldivao']))->value;
$vatsim_id = optional($user->fields->firstWhere('name', $sp_settings['fieldvatsim']))->value;
$discord_id = optional($user->fields->firstWhere('name', $sp_settings['fielddiscord']))->value;
$Auth_ID = Auth::id();
// Addition Uebersetzung
$mtoh = 0; $mtoh = round($user->flight_time/60);
$flightSummary = $user->last_pirep ? __('sptheme.flight_summary', [
'flights' => $user->flights,
'flight_hours' => $mtoh,
'last_flight' => $user->last_pirep->submitted_at->diffForHumans(),
]) : __('sptheme.no_flight_summary');
?>
<div class="row">
   <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body d-flex">
            <div class="flex-fill text-center">
               <?php if($user->avatar == null): ?>
               <img src="<?php echo e(public_asset('SPTheme/images/noavatar.png')); ?>" class="rounded-circle bg-primary img-fluid" width="80" alt="<?php echo app('translator')->get('profile.avatar'); ?>">
               <?php else: ?>
               <img src="<?php echo e($user->avatar->url); ?>" class="rounded-circle bg-primary img-fluid" width="80" alt="<?php echo app('translator')->get('profile.avatar'); ?>">
               <?php endif; ?>
            </div>
            <?php if(is_null($vatsim_id)): ?>
            <div class="flex-fill text-center opacity-25">
               <img src="<?php echo e(public_asset('/SPTheme/images/vatsim_pirep_button.png')); ?>" alt="VATSIM Logo">
               <p class="mb-0 mt-2">ID: -</p>
            </div>
            <?php else: ?>
            <div class="flex-fill text-center ">
               <img src="<?php echo e(public_asset('/SPTheme/images/vatsim_pirep_button.png')); ?>" alt="VATSIM Logo" <?php if(is_null($vatsim_id)): ?> class="opacity-25" <?php endif; ?>>
               <?php if(!Auth::check()): ?>
               <p class="mb-0 mt-2">ID: <span class="align-text-top">******</span> <?php if(config('services.vatsim.enabled') && !$user->vatsim_id): ?> <i class="ph-fill ph-link-break text-danger fs-20 tooltiptop" title="Account not linked"></i> <?php elseif(config('services.vatsim.enabled')): ?> <i class="ph-fill ph-link text-success fs-20 tooltiptop" title="Account linked"></i> <?php endif; ?></p>
               <?php else: ?>
               <p class="mb-0 mt-2">ID: <a href="https://stats.vatsim.net/search_id.php?id=<?php echo e($vatsim_id); ?>" title="<?php echo e($sp_settings['fieldvatsim']); ?>" class="tooltiptop" target="_blank"><?php echo e($vatsim_id); ?></a> <?php if(config('services.vatsim.enabled') && !$user->vatsim_id): ?> <i class="ph-fill ph-link-break text-danger fs-20 tooltiptop" title="Account not linked"></i> <?php elseif(config('services.vatsim.enabled')): ?> <i class="ph-fill ph-link text-success fs-20 tooltiptop" title="Account linked"></i> <?php endif; ?></p>
               <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if(is_null($ivao_id)): ?>
            <div class="flex-fill text-center opacity-25">
               <img src="<?php echo e(public_asset('/SPTheme/images/ivao_pirep_button.png')); ?>" alt="IVAO Logo">
               <p class="mb-0 mt-2">ID: -</p>
            </div>
            <?php else: ?>
            <div class="flex-fill text-center">
               <img src="<?php echo e(public_asset('/SPTheme/images/ivao_pirep_button.png')); ?>" alt="IVAO Logo" <?php if(is_null($ivao_id)): ?> class="opacity-25" <?php endif; ?>>
               <?php if(!Auth::check()): ?>
               <p class="mb-0 mt-2">ID: <span class="align-text-top">******</span> <?php if(config('services.ivao.enabled') && !$user->ivao_id): ?> <i class="ph-fill ph-link-break text-danger fs-20 tooltiptop" title="Account not linked"></i> <?php elseif(config('services.ivao.enabled')): ?> <i class="ph-fill ph-link text-success fs-20 tooltiptop" title="Account linked"></i> <?php endif; ?></p>
               <?php else: ?>
               <p class="mb-0 mt-2">ID: <a href="https://www.ivao.aero/member.aspx?id=<?php echo e($ivao_id); ?>" class="tooltiptop" title="<?php echo e($sp_settings['fieldivao']); ?>" target="_blank"><?php echo e($ivao_id); ?></a> <?php if(config('services.ivao.enabled') && !$user->ivao_id): ?> <i class="ph-fill ph-link-break text-danger fs-20 tooltiptop" title="Account not linked"></i> <?php elseif(config('services.ivao.enabled')): ?> <i class="ph-fill ph-link text-success fs-20 tooltiptop" title="Account linked"></i> <?php endif; ?></p>
               <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if(is_null($discord_id)): ?>
            <div class="flex-fill text-center opacity-25">
               <img src="<?php echo e(public_asset('/SPTheme/images/discord_pirep_button.png')); ?>" alt="DISCORD Logo">
               <p class="mb-0 mt-2">ID: -</p>
            </div>
            <?php else: ?>
            <div class="flex-fill text-center">
               <img src="<?php echo e(public_asset('/SPTheme/images/discord_pirep_button.png')); ?>" alt="DISCORD Logo" <?php if(is_null($discord_id)): ?> class="opacity-25" <?php endif; ?>>
               <?php if(!Auth::check()): ?>
               <p class="mb-0 mt-2">ID: <span class="align-text-top">******</span> <?php if(config('services.discord.enabled') && !$user->discord_id): ?> <i class="ph-fill ph-link-break text-danger fs-20 tooltiptop" title="Account not linked"></i> <?php elseif(config('services.discord.enabled')): ?> <i class="ph-fill ph-link text-success fs-20 tooltiptop" title="Account linked"></i> <?php endif; ?></p>
               <?php else: ?>
               <p class="mb-0 mt-2">ID: <a href="https://www.ivao.aero/member.aspx?id=<?php echo e($discord_id); ?>" class="tooltiptop" title="<?php echo e($sp_settings['fielddiscord']); ?>" target="_blank"><?php echo e($discord_id); ?></a> <?php if(config('services.discord.enabled') && !$user->discord_id): ?> <i class="ph-fill ph-link-break text-danger fs-20 tooltiptop" title="Account not linked"></i> <?php elseif(config('services.discord.enabled')): ?> <i class="ph-fill ph-link text-success fs-20 tooltiptop" title="Account linked"></i> <?php endif; ?></p>
               <?php endif; ?>
            </div>
            <?php endif; ?>
         </div>
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom">
               <?php if(Auth::check()): ?>
               <span class="fi fi-<?php echo e($user->country); ?> shadow-img me-2" title="<?php echo app('translator')->get('common.country'); ?>"></span><?php echo e($user->rank->name); ?>, <?php echo e($user->name); ?>

               <?php else: ?>
               <span class="fi fi-<?php echo e($user->country); ?> shadow-img me-2" title="<?php echo app('translator')->get('common.country'); ?>"></span><?php echo e($user->rank->name); ?>, <?php echo e($user->name_private); ?>

               <?php endif; ?>
               <?php if(!is_null($sp_settings['staff'])): ?>
               <?php if($user->hasRole($sp_settings['staff'])): ?> <span class="badge badge-warning ms-2 tooltiptop" title="<?php echo app('translator')->get('sptheme.staff'); ?>"><i class="ph-fill ph-wrench"></i></span> <?php endif; ?>
               <?php endif; ?>
            </h4>
            <?php if($user->state == 0): ?>
            <div class="alert alert-info text-center tooltiptop" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.pending'); ?>"><i class="ph-fill ph-question fs-2 align-middle"></i> <?php echo app('translator')->get('common.pilot_id'); ?>: <?php echo e($user->ident); ?><?php if(filled($user->callsign)): ?> <?php echo e($user->callsign.' > '); ?> <?php endif; ?></div>
            <?php endif; ?>
            <?php if($user->state == 1): ?>
            <div class="alert alert-success text-center tooltiptop" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.active'); ?>"><i class="ph-fill ph-check-circle fs-2 align-middle"></i> <?php echo app('translator')->get('common.pilot_id'); ?>: <?php echo e($user->ident); ?><?php if(filled($user->callsign)): ?> <?php echo e($user->callsign.' > '); ?> <?php endif; ?></div>
            <?php endif; ?>
            <?php if($user->state == 2): ?>
            <div class="alert alert-danger text-center tooltiptop" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.rejected'); ?>"><i class="ph-fill ph-stop-circle fs-2 align-middle"></i> <?php echo app('translator')->get('common.pilot_id'); ?>: <?php echo e($user->ident); ?><?php if(filled($user->callsign)): ?> <?php echo e($user->callsign.' > '); ?> - <?php echo app('translator')->get('user.state.rejected'); ?> <?php endif; ?></div>
            <?php endif; ?>
            <?php if($user->state == 3): ?>
            <div class="alert alert-warning text-center tooltiptop" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.on_leave'); ?>"><i class="ph-fill ph-pause-circle fs-2 align-middle"></i> <?php echo app('translator')->get('common.pilot_id'); ?>: <?php echo e($user->ident); ?><?php if(filled($user->callsign)): ?> <?php echo e($user->callsign.' > '); ?> - <?php echo app('translator')->get('user.state.on_leave'); ?> <?php endif; ?></div>
            <?php endif; ?>
            <?php if($user->state == 4): ?>
            <div class="alert alert-danger text-center tooltiptop" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.suspended'); ?>"><i class="ph-fill ph-warning-circle fs-2 align-middle"></i> <?php echo app('translator')->get('common.pilot_id'); ?>: <?php echo e($user->ident); ?><?php if(filled($user->callsign)): ?> <?php echo e($user->callsign.' > '); ?> - <?php echo app('translator')->get('user.state.suspended'); ?> <?php endif; ?></div>
            <?php endif; ?>
            <?php if($user->state == 5): ?>
            <div class="alert alert-danger text-center tooltiptop" title="<?php echo app('translator')->get('common.status'); ?>: <?php echo app('translator')->get('user.state.deleted'); ?>"><i class="ph-fill ph-x-circle fs-2 align-middle"></i> <?php echo app('translator')->get('common.pilot_id'); ?>: <?php echo e($user->ident); ?><?php if(filled($user->callsign)): ?> <?php echo e($user->callsign.' > '); ?> - <?php echo app('translator')->get('user.state.deleted'); ?> <?php endif; ?></div>
            <?php endif; ?>
            <div class="table-responsive">
               <table class="table table-striped table-hover mb-0">
                  <tbody>
                     <?php if($user->id === $Auth_ID): ?>
                     <tr>
                        <td class="fw-bold text-end">API Key</td>
                        <td class="text-start">
                           <span id="apiKey_show" style="display: none">
                              <a href="javascript:void(0);" class="tooltiptop" title="<?php echo app('translator')->get('profile.dontshare'); ?>" onclick="apiKeyHide()"><i class="ph-fill ph-eye-slash fs-20 align-middle me-1"></i></a>
                              <?php echo e($user->api_key); ?>

                              <button class="btn btn-sm btn-secondary btn-success ms-2 tooltiptop" data-clipboard="true" data-clipboard-text="<?php echo e($user->api_key); ?>" title="<?php echo app('translator')->get('sptheme.copyclipboard'); ?>"><i class="ph-fill ph-copy"></i></button>
                           </span>
                           <span id="apiKey_hide">
                              <a href="javascript:void(0);" class="tooltiptop" title="<?php echo app('translator')->get('profile.dontshare'); ?>" onclick="apiKeyShow()"><i class="ph-fill ph-eye fs-20 align-middle me-1"></i></a>
                              <?php echo app('translator')->get('profile.apikey'); ?>
                           </span>
                        </td>
                     </tr>
                     <tr>
                        <td class="fw-bold text-end">E-mail</td>
                        <td class="text-start">
                           <span id="email_show" style="display: none">
                              <a href="javascript:void(0);" class="tooltiptop" title="<?php echo app('translator')->get('sptheme.notpublic'); ?>" onclick="emailHide()"><i class="ph-fill ph-eye-slash fs-20 align-middle me-1"></i></a>
                              <?php echo e($user->email); ?>

                           </span>
                           <span id="email_hide">
                              <a href="javascript:void(0);" class="tooltiptop" title="<?php echo app('translator')->get('sptheme.notpublic'); ?>" onclick="emailShow()"><i class="ph-fill ph-eye fs-20 align-middle me-1"></i></a>
                              <?php echo app('translator')->get('common.email'); ?>
                           </span>
                        </td>
                     </tr>
                     <?php endif; ?>
                     <tr>
                        <td class="text-end fw-bold">Rank</td>
                        <td>
                           <?php if(!empty($user->rank) && !empty($user->rank->image_url)): ?>
                           <img src="<?php echo e($user->rank->image_url); ?>" width="45" alt="<?php echo e($user->rank->name); ?>" title="<?php echo e($user->rank->name); ?>" class="img-fluid tooltipright">
                           <?php else: ?>
                           <?php echo e(optional($user->rank)->name); ?>

                           <?php endif; ?>
                        </td>
                     </tr>
                     <?php if($user->home_airport): ?>
                     <tr>
                        <td class="text-end fw-bold"><?php echo app('translator')->get('airports.home'); ?></td>
                        <td><a href="<?php echo e(route('frontend.airports.show', [$user->home_airport_id ?? ''])); ?>" class="badge badge-rounded badge-primary tooltiptop" title="<?php echo app('translator')->get('sptheme.oai'); ?>"><i class="ph-fill ph-house"></i><?php echo e($user->home_airport_id); ?></a></td>
                     </tr>
                     <?php endif; ?>
                     <tr>
                        <td class="fw-bold text-end"><?php echo app('translator')->get('common.timezone'); ?></td>
                        <td><?php echo e($user->timezone); ?></td>
                     </tr>
                     <tr>
                        <td class="fw-bold text-end"><?php echo app('translator')->get('sptheme.membersince'); ?></td>
                        <td><?php echo e($user->created_at->diffForHumans()); ?></td>
                     </tr>
                     <tr>
                        <td class="fw-bold text-end"><?php echo app('translator')->get('sptheme.lastflight'); ?></td>
                        <td><?php if($user->last_pirep): ?> <?php echo e($user->last_pirep->submitted_at->diffForHumans()); ?> <?php else: ?> Did not any flight <?php endif; ?></td>
                     </tr>
                     <tr>
                        <td class="fw-bold text-end"><?php echo app('translator')->get('sptheme.lastseen'); ?></td>
                        <td><?php echo e($lastseen->format('l, d.F Y')); ?></td>
                     </tr>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
   </div>
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border">
         <div class="card-body">
            <?php if(Auth::check() && $user->id === Auth::user()->id): ?>
            <div class="row">
               <?php if(isset($acars) && $acars === true): ?>
               <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
                  <a href="javascript:void(0);" title="ACARS Config" id="download_acars_config" class="btn btn-primary d-grid"><?php echo app('translator')->get('sptheme.acarsconfig'); ?></a>
               </div>
               <?php endif; ?>
               <?php if(isset($acars) && $acars === true): ?>
               <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
                  <?php else: ?>
                  <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
                     <?php endif; ?>
                     <a href="<?php echo e(route('frontend.profile.edit', [$user->id])); ?>" class="btn btn-warning d-grid"><?php echo app('translator')->get('common.edit'); ?></a>
                  </div>
               </div>
               <?php endif; ?>
               <?php if (app('laratrust')->ability('admin', 'admin-access')) : ?>
                  <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
                     <form class="form" method="post" action="<?php echo e(route('DSpecial.assignments_manual')); ?>">
                     <?php echo csrf_field(); ?>
                        <input type="hidden" name="curr_page" value="<?php echo e(url()->full()); ?>">
                        <input type="hidden" name="userid" value="<?php echo e($user->id); ?>">
                        <input type="hidden" name="resetmonth" value="true">
                        <a href="javascript:void(0);" title="Assign Monthly Flights" class="btn btn-danger d-grid" id="reassignMonth">Assign Monthly Flights</a>
                     </form>
                  </div>
               <?php endif; // app('laratrust')->ability ?>
               <img src="<?php echo e(public_asset('/SPTheme/images/banner/16.jpg')); ?>" class="img-fluid card-img-top rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
               <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-identification-card align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.biography'); ?></h4>
               <?php if(Auth::check()): ?>
               <p>
                  <?php echo __('sptheme.user_summary', [
                  'name' => $user->name,
                  'airline' => $user->airline->name,
                  'joined_date' => $user->created_at->format('F j, Y'),
                  'home_airport' => optional($user->home_airport)->full_name ?? $user->home_airport_id,
                  'current_airport' => $user->current_airport->name,
                  'pilot_id' => $user->ident,
                  'callsign' => filled($user->callsign) ? $user->callsign.' > ' : '',
                  'rank' => optional($user->rank)->name,
                  'flight_summary' => $flightSummary,
                  ]); ?>

               </p>
               <span class="text-muted"><i class="ph-fill ph-arrow-fat-lines-right"></i> <?php echo app('translator')->get('sptheme.thankful_message', ['name' => $user->name]); ?></span>
               <?php else: ?>
               <p>
                  <?php echo __('sptheme.user_summary', [
                  'name' => $user->name_private,
                  'airline' => $user->airline->name,
                  'joined_date' => $user->created_at->format('F j, Y'),
                  'home_airport' => optional($user->home_airport)->full_name ?? $user->home_airport_id,
                  'current_airport' => $user->current_airport->name,
                  'pilot_id' => $user->ident,
                  'callsign' => filled($user->callsign) ? $user->callsign.' > ' : '',
                  'rank' => optional($user->rank)->name,
                  'flight_summary' => $flightSummary,
                  ]); ?>

               </p>
               <span class="text-muted"><i class="ph-fill ph-arrow-fat-lines-right"></i> <?php echo app('translator')->get('sptheme.thankful_message', ['name' => $user->name_private]); ?></span>
               <?php endif; ?>
            </div>
         </div>
         <div class="row">
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
               <div class="card border">
                  <div class="card-body widget-desk">
                     <div class="text-end">
                        <h4 class="mt-0 mb-0 fw-bold"><?php echo e($user->flights); ?></h4>
                        <p class="mb-0"><?php echo e(trans_choice('common.flight', $user->flights)); ?></p>
                     </div>
                     <div class="widget-icon">
                        <i class="ph-fill ph-airplane-in-flight"></i>
                     </div>
                     <div class="clearfix"></div>
                  </div>
               </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
               <div class="card border">
                  <div class="card-body widget-desk">
                     <div class="text-end">
                        <h4 class="mt-0 mb-0 fw-bold"><?php echo \App\Support\Units\Time::minutesToTimeString($user->flight_time); ?></h4>
                        <p class="mb-0"><?php echo app('translator')->get('flights.flighthours'); ?></p>
                     </div>
                     <div class="widget-icon">
                        <i class="ph-fill ph-clock-countdown"></i>
                     </div>
                     <div class="clearfix"></div>
                  </div>
               </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
               <div class="card border">
                  <div class="card-body widget-desk">
                     <div class="text-end">
                        <h4 class="mt-0 mb-0 fw-bold"><a href="<?php echo e(route('frontend.airports.show', [$user->current_airport->id ?? ''])); ?>" class="tooltiptop" title="<?php echo app('translator')->get('sptheme.oai'); ?>"><?php echo e($user->current_airport->icao); ?></a></h4>
                        <p class="mb-0"><?php echo app('translator')->get('airports.current'); ?></p>
                     </div>
                     <div class="widget-icon">
                        <i class="ph-fill ph-globe-simple-x"></i>
                     </div>
                     <div class="clearfix"></div>
                  </div>
               </div>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
               <div class="card border">
                  <div class="card-body widget-desk">
                     <div class="text-end">
                        <h4 class="mt-0 mb-0 fw-bold"><?php echo \App\Support\Units\Time::minutesToHours($user->transfer_time); ?>h</h4>
                        <p class="mb-0"><?php echo app('translator')->get('profile.transferhours'); ?></p>
                     </div>
                     <div class="widget-icon">
                        <i class="ph-fill ph-clock-user"></i>
                     </div>
                     <div class="clearfix"></div>
                  </div>
               </div>
            </div>
            <?php if($user->flights > 0): ?>
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
               <?php echo app('arrilot.widget')->run('DBasic::JournalDetails', ['user' => $user->id, 'card' => true, 'limit' => 20]); ?>
            </div>
            <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-6 col-sm-12">
               <?php echo app('arrilot.widget')->run('DBasic::PersonalStats', ['disp' => 'full', 'user' => $user->id, 'type' => 'totdistance']); ?>
            </div>
            <?php endif; ?>
         </div>
      </div>
   </div>
   <?php if($user->flights > 0): ?>
   <div class="row">
      <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
         <div class="tab-default">
            <div role="tabpanel">
               <div class="card border">
                  <div class="card-body">
                     <ul class="nav nav-tabs nav-justified mb-0" role="tablist">
                        <?php if(filled($user->awards)): ?>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link active" data-bs-toggle="tab" href="#awards" role="tab" aria-selected="true" tabindex="-1">
                              <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('profile.your-awards'); ?>
                           </a>
                        </li>
                        <?php endif; ?>
                        <?php if($user->typeratings->count() > 0): ?>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link" data-bs-toggle="tab" href="#trating" role="tab" aria-selected="false" tabindex="-1">
                              <i class="ph-fill ph-list align-middle fs-20 me-1"></i>Type Rating
                           </a>
                        </li>
                        <?php endif; ?>
                        <?php if($user->flights > 0 && Auth::check()): ?>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link" data-bs-toggle="tab" href="#stats" role="tab" aria-selected="false" tabindex="-1">
                              <i class="ph-fill ph-list align-middle fs-20 me-1"></i>Statistics
                           </a>
                        </li>
                        <?php endif; ?>
                        <?php if($user->flights > 0): ?>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link" data-bs-toggle="tab" href="#pireps" role="tab" aria-selected="false" tabindex="-1">
                              <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo e(trans_choice('common.pirep', 2)); ?>

                           </a>
                        </li>
                        <?php endif; ?>
                     </ul>
                  </div>
               </div>
               <div class="tab-content text-muted">
                  <?php if(filled($user->awards)): ?>
                  <div class="tab-pane active show" id="awards" role="tabpanel">
                     <div class="card">
                        <div class="card-body p-0">
                           <?php $__currentLoopData = $user->awards->chunk(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $awards): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <div class="row">
                              <?php $__currentLoopData = $awards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $award): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <div class="col-md-4 text-center">
                                 <div class="card border m-3">
                                    <div class="card-body">
                                       <h4><?php echo e($award->name); ?></h4>
                                       <?php if($award->image_url): ?>
                                       <img src="<?php echo e($award->image_url); ?>" alt="<?php echo e($award->description); ?>" width="250" height="250">
                                       <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                       <?php echo e($award->description); ?><br><span class="text-muted"><?php echo e($award->pivot->created_at->format('d. F Y')); ?></span>
                                    </div>
                                 </div>
                              </div>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </div>
                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                     </div>
                  </div>
                  <?php endif; ?>
                  <?php if($user->typeratings->count() > 0): ?>
                  <div class="tab-pane" id="trating" role="tabpanel">
                     <div class="card">
                        <div class="card-body p-0">
                           <?php $__currentLoopData = $user->typeratings->chunk(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeratings): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <div class="row">
                              <?php $__currentLoopData = $typeratings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <div class="col-md-4 text-center">
                                 <div class="card border m-3">
                                    <div class="card-body">
                                       <h4><?php echo e($tr->name); ?></h4>
                                       <?php if($tr->image_url): ?>
                                       <img src="<?php echo e($tr->image_url); ?>" alt="<?php echo e($tr->description); ?>" width="250" height="250">
                                       <?php endif; ?>
                                    </div>
                                    <div class="card-footer">
                                       <?php echo e($tr->description); ?><br><span class="text-muted"><?php echo app('translator')->get('flights.code'); ?>: <?php echo e($tr->type); ?></span>
                                    </div>
                                 </div>
                              </div>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </div>
                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                     </div>
                  </div>
                  <?php endif; ?>
                  <?php if($user->flights > 0 && Auth::check()): ?>
                  <div class="tab-pane" id="stats" role="tabpanel">
                     <div class="row mb-0">
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12">
                           <?php echo app('arrilot.widget')->run('DBasic::PersonalStats', ['disp' => 'full', 'user' => $user->id, 'type' => 'totflight', 'period' => 15]); ?>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12">
                           <?php echo app('arrilot.widget')->run('DBasic::PersonalStats', ['disp' => 'full', 'user' => $user->id, 'type' => 'avgtime', 'period' => 15]); ?>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12">
                           <?php echo app('arrilot.widget')->run('DBasic::PersonalStats', ['disp' => 'full', 'user' => $user->id, 'type' => 'avgdistance', 'period' => 15]); ?>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12">
                           <?php echo app('arrilot.widget')->run('DBasic::PersonalStats', ['disp' => 'full', 'user' => $user->id, 'type' => 'avgfuel', 'period' => 15]); ?>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12">
                           <?php echo app('arrilot.widget')->run('DBasic::PersonalStats', ['disp' => 'full', 'user' => $user->id, 'type' => 'avglanding', 'period' => 15]); ?>
                        </div>
                        <div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12">
                           <?php echo app('arrilot.widget')->run('DBasic::PersonalStats', ['disp' => 'full', 'user' => $user->id, 'type' => 'avgscore', 'period' => 15]); ?>
                        </div>
                     </div>
                  </div>
                  <?php endif; ?>
                  <?php if($user->flights > 0): ?>
                  <div class="tab-pane" id="pireps" role="tabpanel">
                     <?php echo app('arrilot.widget')->run('DBasic::UserPireps', ['user' => $user->id, 'limit' => 50]); ?>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php else: ?>
   <div class="row">
      <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
         <div class="card border">
            <div class="card-body">
               <div class="alert alert-info" role="alert">
                  <i class="ph-fill ph-info align-middle fs-4 me-1"></i><?php echo app('translator')->get('sptheme.nopireps'); ?>
               </div>
            </div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <?php $__env->stopSection(); ?>
   <script src="<?php echo e(public_asset('SPTheme/js/plugins/clipboard.min.js')); ?>"></script>
   <?php $__env->startSection('scripts'); ?>
   <?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
   <script>
      $("#reassignMonth").on("click", function(event) {
         event.preventDefault();
         Swal.fire({
            toast: true,
            icon: "info",
            title: "<?php echo app('translator')->get('sptheme.reassign'); ?>",
            text: "<?php echo app('translator')->get('sptheme.reassign-t'); ?>",
            animation: true,
            position: 'top-end',
            showConfirmButton: true,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonText: "<?php echo app('translator')->get('sptheme.ok'); ?>",
            cancelButtonText: "<?php echo app('translator')->get('sptheme.cancel'); ?>",
            iconColor: 'white',
            customClass: {
            popup: 'colored-toast',
            confirmButton: 'btn btn-success mx-2',
            cancelButton: 'btn btn-warning mx-2',
            },
         }).then((result) => {
            if (result.isConfirmed) {
            $(event.target).closest('form').submit();
            }
         });
      });

      $("#download_acars_config").on("click", function() {
         Swal.fire({
            toast: true,
            icon: "info",
            title: "<?php echo app('translator')->get('sptheme.acarsconfig'); ?>",
            html: "<?php echo app('translator')->get('sptheme.acarsfile'); ?><br>/My Documents/vmsacars/profiles",
            animation: true,
            position: 'top-end',
            showConfirmButton: true,
            showCancelButton: true,
            showDenyButton: false,
            confirmButtonText: "<?php echo app('translator')->get('sptheme.ok'); ?>",
            cancelButtonText: "<?php echo app('translator')->get('sptheme.cancel'); ?>",
            iconColor: 'white',
            customClass: {
               popup: 'colored-toast',
               confirmButton: 'btn btn-success mx-2',
               cancelButton: 'btn btn-warning mx-2',
            },
         }).then((result) => {
            if (result.isConfirmed) {
               window.location = "<?php echo e(route('frontend.profile.acars')); ?>";
            }
         });
      });

      new ClipboardJS('[data-clipboard=true]').on('success', function(e) {
         e.clearSelection();

         const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            animation: true,
            iconColor: 'white',
            customClass: {
               popup: 'colored-toast',
            },
            timerProgressBar: true,
            didOpen: (toast) => {
               toast.onmouseenter = Swal.stopTimer;
               toast.onmouseleave = Swal.resumeTimer;
            }
         });

         Toast.fire({
            icon: "success",
            title: "<?php echo app('translator')->get('sptheme.apicopied'); ?>"
         });
      });

      function apiKeyShow() {
         document.getElementById("apiKey_show").style = "display:block";
         document.getElementById("apiKey_hide").style = "display:none";
      }

      function apiKeyHide() {
         document.getElementById("apiKey_show").style = "display:none";
         document.getElementById("apiKey_hide").style = "display:block";
      }

      function emailShow() {
         document.getElementById("email_show").style = "display:block";
         document.getElementById("email_hide").style = "display:none";
      }

      function emailHide() {
         document.getElementById("email_show").style = "display:none";
         document.getElementById("email_hide").style = "display:block";
      }
   </script>
   <?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/profile/index.blade.php ENDPATH**/ ?>