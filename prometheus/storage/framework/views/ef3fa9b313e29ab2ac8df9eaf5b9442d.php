<?php $__env->startSection('title', 'My Flight'); ?>
<?php $__env->startSection('content'); ?>
<div class="row">
   <div class="col-12 mb-3">
      <img src="<?php echo e(public_asset('/SPTheme/images/banner/34.jpg')); ?>" class="img-fluid card-img-top rounded rounded mb-3" width="1920" height="400" alt="<?php echo app('translator')->get('sptheme.banner'); ?>">
   </div>
</div>
<div class="row">
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-info align-middle fs-20 me-1"></i><?php echo app('translator')->get('sptheme.freeflightintro'); ?></h4>
            <p>After updating the details of your Personal Flight, simply click the <b>Update &amp; Proceed</b> button. Your flight will be updated, and your bid will be placed.</p>
            <p>Like other flights provided by our system, you can generate a SimBrief OFP for your Personal/Free Flight.</p>
            <p>When you're ready to fly, launch the ACARS software, click the <b>Search/Bids</b> button, and then select <b>Bids</b>. Your Personal/Free Flight will always be listed under Bids and will not appear in search results or on the website.</p>
            <p>Load your bidded flight and proceed with your operation. Safe flights!</p>
         </div>
      </div>
   </div>
   <div class="col-xxl-6 col-xl-6 col-lg-6 col-md-12 col-sm-12 mb-3">
      <div class="card border mb-0">
         <div class="card-body">
            <h4 class="mt-0 header-title border-bottom"><i class="ph-fill ph-compass-rose align-middle fs-20 me-1"></i><?php echo app('translator')->get('DSpecial::common.myflight'); ?></h4>
            <form class="form-horizontal" method="post" action="<?php echo e(route('DSpecial.freeflight_store')); ?>">
               <?php echo csrf_field(); ?>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-building align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.airline'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <?php if($airlines->count() > 1 && !$settings['pilot_company']): ?>
                           <select name="ff_airlineid" id="airline_selection" class="form-select select2" onchange="ChangeCallsignICAO()">
                              <?php $__currentLoopData = $airlines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $airline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <option <?php if($user->airline_id == $airline->id): ?> selected <?php endif; ?> value="<?php echo e($airline->id); ?>"><?php echo e('['.$airline->code.'] '.$airline->name); ?></option>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                           <?php else: ?>
                           <input type="hidden" name="ff_airlineid" id="airline_selection" value="<?php echo e($user->airline_id); ?>">
                           <span class="input-group-text" id="callsign_icao"><?php echo e(optional($user->airline)->icao); ?></span>
                           <?php endif; ?>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded my-3">
                  <div class="row">
                     <label for="name" class="col-5 control-label"><i class="ph-fill ph-hash align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.flightnumber'); ?></label>
                     <div class="col-7">
                        <input type="text" name="ff_number" id="ff_number" class="form-control bg-white border" value="<?php echo e($fflight->flight_number); ?>" min="0" max="9999">
                     </div>
                  </div>
               </div>
               <?php if(!$settings['sb_callsign']): ?>
               <div class="form-group form-bg-grey rounded m-0">
                  <div class="row">
                     <label for="name" class="col-5 control-label"><i class="ph-fill ph-headset align-middle fs-20 me-1"></i><?php echo app('translator')->get('flights.callsign'); ?> <?php echo app('translator')->get('DSpecial::common.optional'); ?></label>
                     <div class="col-7">
                        <input type="text" name="ff_callsign" id="callsign_icao" class="form-control bg-white border" value="<?php echo e($fflight->callsign ?? $user->callsign); ?>" placeholder="<?php echo e($user->id); ?>FF" maxlength="4">
                     </div>
                  </div>
               </div>
               <?php endif; ?>
               <div class="row">
                  <div class="col">
                     <div class="form-group form-bg-grey rounded my-3">
                        <div class="row">
                           <label for="name" class="col-5 control-label"><i class="ph-fill ph-airplane-takeoff align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.departure'); ?></label>
                           <div class="col-7">
                              <input type="text" name="ff_orig" class="form-control bg-white border" maxlength="4" value="<?php echo e($user->curr_airport_id ?? $user->home_airport_id); ?>" <?php if($settings['pilot_location']): ?> readonly <?php endif; ?>>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col">
                     <div class="form-group form-bg-grey rounded my-3">
                        <div class="row">
                           <label for="name" class="col-5 control-label"><i class="ph-fill ph-airplane-landing align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.arrival'); ?></label>
                           <div class="col-7">
                              <input type="text" name="ff_dest" class="form-control bg-white border" value="<?php echo e($fflight->arr_airport_id); ?>" maxlength="4">
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-file-code align-middle fs-20 me-1"></i>IATA Flight Type</label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select name="ff_iatatype" id="type_selection" class="form-select select2">
                              <?php $__currentLoopData = $flight_types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                              <?php if(in_array($key, ['C', 'D', 'E', 'H', 'K', 'L', 'N', 'O', 'P', 'T', 'Z'])): ?>
                              <option value="<?php echo e($key); ?>" <?php if($fflight->flight_type == $key): ?> selected <?php endif; ?>><?php echo e($name); ?></option>
                              <?php endif; ?>
                              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                           </select>
                        </div>
                     </div>
                  </div>
               </div>
               <?php if($aircraft->count()): ?>
               <div class="form-group form-bg-grey rounded mb-3">
                  <div class="row">
                     <label class="col-5 control-label"><i class="ph-fill ph-airplane align-middle fs-20 me-1"></i><?php echo app('translator')->get('common.aircraft'); ?> <?php echo app('translator')->get('DSpecial::common.optional'); ?></label>
                     <div class="col-7">
                        <div class="input-group input-group-lg">
                           <select id="aircraft_selection" name="ff_aircraft" class="form-control select2" onchange="CheckAircraftSelection()"></select>
                        </div>
                     </div>
                  </div>
               </div>
               <?php endif; ?>
               <?php if(filled($ff_cost)): ?>
               <i class="ph-fill ph-money text-danger float-start m-1 tooltiptop" title="Freeflight cost per save/edit: <?php echo e($ff_cost); ?>"></i>
               <?php endif; ?>
               <input type="hidden" name="ff_id" value="<?php echo e($fflight->id); ?>">
               <input type="hidden" name="user_id" value="<?php echo e($user->id); ?>">
               <input type="hidden" name="ff_owner" value="<?php if(Theme::getSetting('roster_ident')): ?> <?php echo e($user->ident.' - '); ?> <?php endif; ?> <?php echo e($user->name_private); ?>">
               <button id="form_proceed" class="btn btn-primary float-end" type="submit"><?php echo app('translator')->get('DSpecial::common.ff_button'); ?></button>
            </form>
         </div>
      </div>
   </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<?php echo \Illuminate\View\Factory::parentPlaceholder('scripts'); ?>
