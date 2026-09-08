<?php $__env->startSection('title', trans_choice('common.pirep', 1).' '.$pirep->ident); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/21.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="200" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12">
      <div class="card border">
         <div class="card-body pb-0">
            <h4 class="mt-0 header-title border-bottom">
               <span class="float-end">
                  <?php if(optional($pirep->airline)->logo): ?>
                  <img src="<?php echo e($pirep->airline->logo); ?>" alt="<?php echo e($pirep->airline->name); ?>" width="90">
                  <?php else: ?>
                  <?php echo e($pirep->airline->name); ?>:
                  <?php endif; ?>
               </span>
               <span class="badge text-bg-warning ms-1 tooltiptop" title="<?php echo e(\App\Models\Enums\FlightType::label($pirep->flight_type)); ?>"><?php echo e($pirep->flight_type); ?></span>
               <span class="badge text-bg-primary ms-1"><b><?php echo e(trans_choice('common.flight', 1)); ?>:</b> <?php echo e(optional($pirep->airline)->code.' '.$pirep->flight_number); ?></span>
               <span class="badge text-bg-primary ms-1"><b><?php echo app('translator')->get('flights.callsign'); ?>:</b> <?php echo e(optional($pirep->airline)->callsign .' '. $pirep->callsign); ?></span>
            </h4>
            <div class="row">
               <div class="col">
                  <div class="card border card-default text-center">
                     <div class="card-header fw-bold"><i class="ph-fill ph-airplane-takeoff align-text-bottom fs-20 me-1"></i><?php echo app('translator')->get('pireps.status.departed'); ?> <?php if(filled($pirep->block_off_time)): ?> <?php echo e($pirep->block_off_time->format('H:i | l d.M.Y')); ?> <?php endif; ?> <span class="fi fi-<?php echo e(strtolower(optional($pirep->dpt_airport)->country)); ?> shadow-img mx-3"></span></div>
                     <div class="card-body">
                        <a href="<?php echo e(route('frontend.airports.show', ['id' => $pirep->dpt_airport_id])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e(optional($pirep->dpt_airport)->full_name ?? $pirep->dpt_airport_id); ?></a>
                     </div>
                  </div>
               </div>
               <div class="col">
                  <div class="card border card-default text-center">
                     <div class="card-header fw-bold"><i class="ph-fill ph-clock-countdown align-text-bottom fs-20 me-1"></i><?php echo app('translator')->get('pireps.flighttime'); ?> <i class="ph-fill ph-dot-outline mx-1"></i><?php if(!empty($pirep->distance)): ?> <i class="ph-fill ph-line-segments align-text-bottom fs-20 me-1"></i> <?php echo app('translator')->get('common.distance'); ?><?php endif; ?></div>
                     <div class="card-body">
                        <?php echo e(\Modules\SPTheme\Services\TimeService::convert($pirep->flight_time)); ?><?php if(!empty($pirep->distance)): ?> <i class="ph-fill ph-dot-outline mx-1"></i> <?php echo e($pirep->distance); ?> nmi <?php endif; ?>
                     </div>
                  </div>
               </div>
               <div class="col">
                  <div class="card border card-default text-center">
                     <div class="card-header fw-bold"><i class="ph-fill ph-airplane-landing align-text-bottom fs-20 me-1"></i><?php echo app('translator')->get('pireps.status.arrived'); ?> <?php if($pirep->block_on_time > $pirep->block_off_time): ?> <?php echo e($pirep->block_on_time->format('H:i | l d.M.Y')); ?> <?php endif; ?><span class="fi fi-<?php echo e(strtolower(optional($pirep->arr_airport)->country)); ?> shadow-img mx-3"></span></div>
                     <div class="card-body">
                        <a href="<?php echo e(route('frontend.airports.show', ['id' => $pirep->arr_airport_id])); ?>" title="<?php echo app('translator')->get('sptheme.oai'); ?>" class="tooltiptop"><?php echo e(optional($pirep->arr_airport)->full_name ?? $pirep->arr_airport_id); ?></a>
                     </div>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col">
                  <div class="card border">
                     <div class="card-body widget-desk row">
                        <div class="col">
                           <?php if($pirep->user?->avatar?->path): ?>
                              <img src="/uploads/<?php echo e($pirep->user->avatar->path); ?>" alt="Avatar" width="46" class="rounded-circle img-fluid">
                           <?php else: ?>
                              <img src="<?php echo e(asset('SPTheme/images/noavatar.png')); ?>" alt="No Avatar" width="46" class="rounded-circle img-fluid">
                           <?php endif; ?>
                        </div>
                        <div class="text-end col">
                           <h5 class="mt-0 mb-0 fw-bold">
                              <?php if(Auth::check()): ?>
                                 <span class="fi fi-<?php echo e(optional($pirep->user)->country); ?> shadow-img me-2" title="<?php echo app('translator')->get('common.country'); ?>"></span><?php echo e(optional($pirep->user->rank)->name); ?>, <a href="<?php echo e(route('frontend.profile.show', [$pirep->user_id])); ?>"><?php echo e(optional($pirep->user)->name); ?></a>
                              <?php else: ?>
                                 <span class="fi fi-<?php echo e(optional($pirep->user)->country); ?> shadow-img me-2" title="<?php echo app('translator')->get('common.country'); ?>"></span><?php echo e(optional($pirep->user->rank)->name); ?>, <a href="<?php echo e(route('frontend.profile.show', [$pirep->user_id])); ?>"><?php echo e(optional($pirep->user)->name_private); ?></a>
                              <?php endif; ?>
                              <?php if(!is_null($sp_settings['staff'])): ?>
                                 <?php if($pirep->user->hasRole($sp_settings['staff'])): ?> <span class="badge badge-warning ms-2 tooltiptop" title="<?php echo app('translator')->get('sptheme.staff'); ?>"><i class="ph-fill ph-wrench"></i></span> <?php endif; ?>
                              <?php endif; ?>
                           </h5>
                           <p class="mb-0"><?php echo app('translator')->get('sptheme.pic'); ?></p>
                        </div>
                     </div>
                  </div>
               </div>
               <?php if(!empty($pirep->distance)): ?>
               <div class="col">
                  <div class="card border">
                     <div class="card-body widget-desk">
                        <div class="text-end">
                           <h4 class="mt-0 mb-0">
                              <div class="progress" style="height: 20px;">
                                 <div class="progress-bar <?php if(blank($pirep->block_on_time)): ?> progress-animated <?php endif; ?> bg-warning" role="progressbar" style="width: <?php echo e($pirep->progress_percent); ?>%" aria-valuenow="<?php echo e($pirep->progress_percent); ?>" aria-valuemin="0" aria-valuemax="100"><?php echo e($pirep->progress_percent); ?>%</div>
                              </div>
                           </h4>
                           <p class="mb-0"><?php echo app('translator')->get('sptheme.routeflown'); ?>: <?php echo e($pirep->progress_percent); ?>%</p>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endif; ?>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="row">
   <?php if($pirep->state==1): ?>
   <div class="col">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold text-warning"><?php echo e(PirepState::label($pirep->state)); ?></h4>
               <p class="mb-0"><?php echo app('translator')->get('common.state'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-hourglass-medium text-warning"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <?php if($pirep->state==2): ?>
   <div class="col">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold text-success"><?php echo e(PirepState::label($pirep->state)); ?></h4>
               <p class="mb-0"><?php echo app('translator')->get('common.state'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-check-fat text-success"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <?php if($pirep->state==6): ?>
   <div class="col">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold text-danger"><?php echo e(PirepState::label($pirep->state)); ?></h4>
               <p class="mb-0"><?php echo app('translator')->get('common.state'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-x-circle text-danger"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <?php if($pirep->state !== PirepState::DRAFT): ?>
   <div class="col">
      <div class="card border">
         <div class="card-body widget-desk">
            <div class="text-end">
               <h4 class="mt-0 mb-0 fw-bold text-info"><?php echo e(PirepStatus::label($pirep->status)); ?></h4>
               <p class="mb-0"><?php echo app('translator')->get('common.status'); ?></p>
            </div>
            <div class="widget-icon">
               <i class="ph-fill ph-map-pin-line text-info"></i>
            </div>
            <div class="clearfix"></div>
         </div>
      </div>
   </div>
   <?php endif; ?>
</div>
<div class="row">
   <div class="col-xxl-8 col-xl-8 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-hard-drives align-middle fs-20 me-1"></i><?php echo app('translator')->get('pireps.flightinformations'); ?>
               <?php if(!empty($pirep->simbrief)): ?><button class="btn btn-info float-end" data-bs-toggle="modal" data-bs-target="#simbriefModal"><?php echo app('translator')->get('sptheme.simofp'); ?></button><?php endif; ?>
            </h4>
            <div class="tab-default">
               <div role="tabpanel">
                  <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                     <li class="nav-item" role="presentation">
                        <a class="nav-link active" data-bs-toggle="tab" href="#routemap" role="tab" aria-selected="true">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.routemap'); ?>
                        </a>
                     </li>
                     <?php if(Auth::check() && $pirep->acars && $pirep->acars->count() > 0): ?>
                     <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#analytics" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.analytics'); ?>
                        </a>
                     </li>
                     <?php endif; ?>
                     <?php if(Auth::check() && $pirep->fields && $pirep->fields->count() > 0 && $pirep->fields->count() <= 150): ?>
                        <li class="nav-item" role="presentation">
                        <a class="nav-link" data-bs-toggle="tab" href="#field" role="tab" aria-selected="false" tabindex="-1">
                           <i class="ph-fill ph-list align-middle fs-20 me-1"></i>PIREP <?php echo e(trans_choice('common.field', 2)); ?>

                        </a>
                        </li>
                     <?php endif; ?>
                     <?php if(count($pirep->acars_logs) > 0): ?>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link me-0" data-bs-toggle="tab" href="#flightlog" role="tab" aria-selected="false" tabindex="-1">
                              <i class="ph-fill ph-list align-middle fs-20 me-1"></i><?php echo app('translator')->get('pireps.flightlog'); ?>
                           </a>
                        </li>
                     <?php endif; ?>
                     <?php if(Auth::check() && (count($pirep->fares) > 0 || count($pirep->transactions) > 0)): ?>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link" data-bs-toggle="tab" href="#finance" role="tab" aria-selected="false" tabindex="-1">
                              <i class="ph-fill ph-list align-middle fs-20 me-1"></i>Finance
                           </a>
                        </li>
                     <?php endif; ?>
                     <?php if(Auth::check() && $pirep->comments->count() > 0): ?>
                        <li class="nav-item" role="presentation">
                           <a class="nav-link" data-bs-toggle="tab" href="#comments" role="tab" aria-selected="false" tabindex="-1">
                              <i class="ph-fill ph-list align-middle fs-20 me-1"></i>Comments
                           </a>
                        </li>
                     <?php endif; ?>
                  </ul>
                  <div class="tab-content text-muted">
                     <div class="tab-pane active show" id="routemap" role="tabpanel">
                        <?php echo $__env->make('pireps.map', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     </div>
                     <?php if(Auth::check() && $pirep->acars && $pirep->acars->count() > 0): ?>
                     <div class="tab-pane" id="analytics" role="tabpanel">
                        <?php echo $__env->make('pireps.analytics', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                     </div>
                     <?php endif; ?>
                     <?php if(Auth::check() && $pirep->fields && $pirep->fields->count() > 0 && $pirep->fields->count() <= 150): ?>
                        <div class="tab-pane table-responsive" id="field" role="tabpanel">
                        <div class="dz-scroll" style="max-height:800px;">
                           <table class="table table-striped table-hover mb-0">
                              <?php $__currentLoopData = $pirep->fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <tr>
                                 <td class="col-md-4 fw-bold"><?php echo e($field->name); ?></td>
                                 <td><?php echo e($field->value); ?></td>
                              </tr>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </table>
                        </div>
                  </div>
                  <?php endif; ?>
                  <?php if(count($pirep->acars_logs) > 0): ?>
                  <div class="tab-pane table-responsive" id="flightlog" role="tabpanel">
                     <div class="dz-scroll" style="max-height:800px;">
                        <table class="table table-striped table-hover mb-0">
                           <?php $__currentLoopData = $pirep->acars_logs->sortBy('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <tr>
                              <td class="col-md-4 fw-bold"><?php echo e(show_datetime($log->created_at)); ?></td>
                              <td><?php echo e($log->log); ?></td>
                           </tr>
                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </table>
                     </div>
                  </div>
                  <?php endif; ?>
                  <?php if(Auth::check() && (count($pirep->fares) > 0 || count($pirep->transactions) > 0)): ?>
                  <div class="tab-pane" id="finance" role="tabpanel">
                     <?php
                        $p_credit = $pirep->transactions->where('journal_id', $pirep->airline->journal->id)->sum('credit');
                        $p_debit = $pirep->transactions->where('journal_id', $pirep->airline->journal->id)->sum('debit');
                        $p_balance = $p_credit - $p_debit;
                     ?>
                     <table class="table table-striped table-hover mb-0">
                        <?php $__currentLoopData = $pirep->transactions->where('journal_id', $pirep->airline->journal->id); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                           <td class="col-md-4 fw-bold"><?php echo e($entry->memo); ?></td>
                           <td class="text-end"><?php if($entry->credit): ?><?php echo e(money($entry->credit, setting('units.currency'))); ?><?php endif; ?></td>
                           <td class="text-end"><?php if($entry->debit): ?><?php echo e(money($entry->debit, setting('units.currency'))); ?><?php endif; ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                           <td class="col-md-4 fw-bold">Summary</td>
                           <td class="text-success text-end fw-bold"><?php echo e(money($p_credit, setting('units.currency'))); ?></td>
                           <td class="text-danger text-end fw-bold"><?php echo e(money($p_debit, setting('units.currency'))); ?></td>
                        </tr>
                        <tr>
                           <td class="col-md-4 fw-bold">Profit / Loss</td>
                           <td></td>
                           <td class="text-end text-decoration-underline fs-18 fw-bold <?php if($p_balance > 0): ?> text-success <?php else: ?> text-danger <?php endif; ?>;"><?php echo e(money($p_balance, setting('units.currency'))); ?></td>
                        </tr>
                     </table>
                  </div>
                  <?php endif; ?>
                  <?php if(Auth::check() && $pirep->comments->count() > 0): ?>
                  <div class="tab-pane table-responsive" id="comments" role="tabpanel">
                     <div class="dz-scroll" style="max-height:800px;">
                        <table class="table table-striped table-hover mb-0">
                           <?php $__currentLoopData = $pirep->comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                           <tr>
                              <td class="col-md-4 fw-bold"><?php echo e($comment->created_at->format('d.M.Y H:i')); ?></td>
                              <td><?php echo e($comment->comment); ?></td>
                           </tr>
                           <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </table>
                     </div>
                  </div>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="col-xxl-4 col-xl-4 col-lg-6 col-md-12 col-sm-12 mb-3">
   <?php if(Auth::check() && filled($pirep->notes)): ?>
   <div class="row">
      <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
         <div class="card border mb-0">
            <div class="card-body">
               <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-note align-middle fs-20 me-1"></i><?php echo e(trans_choice('common.note', 1)); ?></h4>
               <div class="alert alert-info" role="alert"><?php echo e($pirep->notes); ?></div>
            </div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <?php if((count($pirep->fares) > 0 || count($pirep->transactions) > 0)): ?>
   <div class="row">
      <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mb-3">
         <div class="card border mb-0">
            <div class="card-body">
               <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-box-arrow-down align-middle fs-20 me-1"></i><?php echo e(trans_choice('pireps.fare', 2)); ?></h4>
               <ul class="list-icons mb-0">
                  <?php $__currentLoopData = $pirep->fares->sortBy('count', SORT_NATURAL); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fare): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <li class="d-flex justify-content-between align-items-center py-2">
                     <img src="<?php echo e(public_asset('/SPTheme/images/fares/seat_')); ?><?php echo e($fare->name); ?>.png" class="me-2"> <?php echo e(optional($fare)->name.' ('.optional($fare)->code.')'); ?>

                     <p class="mb-0 card-footer border-0 rounded fw-bold p-2">
                        <?php echo e($fare->count); ?>

                        <?php if($fare->type === 1): ?> <?php echo e(setting('units.weight')); ?> <?php else: ?> pax <?php endif; ?>
                     </p>
                  </li>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
               </ul>
            </div>
         </div>
      </div>
   </div>
   <?php endif; ?>
   <div class="row row-cols-2">
      <?php if(Auth::check()): ?>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e(optional($pirep->aircraft)->registration); ?> (<?php echo e(optional($pirep->aircraft)->icao); ?>)</h4>
                  <p class="mb-0"><?php echo app('translator')->get('common.aircraft'); ?></p>
               </div>
               <div class="widget-icon">
                  <i class="ph-fill ph-airplane-tilt"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>      
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e(PirepSource::label($pirep->source)); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.source'); ?></p>
               </div>
               <div class="widget-icon">
                  <i class="ph-fill ph-desktop-tower"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <?php if(filled($pirep->score)): ?>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e($pirep->score); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.score'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-trophy"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <?php endif; ?>
      <?php if($pirep->landing_rate != 0): ?>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e(number_format($pirep->landing_rate).' ft/min'); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.lrate'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-airplane-taxiing"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>
      <?php if($pirep->block_fuel || $pirep->fuel_used): ?>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e($pirep->block_fuel . ' ' . setting('units.fuel')); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.bfuel'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-airplane-takeoff"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e($pirep->fuel_used . ' ' . setting('units.fuel')); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.ufuel'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-gas-pump"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <?php endif; ?>
      <?php if($pirep->block_fuel && $pirep->fuel_used): ?>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e(round(($pirep->block_fuel->local() ?? 0) - ($pirep->fuel_used->local() ?? 0)) . ' ' . setting('units.fuel')); ?>

                  </h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.rfuel'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-airplane-landing"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <?php endif; ?>
      <?php if($pirep->source != 0 && filled($pirep->created_at) && filled($pirep->submitted_at)): ?>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e($pirep->created_at->format('H:i')); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.flightstart'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-clock"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e(\Modules\SPTheme\Services\TimeService::convert($pirep->created_at->diffInMinutes(($pirep->submitted_at)))); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.dutytime'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-clock-countdown"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <div class="col mb-3">
         <div class="card border mb-0">
            <div class="card-body widget-desk">
               <div class="text-end">
                  <h4 class="mt-0 mb-0 fw-bold"><?php echo e($pirep->submitted_at->format('H:i')); ?></h4>
                  <p class="mb-0"><?php echo app('translator')->get('sptheme.flightend'); ?></p>
               </div>
               <div class="widget-icon">
               <i class="ph-fill ph-clock-afternoon"></i>
               </div>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
      <?php endif; ?>
   </div>
</div>
<?php if(!empty($pirep->simbrief)): ?>
<div class="modal fade" id="simbriefModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="simbriefLabel" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
         <div class="modal-header pb-0">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-file-text fs-20 me-2"></i><?php echo app('translator')->get('sptheme.simofp'); ?></h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="ph-fill ph-minus text-danger fs-20"></i></button>
         </div>
         <div class="modal-body pt-0">
            <div class="card-body p-0">
               <div class="overflow-auto" style="height:600px;">
                  <?php echo $pirep->simbrief->xml->text->plan_html; ?>

               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?php echo app('translator')->get('common.close'); ?></button>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/pireps/show.blade.php ENDPATH**/ ?>