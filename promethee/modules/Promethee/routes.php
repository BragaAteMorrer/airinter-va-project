<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\LanguageController;
use Modules\Promethee\Http\PortalController;
use Modules\Promethee\Http\PirepController;
use Modules\Promethee\Http\FlightProgrammeController;
use Modules\Promethee\Http\DownloadsController;
use Modules\Promethee\Http\MissionsController;
use Modules\Promethee\Http\CalendarController;
use Modules\Promethee\Http\EconomyController;
use Modules\Promethee\Http\CompanyController;
use Modules\Promethee\Http\MaintenanceAdminController;
use Modules\Promethee\Http\RegionalOperationsAdminController;
use Modules\Promethee\Http\MinitelController;
use Modules\Promethee\Http\MinitelOperationsController;
use Modules\Promethee\Http\TelemetryController;
use Modules\Promethee\Http\AcarsOperationsController;
use Modules\Promethee\Http\AcarsConfigurationController;
use Modules\Promethee\Http\OperationsV1Controller;
use Modules\Promethee\Http\HermesReleaseController;
use Modules\Promethee\Http\SimBriefCallbackController;
use Modules\Promethee\Http\DatalinkController;
use Modules\Promethee\Http\SopController;
use Modules\Promethee\Http\AutomationController;
use Modules\Promethee\Http\PresenceController;
use Modules\Promethee\Http\DispatchDeskController;
use Modules\Promethee\Http\CrmController;
use Modules\Promethee\Http\AircraftConfigurationAdminController;
use Modules\Promethee\Http\EngineMaintenanceAdminController;
use Modules\Promethee\Http\RealSimulatorCertificationController;
use Modules\Promethee\Http\Api\AircraftConfigurationController;
use Modules\Promethee\Http\Api\AcarsSimBriefController;
use Modules\Promethee\Http\Api\AcarsSessionController;

// Browsers request this conventional path even though the branded icon lives
// with the static Promethee assets.
Route::redirect('/favicon.ico', '/promethee-assets/favicon.png');
// Retain the Promethee URL, but use phpVMS' one canonical implementation.
Route::get('/language/{lang}', [LanguageController::class, 'switchLang'])
    ->middleware('web')->name('promethee.language');
Route::get('/occ', [PortalController::class, 'occ'])->middleware('web')->name('promethee.occ');
// Public flight reports replace the legacy phpVMS report screen. The report
// remains readable without an account, just as the former public URL was.
Route::get('/pireps/{id}', [PirepController::class, 'pirep'])->middleware(['web','auth'])->name('promethee.pireps.show');
Route::delete('/pireps/{id}', [PirepController::class, 'deleteOwnPirep'])->middleware(['web','auth'])->name('promethee.pireps.delete');
Route::post('/pireps/{id}/repeat', [PirepController::class, 'repeatPirep'])->middleware(['web','auth'])->name('promethee.pireps.repeat');
// Backward-compatible name used by the aircraft history view.
Route::get('/pirep/{id}', [PirepController::class, 'pirep'])->middleware(['web','auth'])->name('promethee.pirep');
Route::middleware('web')->prefix('public')->name('promethee.public.')->group(function () {
    Route::get('/pilots', [PortalController::class, 'publicPilots'])->name('pilots');
    Route::get('/pireps', [PirepController::class, 'publicPireps'])->middleware('auth')->name('pireps');
    Route::get('/pireps/mine', [PirepController::class, 'myPireps'])->middleware('auth')->name('pireps.mine');
    Route::get('/live', [PortalController::class, 'publicLive'])->name('live');
    Route::get('/live-data', [PortalController::class, 'liveData'])->name('live.data');
});
// Native Promethee fleet directory. Data comes directly from phpVMS models.
Route::get('/fleet', [CompanyController::class, 'fleet'])->middleware('web')->name('promethee.fleet');
// Legacy bookmarks remain redirects only; no Promethee navigation depends on them.
Route::redirect('/dfleet', '/fleet', 301)->middleware('web');

