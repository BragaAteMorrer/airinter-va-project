<?php

use Illuminate\Support\Facades\Route;
use Modules\Promethee\Http\MinitelPortalController;

Route::middleware(['web', 'auth'])->prefix('minitel/portal')->name('promethee.minitel.portal.')->group(function () {
    Route::get('/dashboard', [MinitelPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/pireps', [MinitelPortalController::class, 'pireps'])->name('pireps');
    Route::get('/missions', [MinitelPortalController::class, 'missions'])->name('missions');
    Route::get('/passport', [MinitelPortalController::class, 'passport'])->name('passport');
    Route::get('/finances', [MinitelPortalController::class, 'finances'])->name('finances');
    Route::get('/regional', [MinitelPortalController::class, 'regional'])->name('regional');
    Route::get('/admin', [MinitelPortalController::class, 'admin'])->name('admin');
});
