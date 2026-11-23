<?php

use App\Http\Controllers\Api\SensorDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — AeroSense V2
|--------------------------------------------------------------------------
|
| [LAMA - tetap aktif untuk backward compatibility]
| POST /api/send-data
|   → Endpoint generik, FacultyId dikirim di dalam body payload
|   → Cocok untuk ESP32 lama yang sudah terpasang
|
| [BARU - per-node dedicated endpoint]
| POST /api/nodes/{facultyId}/data
|   → Setiap fakultas punya URL sendiri (ID di URL, tidak perlu di body)
|   → Contoh: POST /api/nodes/1/data  = Fakultas Teknik
|             POST /api/nodes/12/data = Sekolah Vokasi
|
| [READ - untuk public dashboard]
| GET /api/sensor-readings/latest
|   → Mengembalikan data terbaru per fakultas (dipakai public dashboard)
|
| GET /api/nodes/{facultyId}/latest
|   → Data terbaru untuk satu node spesifik
|
*/

// ── [LAMA] Endpoint generik — tetap hidup, tidak ada yang rusak ──────────
Route::post('/send-data', [SensorDataController::class, 'store'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── [BARU] Per-node endpoints — satu URL per fakultas ────────────────────
Route::post('/nodes/{facultyId}/data', [SensorDataController::class, 'storeByNode'])
    ->whereNumber('facultyId')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── READ endpoints ────────────────────────────────────────────────────────

// Trigger bersihkan Sekolah Pascasarjana & pindahkan Prototype ke ID 13
Route::get('/nodes/clean-and-reassign', [SensorDataController::class, 'cleanAndReassignNodes']);

// Semua fakultas sekaligus (untuk public dashboard auto-refresh)
Route::get('/sensor-readings/latest', [SensorDataController::class, 'latest']);

// Satu node spesifik (untuk debugging / monitoring per fakultas)
Route::get('/nodes/{facultyId}/latest', [SensorDataController::class, 'latestByNode'])
    ->whereNumber('facultyId');