Route::middleware(['web','auth'])->name('promethee.')->group(function () {
    Route::get('/', [PortalController::class,'dashboard'])->name('dashboard');
    Route::post('/flights/itineraries/reserve', [FlightProgrammeController::class, 'reserveItinerary'])
        ->name('flights.itineraries.reserve');
    Route::get('/departure-board-data', [PortalController::class,'departureBoardData'])->name('departure-board.data');
    Route::prefix('minitel')->name('minitel.')->group(function () {
        Route::get('/bootstrap', [MinitelController::class, 'bootstrap'])->name('bootstrap');
        Route::get('/flights', [MinitelController::class, 'flights'])->name('flights');
        Route::get('/routes', [MinitelController::class, 'routes'])->name('routes');
        Route::get('/fleet', [MinitelController::class, 'fleet'])->name('fleet');
        Route::get('/pilots', [MinitelController::class, 'pilots'])->name('pilots');
        Route::get('/calendar', [MinitelController::class, 'calendar'])->name('calendar');
        Route::get('/profile', [MinitelController::class, 'profile'])->name('profile');
        Route::get('/operations', [MinitelOperationsController::class, 'index'])->name('operations');
        Route::get('/operations/search-flights', [MinitelOperationsController::class, 'searchFlights'])->name('operations.search-flights');
        Route::post('/flights/{flight}/reserve', [MinitelOperationsController::class, 'reserve'])->name('operations.reserve');
        Route::get('/operations/{operation}', [MinitelOperationsController::class, 'show'])->name('operations.show');
        Route::get('/operations/{operation}/aircraft', [MinitelOperationsController::class, 'aircraft'])->name('operations.aircraft');
        Route::put('/operations/{operation}/aircraft', [MinitelOperationsController::class, 'selectAircraft'])->name('operations.aircraft.select');
        Route::get('/operations/{operation}/briefing', [MinitelOperationsController::class, 'briefing'])->name('operations.briefing');
        Route::get('/operations/{operation}/dispatch', [MinitelOperationsController::class, 'dispatch'])->name('operations.dispatch');
        Route::post('/operations/{operation}/pirep', [MinitelOperationsController::class, 'prefilePirep'])->name('operations.pirep');
        Route::post('/operations/{operation}/simbrief/redirect', [MinitelOperationsController::class, 'simbriefRedirect'])->name('operations.simbrief.redirect');
        Route::post('/operations/{operation}/simbrief/account/import', [MinitelOperationsController::class, 'simbriefImportAccount'])->name('operations.simbrief.import-account');
        Route::post('/operations/{operation}/simbrief/session', [MinitelOperationsController::class, 'simbriefSession'])->name('operations.simbrief.session');
        Route::post('/operations/{operation}/simbrief/import', [MinitelOperationsController::class, 'simbriefImport'])->name('operations.simbrief.import');
    });
    Route::get('/profile', [PortalController::class,'profile'])->name('profile');
    Route::get('/profile/edit', [PortalController::class,'editProfile'])->name('profile.edit');
    Route::patch('/profile', [PortalController::class,'updateProfile'])->name('profile.update');
    Route::get('/passport', [PortalController::class,'passport'])->name('passport');
    Route::get('/bookings', [PortalController::class,'bookings'])->name('bookings');
    Route::delete('/bookings/{bid}', [PortalController::class,'cancelBooking'])->name('bookings.cancel');
    Route::delete('/bookings/{bid}/pirep', [PortalController::class,'deleteBookingPirep'])->name('bookings.pirep.delete');
    Route::get('/downloads', [DownloadsController::class,'downloads'])->name('downloads');
    Route::get('/downloads/categories/{category}', [DownloadsController::class,'downloadCategoryPage'])->where('category','acars|fleet|airports|documents')->name('downloads.category');
    Route::get('/downloads/{file}', [DownloadsController::class,'download'])->name('downloads.download');
    Route::get('/missions', [MissionsController::class,'missions'])->name('missions');
    Route::post('/missions/{id}/reserve', [MissionsController::class,'reserveMission'])->name('missions.reserve');
    Route::delete('/missions/{id}/reservation', [MissionsController::class,'cancelMissionReservation'])->name('missions.cancel');
    Route::get('/assignments', [PortalController::class,'assignments'])->name('assignments');
    Route::get('/shop', [PortalController::class,'shop'])->name('shop');
    Route::post('/shop/{id}/buy', [PortalController::class,'buyShopItem'])->name('shop.buy');
    Route::get('/transfers', [PortalController::class,'transfers'])->name('transfers');
    Route::get('/jumpseat', [PortalController::class,'jumpseat'])->name('jumpseat');
    Route::post('/jumpseat', [PortalController::class,'requestJumpseat'])->name('jumpseat.buy');
    Route::get('/operations', [PortalController::class,'operations'])->name('operations');
    // Native Promethee pages backed directly by phpVMS data/models.
    Route::get('/airlines', [CompanyController::class, 'airlines'])->name('airlines');
    Route::get('/documents', [DownloadsController::class, 'documents'])->name('documents');
    Route::get('/my-documents', [DownloadsController::class, 'myDocuments'])->name('documents.mine');
    Route::get('/documents/{file}', [DownloadsController::class, 'document'])->name('documents.show');
    Route::get('/documents/{file}/content', [DownloadsController::class, 'documentContent'])->name('documents.content');
    Route::get('/finances', [CompanyController::class, 'finances'])->name('finances');
    Route::get('/maintenance', [CompanyController::class, 'maintenance'])->name('maintenance');
    Route::get('/aircraft/{registration}', [CompanyController::class, 'aircraftDetail'])->name('aircraft.show');

    // Compatibility redirects for historic DisposableBasic bookmarks only.
    Route::redirect('/dairlines', '/airlines', 301);
    Route::redirect('/dcompany', '/airlines', 301)->name('company');
    Route::redirect('/dmaintenance', '/maintenance', 301);
    Route::get('/daircraft/{registration}', fn (string $registration) => redirect('/aircraft/'.rawurlencode($registration), 301));
    Route::get('/live', [PortalController::class,'live'])->name('live');
    Route::get('/live-data', [PortalController::class,'liveData'])->name('live.data');
    Route::get('/calendar', [CalendarController::class,'calendar'])->name('calendar');
    Route::post('/calendar/{id}/rsvp', [CalendarController::class,'rsvpEvent'])->name('calendar.rsvp');
    Route::get('/pilots', [PortalController::class,'pilots'])->name('pilots');
    Route::get('/pilots/{id}', [PortalController::class,'pilot'])->name('pilots.show');
    Route::get('/safety', [PortalController::class,'safety'])->name('safety');
    Route::get('/safety/export', [PortalController::class,'export'])->name('export');
    Route::get('/acars', [PortalController::class,'acars'])->name('acars');
    Route::get('/flights', [FlightProgrammeController::class,'flights'])->name('flights');
    Route::get('/flights/{id}', [FlightProgrammeController::class,'flight'])->name('flights.show');
    Route::post('/flights/{id}/reserve', [FlightProgrammeController::class,'reserveFlight'])->name('flights.reserve');
    Route::get('/flights/{id}/briefing', [FlightProgrammeController::class,'briefing'])->name('flights.briefing');
    Route::post('/flights/{id}/briefing', [FlightProgrammeController::class,'saveBriefing'])->name('flights.briefing.save');
    Route::get('/simbrief/{id}', [FlightProgrammeController::class,'simbrief'])->name('simbrief.show');
    Route::get('/replay/{id}', [FlightProgrammeController::class,'replay'])->name('replay');
});

