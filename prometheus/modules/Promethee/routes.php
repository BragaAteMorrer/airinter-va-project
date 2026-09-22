<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\LanguageController;
use Modules\Promethee\Http\PortalController;
use Modules\Promethee\Http\TelemetryController;
use Modules\Promethee\Http\AcarsOperationsController;
use Modules\Promethee\Http\AcarsConfigurationController;
use Modules\Promethee\Http\OperationsV1Controller;
use Modules\Promethee\Http\HermesReleaseController;

// Browsers request this conventional path even though the branded icon lives
// with the static Promethee assets.
Route::redirect('/favicon.ico', '/assets/img/favicon.png');
// Retain the Promethee URL, but use phpVMS' one canonical implementation.
Route::get('/language/{lang}', [LanguageController::class, 'switchLang'])
    ->middleware('web')->name('promethee.language');
Route::get('/occ', [PortalController::class, 'occ'])->middleware('web')->name('promethee.occ');
// Public flight reports replace the legacy phpVMS report screen. The report
// remains readable without an account, just as the former public URL was.
Route::get('/pireps/{id}', [PortalController::class, 'pirep'])->middleware(['web','auth'])->name('promethee.pireps.show');
// Backward-compatible name used by the aircraft history view.
Route::get('/pirep/{id}', [PortalController::class, 'pirep'])->middleware(['web','auth'])->name('promethee.pirep');
Route::middleware('web')->prefix('public')->name('promethee.public.')->group(function () {
    Route::get('/pilots', [PortalController::class, 'publicPilots'])->name('pilots');
    Route::get('/pireps', [PortalController::class, 'publicPireps'])->middleware('auth')->name('pireps');
    Route::get('/live', [PortalController::class, 'publicLive'])->name('live');
    Route::get('/live-data', [PortalController::class, 'liveData'])->name('live.data');
});
// The historic fleet directory was public; retain that access level.
Route::get('/dfleet', [PortalController::class, 'fleet'])->middleware('web')->name('promethee.fleet');

Route::middleware(['web','auth'])->name('promethee.')->group(function () {
    Route::get('/', [PortalController::class,'dashboard'])->name('dashboard');
    Route::get('/departure-board-data', [PortalController::class,'departureBoardData'])->name('departure-board.data');
    Route::get('/profile', [PortalController::class,'profile'])->name('profile');
    Route::get('/profile/edit', [PortalController::class,'editProfile'])->name('profile.edit');
    Route::patch('/profile', [PortalController::class,'updateProfile'])->name('profile.update');
    Route::get('/passport', [PortalController::class,'passport'])->name('passport');
    Route::get('/bookings', [PortalController::class,'bookings'])->name('bookings');
    Route::get('/downloads', [PortalController::class,'downloads'])->name('downloads');
    Route::get('/downloads/categories/{category}', [PortalController::class,'downloadCategoryPage'])->where('category','acars|fleet|airports|documents')->name('downloads.category');
    Route::get('/downloads/{file}', [PortalController::class,'download'])->name('downloads.download');
    Route::get('/missions', [PortalController::class,'missions'])->name('missions');
    Route::get('/assignments', [PortalController::class,'assignments'])->name('assignments');
    Route::get('/shop', [PortalController::class,'shop'])->name('shop');
    Route::post('/shop/{id}/buy', [PortalController::class,'buyShopItem'])->name('shop.buy');
    Route::get('/transfers', [PortalController::class,'transfers'])->name('transfers');
    Route::post('/transfers', [PortalController::class,'requestTransfer'])->name('transfers.request');
    Route::get('/jumpseat', [PortalController::class,'jumpseat'])->name('jumpseat');
    Route::post('/jumpseat', [PortalController::class,'requestJumpseat'])->name('jumpseat.buy');
    Route::get('/operations', [PortalController::class,'operations'])->name('operations');
    // Native replacements for the former Disposable pages. Existing bookmarks
    // continue to work without enabling unrelated legacy module features.
    Route::get('/dairlines', [PortalController::class, 'airlines'])->name('airlines');
    // Historic company bookmark used by the previous themes.
    Route::get('/dcompany', [PortalController::class, 'airlines'])->name('company');
    Route::get('/dmaintenance', [PortalController::class, 'maintenance'])->name('maintenance');
    Route::get('/daircraft/{registration}', [PortalController::class, 'aircraftDetail'])->name('aircraft.show');
    Route::get('/live', [PortalController::class,'live'])->name('live');
    Route::get('/live-data', [PortalController::class,'liveData'])->name('live.data');
    Route::get('/calendar', [PortalController::class,'calendar'])->name('calendar');
    Route::post('/calendar/{id}/rsvp', [PortalController::class,'rsvpEvent'])->name('calendar.rsvp');
    Route::get('/pilots', [PortalController::class,'pilots'])->name('pilots');
    Route::get('/pilots/{id}', [PortalController::class,'pilot'])->name('pilots.show');
    Route::get('/safety', [PortalController::class,'safety'])->name('safety');
    Route::get('/safety/export', [PortalController::class,'export'])->name('export');
    Route::get('/acars', [PortalController::class,'acars'])->name('acars');
    Route::get('/flights', [PortalController::class,'flights'])->name('flights');
    Route::get('/flights/{id}', [PortalController::class,'flight'])->name('flights.show');
    Route::post('/flights/{id}/reserve', [PortalController::class,'reserveFlight'])->name('flights.reserve');
    Route::get('/flights/{id}/briefing', [PortalController::class,'briefing'])->name('flights.briefing');
    Route::post('/flights/{id}/briefing', [PortalController::class,'saveBriefing'])->name('flights.briefing.save');
    Route::get('/replay/{id}', [PortalController::class,'replay'])->name('replay');
});

