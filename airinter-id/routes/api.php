<?php

use App\Http\Controllers\Api\HermesBridgeController;
use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/v1/me', MeController::class);
Route::middleware('auth:api')->get('/v1/hermes/bridge', HermesBridgeController::class);
