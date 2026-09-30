<?php

use App\Http\Controllers\Api\MeController;
use App\Http\Controllers\Api\HermesIdentityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/v1/me', MeController::class);
Route::middleware('auth:api')->get('/v1/hermes/session', HermesIdentityController::class);