// Dispatch Desk is readable by every authenticated pilot. Mutating OPS/Datalink
// endpoints remain in the administrator-only route tree below.
Route::middleware(['web','auth'])->prefix('admin/promethee')->name('admin.promethee.')->group(function () {
    Route::get('/dispatch', [DispatchDeskController::class, 'index'])->name('dispatch');
    Route::get('/dispatch/feed', [DispatchDeskController::class, 'feed'])->name('dispatch.feed');
    Route::get('/dispatch/operations/{operation}', [DispatchDeskController::class, 'operation'])->name('dispatch.operation');
    Route::get('/datalink/messages', [DatalinkController::class, 'adminIndex'])->name('datalink.messages');
    Route::post('/datalink/messages', [DatalinkController::class, 'adminSend'])->name('datalink.messages.send');
    Route::post('/datalink/messages/{message}/read', [DatalinkController::class, 'adminRead'])->name('datalink.messages.read');
    Route::post('/datalink/messages/{message}/ack', [DatalinkController::class, 'adminAcknowledge'])->name('datalink.messages.ack');
});

/* Administration has a dedicated, server-protected route tree. */
Route::middleware(['web','auth','ability:admin,admin-access'])->prefix('admin/promethee')->name('admin.promethee.')->group(function () {
        Route::get('/', [PortalController::class,'adminDashboard'])->name('dashboard');
        Route::get('/identite', [PortalController::class, 'branding'])->name('branding');
        Route::post('/identite', [PortalController::class, 'saveBranding'])->name('branding.save');
        Route::post('/identite/importer', [PortalController::class, 'importBranding'])->name('branding.import');
        Route::get('/simbrief', [PortalController::class, 'adminSimbrief'])->name('simbrief');
        Route::get('/airframes', [AircraftConfigurationAdminController::class, 'index'])->name('airframes');
        Route::post('/airframes/types', [AircraftConfigurationAdminController::class, 'storeType'])->name('airframes.types.save');
        Route::post('/airframes/variants', [AircraftConfigurationAdminController::class, 'storeVariant'])->name('airframes.variants.save');
        Route::post('/airframes/variants/{variant}/duplicate', [AircraftConfigurationAdminController::class, 'duplicateVariant'])->name('airframes.variants.duplicate');
        Route::post('/airframes/configurations', [AircraftConfigurationAdminController::class, 'storeConfiguration'])->name('airframes.configurations.save');
        Route::post('/airframes/assign', [AircraftConfigurationAdminController::class, 'assign'])->name('airframes.assign');
        Route::post('/airframes/simulator-profiles', [AircraftConfigurationAdminController::class, 'storeSimulatorProfile'])->name('airframes.simulator-profiles.save');
        Route::post('/airframes/modifications', [AircraftConfigurationAdminController::class, 'storeModification'])->name('airframes.modifications.save');
        Route::get('/real-simulator-certifications', [RealSimulatorCertificationController::class, 'index'])->name('real-simulator-certifications');
        Route::post('/real-simulator-certifications', [RealSimulatorCertificationController::class, 'store'])->name('real-simulator-certifications.store');
        Route::put('/real-simulator-certifications/{certification}', [RealSimulatorCertificationController::class, 'update'])->name('real-simulator-certifications.update');
        Route::get('/pireps-emergency', [PortalController::class,'emergencyPireps'])->name('pireps-emergency');
        Route::delete('/pireps-emergency/{id}', [PortalController::class,'emergencyDeletePirep'])->name('pireps-emergency.delete');
        Route::post('/simbrief/api-key', [PortalController::class, 'saveSimbriefApiKey'])->name('simbrief.api-key.save');
        Route::delete('/simbrief/api-key', [PortalController::class, 'deleteSimbriefApiKey'])->name('simbrief.api-key.delete');
        Route::post('/simbrief/settings', [PortalController::class, 'saveSimbriefSettings'])->name('simbrief.settings');
        Route::post('/simbrief/sync', [PortalController::class, 'syncSimbrief'])->name('simbrief.sync');
        Route::post('/calendar', [CalendarController::class,'saveEvent'])->name('calendar.save');
        Route::delete('/calendar/{id}', [CalendarController::class,'deleteEvent'])->name('calendar.delete');
        Route::post('/pilots/{id}', [PortalController::class,'saveMember'])->name('pilots.save');
        Route::get('/tarifs-bbr', [EconomyController::class,'bbrSettings'])->name('bbr');
        Route::post('/tarifs-bbr', [EconomyController::class,'saveBbrSettings'])->name('bbr.save');
        Route::get('/economy', [EconomyController::class,'economy'])->name('economy');
        Route::get('/pricing-criteria', [EconomyController::class,'pricingCriteria'])->name('pricing-criteria');
        Route::post('/pricing-criteria', [EconomyController::class,'savePricingCriteria'])->name('pricing-criteria.save');
        Route::get('/economy/flight-prices/edit', [EconomyController::class,'flightPriceEditor'])->name('economy.flight-prices.edit');
        Route::get('/economy/flight-prices/{flight}/edit', [EconomyController::class,'flightPriceEdit'])->name('economy.flight-prices.line-edit');
        Route::post('/economy/bands', [EconomyController::class,'saveBandSettings'])->name('economy.bands');
        Route::post('/economy/simulation', [EconomyController::class,'saveSimulationSettings'])->name('economy.simulation');
        Route::post('/economy/preview', [EconomyController::class,'preview'])->name('economy.preview');
        Route::post('/economy/flight-prices', [EconomyController::class,'changeFlightPrices'])->name('economy.flight-prices');
        Route::get('/economy/fuel-prices/edit', [EconomyController::class,'fuelPriceEditor'])->name('economy.fuel-prices.editor');
        Route::get('/economy/fuel-prices/{country}/edit', [EconomyController::class,'fuelPriceEdit'])->name('economy.fuel-prices.edit');
        Route::post('/economy/fuel-prices', [EconomyController::class,'changeFuelPrices'])->name('economy.fuel-prices');
        Route::post('/economy/apply', [EconomyController::class,'apply'])->name('economy.apply');
        Route::post('/economy/cancel-preview', [EconomyController::class,'cancelPreview'])->name('economy.cancel-preview');
        Route::post('/economy/{id}/revert', [EconomyController::class,'revert'])->name('economy.revert');
        Route::post('/economy/import', [EconomyController::class,'importPricing'])->name('economy.import');
        Route::post('/economy/rules', [EconomyController::class,'savePricingRule'])->name('economy.rules.save');
        Route::put('/economy/rules/{id}', [EconomyController::class,'updatePricingRule'])->name('economy.rules.update');
        Route::post('/economy/rules/{id}/preview', [EconomyController::class,'previewPricingRule'])->name('economy.rules.preview');
        Route::delete('/economy/rules/{id}', [EconomyController::class,'deletePricingRule'])->name('economy.rules.delete');
        Route::get('/seasons', [EconomyController::class,'seasons'])->name('seasons');
        Route::post('/seasons', [EconomyController::class,'saveSeason'])->name('seasons.save');
        Route::post('/seasons/pricing-adjustments', [EconomyController::class,'saveSeasonPricingAdjustment'])->name('seasons.pricing-adjustments.save');
        Route::delete('/seasons/pricing-adjustments/{id}', [EconomyController::class,'deleteSeasonPricingAdjustment'])->name('seasons.pricing-adjustments.delete');
        Route::post('/seasons/import', [EconomyController::class,'importSchedule'])->name('seasons.import');
        Route::post('/safety', [PortalController::class,'generate'])->name('safety.generate');
        Route::get('/network', [PortalController::class,'network'])->name('network');
        Route::get('/network/presence', [PresenceController::class,'index'])->name('network.presence');
        Route::get('/health', [PortalController::class,'health'])->name('health');
        Route::redirect('/catalogue', '/catalogue/flights')->name('catalogue');
        Route::get('/catalogue/{type}', [PortalController::class,'catalogue'])->where('type','flights|airports')->name('catalogue.type');
        Route::get('/mailbox', [PortalController::class,'mailbox'])->name('mailbox');
        Route::post('/mailbox', [PortalController::class,'sendMessage'])->name('mailbox.send');
        Route::get('/crm', [CrmController::class,'index'])->name('crm');
        Route::post('/crm/send', [CrmController::class,'send'])->name('crm.send');
        Route::post('/crm/senders', [CrmController::class,'saveSender'])->name('crm.senders.save');
        Route::delete('/crm/senders/{id}', [CrmController::class,'deleteSender'])->name('crm.senders.delete');
        Route::get('/crm/campaigns/{id}', [CrmController::class,'campaign'])->name('crm.campaigns.show');
        // Lot 5 transport surface for the future Dispatcher Desk.
        Route::get('/sop', [SopController::class, 'admin'])->name('sop');
        Route::put('/sop/scoring/{rule}', [SopController::class, 'saveScoringRule'])->name('sop.scoring.update');
        Route::post('/sop/rules', [SopController::class, 'saveRule'])->name('sop.rules.save');
        Route::put('/sop/rules/{rule}', [SopController::class, 'saveRule'])->name('sop.rules.update');
        Route::delete('/sop/rules/{rule}', [SopController::class, 'deleteRule'])->name('sop.rules.delete');
        Route::post('/sop/alerts/{evaluation}/ack', [SopController::class, 'acknowledgeAlert'])->name('sop.alerts.ack');
        Route::get('/automation', [AutomationController::class,'automation'])->name('automation');
        Route::get('/automation/rules/{kind}/{id}', [AutomationController::class,'automationRule'])->where('kind','badge|rank')->name('automation.rules.show');
        Route::post('/automation/awards', [AutomationController::class,'createAutomationAward'])->name('automation.awards.create');
        Route::put('/automation/awards/{award}', [AutomationController::class,'updateAutomationAward'])->name('automation.awards.update');
        Route::post('/automation/ranks/catalogue', [AutomationController::class,'createAutomationRank'])->name('automation.ranks.create');
        Route::put('/automation/ranks/catalogue/{rank}', [AutomationController::class,'updateAutomationRank'])->name('automation.ranks.update');
        Route::post('/automation/badges', [AutomationController::class,'saveBadgeRule'])->name('automation.badges.save');
        Route::post('/automation/ranks', [AutomationController::class,'saveRankRule'])->name('automation.ranks.save');
        Route::post('/automation/preview', [AutomationController::class,'previewAutomation'])->name('automation.preview');
        Route::post('/automation/recalculate', [AutomationController::class,'recalculateAutomation'])->name('automation.recalculate');
        Route::get('/events', [CalendarController::class,'adminEvents'])->name('events');
        Route::post('/events', [CalendarController::class,'saveAdminEvent'])->name('events.save');
        Route::delete('/events/{id}', [CalendarController::class,'deleteEvent'])->name('events.delete');
        Route::get('/missions', [MissionsController::class,'adminMissions'])->name('missions');
        Route::post('/missions', [MissionsController::class,'saveMission'])->name('missions.save');
        Route::delete('/missions/{id}', [MissionsController::class,'deleteMission'])->name('missions.delete');
        Route::post('/circuits', [MissionsController::class,'saveCircuit'])->name('circuits.save');
        Route::delete('/circuits/{id}', [MissionsController::class,'deleteCircuit'])->name('circuits.delete');
        Route::get('/assignments', [PortalController::class,'adminAssignments'])->name('assignments');
        Route::post('/assignments', [PortalController::class,'saveAssignment'])->name('assignments.save');
        Route::delete('/assignments/{id}', [PortalController::class,'deleteAssignment'])->name('assignments.delete');
        Route::delete('/assignments', [PortalController::class,'deleteAssignments'])->name('assignments.bulk-delete');
        Route::get('/airlines', [PortalController::class,'adminAirlines'])->name('airlines');
        Route::post('/airlines', [PortalController::class,'saveAdminAirline'])->name('airlines.save');
        Route::get('/regional-operations', [RegionalOperationsAdminController::class,'regionalOperations'])->name('regional');
        Route::post('/regional-operations/settings', [RegionalOperationsAdminController::class,'saveRegionalOperations'])->name('regional.settings');
        Route::post('/regional-operations/bases', [RegionalOperationsAdminController::class,'saveRegionalBase'])->name('regional.bases.save');
        Route::post('/regional-operations/aircraft', [RegionalOperationsAdminController::class,'assignAircraftBase'])->name('regional.aircraft.assign');
        Route::post('/regional-operations/repatriation/sync', [RegionalOperationsAdminController::class,'syncRegionalRepatriations'])->name('regional.repatriation.sync');
        Route::post('/regional-operations/rotation/settings', [RegionalOperationsAdminController::class,'saveFleetRotationSettings'])->name('regional.rotation.settings');
        Route::post('/regional-operations/rotation/run', [RegionalOperationsAdminController::class,'runFleetRotation'])->name('regional.rotation.run');
        Route::get('/maintenance', [MaintenanceAdminController::class,'adminMaintenance'])->name('maintenance');
        Route::post('/maintenance/airframe-settings', [MaintenanceAdminController::class,'saveAirframeMaintenanceSettings'])->name('maintenance.airframe-settings.save');
        Route::post('/maintenance/airframe/{aircraft}/start', [MaintenanceAdminController::class,'startAirframeCheck'])->name('maintenance.airframe.start');
        Route::post('/maintenance/sync', [EngineMaintenanceAdminController::class,'syncFleet'])->name('maintenance.sync');
        Route::post('/maintenance/engine-profiles', [EngineMaintenanceAdminController::class,'saveProfile'])->name('maintenance.engine-profiles.save');
        Route::post('/maintenance/engines', [EngineMaintenanceAdminController::class,'createUnit'])->name('maintenance.engines.create');
        Route::post('/maintenance/engines/{engine}/overhaul', [EngineMaintenanceAdminController::class,'overhaul'])->name('maintenance.engines.overhaul');
        Route::post('/maintenance/engines/{engine}/install', [EngineMaintenanceAdminController::class,'install'])->name('maintenance.engines.install');
        Route::get('/passport', [PortalController::class,'adminPassport'])->name('passport');
        Route::post('/passport', [PortalController::class,'savePassportSettings'])->name('passport.save');
        Route::get('/shop', [PortalController::class,'adminShop'])->name('shop');
        Route::post('/shop/items', [PortalController::class,'saveShopItem'])->name('shop.items.save');
        Route::post('/shop/wallets', [PortalController::class,'creditWallet'])->name('shop.wallets.credit');
        Route::get('/jumpseats', [PortalController::class,'adminJumpseats'])->name('jumpseats');
        Route::post('/jumpseats/settings', [PortalController::class,'saveJumpseatSettings'])->name('jumpseats.settings');
        Route::get('/downloads', [DownloadsController::class,'adminDownloads'])->name('downloads');
        Route::post('/downloads', [DownloadsController::class,'storeDownload'])->name('downloads.store');
        Route::get('/downloads/{file}/edit', [DownloadsController::class,'editDownload'])->name('downloads.edit');
        Route::put('/downloads/{file}', [DownloadsController::class,'updateDownload'])->name('downloads.update');
        Route::delete('/downloads/{file}', [DownloadsController::class,'deleteDownload'])->name('downloads.delete');
});
// SimBrief redirects the browser here after an API generation. The random state token
// correlates the callback; the OFP is still imported only by the authenticated pilot.
Route::middleware('web')->get('/simbrief/callback/{state}', [SimBriefCallbackController::class, '__invoke'])
    ->where('state', '[A-Za-z0-9]{64}')
    ->name('promethee.simbrief.callback');

