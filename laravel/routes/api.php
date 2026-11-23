<?php

use App\Http\Controllers\Api\SensorDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — AeroSense V2
|--------------------------------------------------------------------------
| POST /api/send-data              — IoT sensor node ingestion (matches Arduino code)
| GET  /api/sensor-readings/latest — Latest reading per faculty (for public dashboard)
*/

// IoT Device endpoint — matches the Arduino URL exactly
Route::post('/send-data', [SensorDataController::class, 'store'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Public read endpoint for the viewer dashboard auto-refresh
Route::get('/sensor-readings/latest', [SensorDataController::class, 'latest']);