<script>
   $(".select2").select2({
      width: '100%'
   });

   // Define data for Select2 dropdowns
   var ICAO = <?php echo $icao; ?>;
   var FLEET = <?php echo $fleet_full; ?>;

   function ChangeCallsignICAO() {
      var airlineSelect = document.getElementById('airline_selection');
      var aircraftSelect = document.getElementById('aircraft_selection');
      var selectedAirline = airlineSelect.value;

      // Leere das Flugzeug-Auswahlfeld
      aircraftSelect.innerHTML = '';

      // Füge das Standard-Option hinzu
      var defaultOption = document.createElement('option');
      defaultOption.value = 0;
      defaultOption.text = 'Please select an aircraft';
      aircraftSelect.appendChild(defaultOption);

      // Filtere die Flugzeuge basierend auf der gewählten Airline
      FLEET.forEach(function(aircraft) {
         if (aircraft.text.startsWith(ICAO[selectedAirline])) {
               var option = document.createElement('option');
               option.value = aircraft.id;
               option.text = aircraft.text;
               aircraftSelect.appendChild(option);
         }
      });

      // Überprüfe nach dem Update der Flugzeugauswahl, ob der Button deaktiviert werden muss
      CheckAircraftSelection();
   }

   // Setze Event-Listener für die Auswahl der Airline
   document.getElementById('airline_selection').addEventListener('change', ChangeCallsignICAO);

   // Initialisiere die Flugzeugauswahl beim Laden der Seite
   window.onload = ChangeCallsignICAO;

   // Prüfe die Auswahl der Flugzeuge und aktualisiere den Button-Status
   function CheckAircraftSelection() {
      let selected_aircraft = document.getElementById('aircraft_selection').value;
      if (selected_aircraft == 0) {
         document.getElementById('form_proceed').classList.add('disabled');
      } else {
         document.getElementById('form_proceed').classList.remove('disabled');
      }
   }

   // Setze Event-Listener für das Auswahlfeld der Flugzeuge, um den Button zu überprüfen, falls die Auswahl geändert wird
   document.getElementById('aircraft_selection').addEventListener('change', CheckAircraftSelection);
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/jewe0363/prometheus/resources/views/layouts/SPTheme/modules/DisposableSpecial/freeflights/index.blade.php ENDPATH**/ ?>