// Hermès authentication is owned by Prométhée, not phpVMS core.
Route::middleware('api')->post('/api/acars/session', [AcarsSessionController::class, 'store'])
    ->middleware('throttle:5,1');
Route::middleware('api')->post('/api/acars/argos', [AcarsSessionController::class, 'storeFromArgos'])
    ->middleware('throttle:10,1');
Route::middleware(['api','api.auth'])->delete('/api/acars/session', [AcarsSessionController::class, 'destroy']);

// Compatibility endpoints for older Hermès builds. New clients use /api/v1/operations/*.
Route::middleware(['api','api.auth'])->prefix('api/acars')->group(function () {
    Route::get('/aircraft/{registration}/resolved-profile', [AircraftConfigurationController::class, 'show']);
    Route::post('/flights/{flight_id}/simbrief/session', [AcarsSimBriefController::class, 'session']);
    Route::post('/flights/{flight_id}/simbrief/redirect', [AcarsSimBriefController::class, 'redirect']);
    Route::post('/flights/{flight_id}/simbrief/account/import', [AcarsSimBriefController::class, 'importAccount']);
    Route::post('/flights/{flight_id}/simbrief/import', [AcarsSimBriefController::class, 'import']);
});

// Update discovery is intentionally public: Hermès checks before the pilot signs in.
Route::middleware('api')->get('/api/v1/hermes/releases/latest', [HermesReleaseController::class, 'latest']);

