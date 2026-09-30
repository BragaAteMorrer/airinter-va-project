<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\Admin\SecurityAdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\PasswordSecurityController;
use App\Http\Controllers\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Auth\TwoFactorSettingsController;
use App\Http\Controllers\Oidc\ProviderController;
use App\Http\Middleware\EnsureSecurityAdministrator;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/.well-known/openid-configuration', [ProviderController::class, 'discovery'])->name('oidc.discovery');
Route::get('/.well-known/oauth-authorization-server', [ProviderController::class, 'oauthMetadata'])->name('oauth.metadata');
Route::get('/.well-known/jwks.json', [ProviderController::class, 'jwks'])->name('oidc.jwks');
Route::get('/.well-known/hermes-client', [ProviderController::class, 'hermesClient'])->name('oidc.hermes-client');
Route::get('/hermes/client-config', [ProviderController::class, 'hermesClient'])->name('hermes.client-config');
Route::middleware('auth:api')->get('/oauth/userinfo', [ProviderController::class, 'userinfo'])->name('oidc.userinfo');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/two-factor-challenge', [TwoFactorChallengeController::class, 'create'])->name('two-factor.login');
    Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])->middleware('throttle:10,1')->name('two-factor.login.store');

    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update.reset');
});

Route::middleware('auth')->get('/auth/passkey/complete', function () {
    return redirect()->intended(route('account'));
})->name('auth.passkey.complete');

Route::middleware('auth')->group(function () {
    Route::get('/account', AccountController::class)->name('account');
    Route::put('/account/preferences', [AccountController::class, 'updatePreferences'])->name('account.preferences.update');

    Route::get('/confirm-password', [ConfirmPasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmPasswordController::class, 'store'])->name('password.confirm.store');

    Route::get('/verify-email', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');

    Route::middleware('password.confirm')->group(function () {
        Route::put('/account/password', [PasswordSecurityController::class, 'update'])->name('account.password.update');
        Route::get('/account/mfa', [TwoFactorSettingsController::class, 'create'])->name('account.mfa');
        Route::post('/account/mfa', [TwoFactorSettingsController::class, 'confirm'])->name('account.mfa.confirm');
        Route::post('/account/mfa/recovery-codes', [TwoFactorSettingsController::class, 'regenerate'])->name('account.mfa.recovery.regenerate');
        Route::delete('/account/mfa', [TwoFactorSettingsController::class, 'destroy'])->name('account.mfa.destroy');
    });

    Route::get('/account/mfa/recovery-codes', [TwoFactorSettingsController::class, 'recoveryCodes'])->name('account.mfa.recovery-codes');
    Route::delete('/account/applications/{clientId}', [AccountSecurityController::class, 'revokeApplication'])->name('account.applications.revoke');
    Route::delete('/account/trusted-devices/{device}', [AccountSecurityController::class, 'revokeTrustedDevice'])->name('account.trusted-devices.revoke');
    Route::delete('/account/sessions/{sessionId}', [AccountSecurityController::class, 'revokeSession'])->name('account.sessions.revoke');
    Route::delete('/account/sessions', [AccountSecurityController::class, 'revokeOtherSessions'])->name('account.sessions.revoke-others');
    Route::middleware([EnsureSecurityAdministrator::class, 'password.confirm'])->group(function () {
        Route::get('/admin/security', [SecurityAdminController::class, 'index'])->name('admin.security');
        Route::post('/admin/security/users/{user}/revoke', [SecurityAdminController::class, 'revokeUser'])->name('admin.security.users.revoke');
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
