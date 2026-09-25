<?php

use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/v1/me', MeController::class);