/* Administration has a dedicated, server-protected route tree. */
Route::middleware(['web','auth','ability:admin,admin-access'])->prefix('admin/promethee')->name('admin.promethee.')->group(function () {
        Route::get('/', [PortalController::class,'adminDashboard'])->name('dashboard');
        Route::get('/identite', [PortalController::class, 'branding'])->name('branding');
        Route::post('/identite', [PortalController::class, 'saveBranding'])->name('branding.save');
        Route::post('/identite/importer', [PortalController::class, 'importBranding'])->name('branding.import');
        Route::post('/calendar', [PortalController::class,'saveEvent'])->name('calendar.save');
        Route::delete('/calendar/{id}', [PortalController::class,'deleteEvent'])->name('calendar.delete');
        Route::post('/pilots/{id}', [PortalController::class,'saveMember'])->name('pilots.save');
        Route::get('/economy', [PortalController::class,'economy'])->name('economy');
        Route::get('/economy/flight-prices/edit', [PortalController::class,'flightPriceEditor'])->name('economy.flight-prices.edit');
        Route::get('/economy/flight-prices/{flight}/edit', [PortalController::class,'flightPriceEdit'])->name('economy.flight-prices.line-edit');
        Route::post('/economy/bands', [PortalController::class,'saveBandSettings'])->name('economy.bands');
        Route::post('/economy/simulation', [PortalController::class,'saveSimulationSettings'])->name('economy.simulation');
        Route::post('/economy/preview', [PortalController::class,'preview'])->name('economy.preview');
        Route::post('/economy/flight-prices', [PortalController::class,'changeFlightPrices'])->name('economy.flight-prices');
        Route::get('/economy/fuel-prices/edit', [PortalController::class,'fuelPriceEditor'])->name('economy.fuel-prices.editor');
        Route::get('/economy/fuel-prices/{country}/edit', [PortalController::class,'fuelPriceEdit'])->name('economy.fuel-prices.edit');
        Route::post('/economy/fuel-prices', [PortalController::class,'changeFuelPrices'])->name('economy.fuel-prices');
        Route::post('/economy/apply', [PortalController::class,'apply'])->name('economy.apply');
        Route::post('/economy/cancel-preview', [PortalController::class,'cancelPreview'])->name('economy.cancel-preview');
        Route::post('/economy/{id}/revert', [PortalController::class,'revert'])->name('economy.revert');
        Route::post('/economy/import', [PortalController::class,'importPricing'])->name('economy.import');
        Route::post('/economy/rules', [PortalController::class,'savePricingRule'])->name('economy.rules.save');
        Route::put('/economy/rules/{id}', [PortalController::class,'updatePricingRule'])->name('economy.rules.update');
        Route::post('/economy/rules/{id}/preview', [PortalController::class,'previewPricingRule'])->name('economy.rules.preview');
        Route::delete('/economy/rules/{id}', [PortalController::class,'deletePricingRule'])->name('economy.rules.delete');
        Route::get('/seasons', [PortalController::class,'seasons'])->name('seasons');
        Route::post('/seasons', [PortalController::class,'saveSeason'])->name('seasons.save');
        Route::post('/seasons/import', [PortalController::class,'importSchedule'])->name('seasons.import');
        Route::post('/safety', [PortalController::class,'generate'])->name('safety.generate');
        Route::get('/network', [PortalController::class,'network'])->name('network');
        Route::get('/health', [PortalController::class,'health'])->name('health');
        Route::redirect('/catalogue', '/catalogue/flights')->name('catalogue');
        Route::get('/catalogue/{type}', [PortalController::class,'catalogue'])->where('type','flights|airports')->name('catalogue.type');
        Route::get('/mailbox', [PortalController::class,'mailbox'])->name('mailbox');
        Route::post('/mailbox', [PortalController::class,'sendMessage'])->name('mailbox.send');
        Route::get('/automation', [PortalController::class,'automation'])->name('automation');
        Route::get('/automation/rules/{kind}/{id}', [PortalController::class,'automationRule'])->where('kind','badge|rank')->name('automation.rules.show');
        Route::post('/automation/awards', [PortalController::class,'createAutomationAward'])->name('automation.awards.create');
        Route::put('/automation/awards/{award}', [PortalController::class,'updateAutomationAward'])->name('automation.awards.update');
        Route::post('/automation/ranks/catalogue', [PortalController::class,'createAutomationRank'])->name('automation.ranks.create');
        Route::put('/automation/ranks/catalogue/{rank}', [PortalController::class,'updateAutomationRank'])->name('automation.ranks.update');
        Route::post('/automation/badges', [PortalController::class,'saveBadgeRule'])->name('automation.badges.save');
        Route::post('/automation/ranks', [PortalController::class,'saveRankRule'])->name('automation.ranks.save');
        Route::post('/automation/preview', [PortalController::class,'previewAutomation'])->name('automation.preview');
        Route::post('/automation/recalculate', [PortalController::class,'recalculateAutomation'])->name('automation.recalculate');
        Route::get('/events', [PortalController::class,'adminEvents'])->name('events');
        Route::post('/events', [PortalController::class,'saveAdminEvent'])->name('events.save');
        Route::delete('/events/{id}', [PortalController::class,'deleteEvent'])->name('events.delete');
        Route::get('/missions', [PortalController::class,'adminMissions'])->name('missions');
        Route::post('/missions', [PortalController::class,'saveMission'])->name('missions.save');
        Route::delete('/missions/{id}', [PortalController::class,'deleteMission'])->name('missions.delete');
        Route::post('/circuits', [PortalController::class,'saveCircuit'])->name('circuits.save');
        Route::delete('/circuits/{id}', [PortalController::class,'deleteCircuit'])->name('circuits.delete');
        Route::get('/assignments', [PortalController::class,'adminAssignments'])->name('assignments');
        Route::post('/assignments', [PortalController::class,'saveAssignment'])->name('assignments.save');
        Route::delete('/assignments/{id}', [PortalController::class,'deleteAssignment'])->name('assignments.delete');
        Route::delete('/assignments', [PortalController::class,'deleteAssignments'])->name('assignments.bulk-delete');
        Route::get('/airlines', [PortalController::class,'adminAirlines'])->name('airlines');
        Route::post('/airlines', [PortalController::class,'saveAdminAirline'])->name('airlines.save');
        Route::get('/passport', [PortalController::class,'adminPassport'])->name('passport');
        Route::post('/passport', [PortalController::class,'savePassportSettings'])->name('passport.save');
        Route::get('/shop', [PortalController::class,'adminShop'])->name('shop');
        Route::post('/shop/items', [PortalController::class,'saveShopItem'])->name('shop.items.save');
        Route::post('/shop/wallets', [PortalController::class,'creditWallet'])->name('shop.wallets.credit');
        Route::get('/transfers', [PortalController::class,'adminTransfers'])->name('transfers');
        Route::post('/transfers/{id}', [PortalController::class,'decideTransfer'])->name('transfers.decide');
        Route::get('/jumpseats', [PortalController::class,'adminJumpseats'])->name('jumpseats');
        Route::post('/jumpseats/settings', [PortalController::class,'saveJumpseatSettings'])->name('jumpseats.settings');
        Route::get('/downloads', [PortalController::class,'adminDownloads'])->name('downloads');
        Route::post('/downloads', [PortalController::class,'storeDownload'])->name('downloads.store');
        Route::get('/downloads/{file}/edit', [PortalController::class,'editDownload'])->name('downloads.edit');
        Route::put('/downloads/{file}', [PortalController::class,'updateDownload'])->name('downloads.update');
        Route::delete('/downloads/{file}', [PortalController::class,'deleteDownload'])->name('downloads.delete');
});
// Update discovery is intentionally public: Hermès checks before the pilot signs in.
Route::middleware('api')->get('/api/v1/hermes/releases/latest', [HermesReleaseController::class, 'latest']);

Route::middleware(['api','api.auth'])->prefix('api/v1')->group(function () {
    Route::get('/me', [OperationsV1Controller::class, 'me']);
    Route::get('/operations/{bid}', [OperationsV1Controller::class, 'show']);
    Route::get('/operations/{bid}/aircraft-eligibility', [OperationsV1Controller::class, 'aircraft']);
    Route::get('/operations/{bid}/briefing', [OperationsV1Controller::class, 'briefing']);
    Route::get('/operations/{bid}/readiness', [OperationsV1Controller::class, 'readiness']);
});

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