Route::middleware(['api','api.auth'])->prefix('api/v1')->group(function () {
    Route::get('/me', [OperationsV1Controller::class, 'me']);
    Route::delete('/pireps/{pirep}', [OperationsV1Controller::class, 'deletePirep']);
    Route::get('/aircraft/{registration}/resolved-profile', [AircraftConfigurationController::class, 'show']);
    Route::get('/me/aircraft-variants', [OperationsV1Controller::class, 'myAircraftVariants']);
    Route::put('/me/aircraft-variants', [OperationsV1Controller::class, 'saveMyAircraftVariants']);
    Route::get('/me/simulator-profiles', [OperationsV1Controller::class, 'myAircraftVariants']);
    Route::put('/me/simulator-profiles', [OperationsV1Controller::class, 'saveMyAircraftVariants']);
    Route::get('/flights', [OperationsV1Controller::class, 'searchFlights']);
    Route::post('/flights/{flightId}/reserve', [OperationsV1Controller::class, 'reserveFlight']);
    Route::get('/operations', [OperationsV1Controller::class, 'index']);
    Route::get('/operations/{bid}', [OperationsV1Controller::class, 'show']);
    Route::delete('/operations/{bid}', [OperationsV1Controller::class, 'destroy']);
    Route::get('/operations/{bid}/aircraft-eligibility', [OperationsV1Controller::class, 'aircraft']);
    Route::put('/operations/{bid}/aircraft', [OperationsV1Controller::class, 'selectAircraft']);
    Route::get('/operations/{bid}/aircraft-variants', [OperationsV1Controller::class, 'aircraftVariants']);
    Route::put('/operations/{bid}/aircraft-variant', [OperationsV1Controller::class, 'selectAircraftVariant']);
    Route::get('/operations/{bid}/simulator-profiles', [OperationsV1Controller::class, 'aircraftVariants']);
    Route::put('/operations/{bid}/simulator-profile', [OperationsV1Controller::class, 'selectAircraftVariant']);
    Route::get('/operations/{bid}/briefing', [OperationsV1Controller::class, 'briefing']);
    Route::get('/operations/{bid}/readiness', [OperationsV1Controller::class, 'readiness']);
    Route::get('/operations/{bid}/dispatch', [OperationsV1Controller::class, 'operationDispatch']);
    Route::get('/operations/{bid}/weather', [OperationsV1Controller::class, 'operationWeather']);
    Route::get('/operations/{bid}/pirep', [OperationsV1Controller::class, 'pirep']);
    Route::get('/operations/{bid}/debrief', [OperationsV1Controller::class, 'debrief']);
    Route::get('/operations/{bid}/fleet-state', [OperationsV1Controller::class, 'fleetState']);
    Route::post('/operations/{bid}/fleet-state/reconcile', [OperationsV1Controller::class, 'reconcileFleet']);
    Route::post('/operations/{bid}/pirep', [OperationsV1Controller::class, 'prefilePirep']);
    Route::post('/operations/{bid}/telemetry', [TelemetryController::class, 'storeOperation']);
    Route::get('/operations/{operation}/datalink', [DatalinkController::class, 'index']);
    Route::post('/operations/{operation}/datalink', [DatalinkController::class, 'send']);
    Route::post('/operations/{operation}/datalink/{message}/read', [DatalinkController::class, 'read']);
    Route::post('/operations/{operation}/datalink/{message}/ack', [DatalinkController::class, 'acknowledge']);
    Route::get('/operations/{operation}/sop', [SopController::class, 'index']);
    Route::post('/operations/{operation}/sop/facts', [SopController::class, 'ingest']);
    Route::post('/operations/{operation}/sop/evaluations/{evaluation}/review', [SopController::class, 'review']);
    Route::post('/operations/{operation}/presence/heartbeat', [PresenceController::class, 'heartbeat']);
    Route::get('/network/presence', [PresenceController::class, 'index']);
    Route::post('/operations/{operation}/simbrief/readiness', [AcarsSimBriefController::class, 'readinessOperation']);
    Route::post('/operations/{operation}/simbrief/session', [AcarsSimBriefController::class, 'sessionOperation']);
    Route::post('/operations/{operation}/simbrief/redirect', [AcarsSimBriefController::class, 'redirectOperation']);
    Route::post('/operations/{operation}/simbrief/account/import', [AcarsSimBriefController::class, 'importAccountOperation']);
    Route::post('/operations/{operation}/simbrief/import', [AcarsSimBriefController::class, 'importOperation']);
    Route::get('/hermes/configuration', [AcarsConfigurationController::class, 'show']);
});

// Deprecated compatibility surface for older Hermès/Prometheus clients.
// New desktop builds must consume /api/v1 exclusively. Do not add features here.
Route::middleware(['api','api.auth'])->prefix('api/promethee')->group(function () {
    Route::post('/pireps/{id}/telemetry', [TelemetryController::class,'store']);
    Route::get('/acars/operations', [AcarsOperationsController::class, 'index']);
    Route::get('/acars/operations/{bid}/aircraft', [AcarsOperationsController::class, 'aircraft']);
    Route::get('/acars/operations/{bid}/ofp', [AcarsOperationsController::class, 'ofp']);
    Route::get('/acars/configuration', [AcarsConfigurationController::class, 'show']);
});

Route::middleware(['web','auth','ability:admin,admin-access'])->get('/admin/identity/identite', fn () => redirect()->route('admin.promethee.branding'));

// Transitional bookmarks: all new Prométhée URLs are root URLs.
Route::middleware(['web','auth'])->get('/promethee/{path?}', function (?string $path = null) {
    return redirect('/'.ltrim((string) $path, '/'));
})->where('path', '.*');
