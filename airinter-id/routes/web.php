<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Oidc\ProviderController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/.well-known/openid-configuration', [ProviderController::class, 'discovery'])->name('oidc.discovery');
Route::get('/.well-known/oauth-authorization-server', [ProviderController::class, 'oauthMetadata'])->name('oauth.metadata');
Route::get('/.well-known/jwks.json', [ProviderController::class, 'jwks'])->name('oidc.jwks');
Route::middleware('auth:api')->get('/oauth/userinfo', [ProviderController::class, 'userinfo'])->name('oidc.userinfo');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/account', AccountController::class)->name('account');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
