<?php

use App\Http\Controllers\Api\ApiTokenController;
use App\Http\Controllers\Api\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::post('tokens', ApiTokenController::class)->middleware('throttle:10,1');

Route::middleware(['auth:sanctum', 'can:manage-appointments', 'throttle:60,1'])
    ->apiResource('appointments', AppointmentController::class);
