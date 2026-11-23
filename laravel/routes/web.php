<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Viewer\DashboardController;
use App\Http\Controllers\Viewer\EducationController;
use App\Http\Controllers\Admin\PrediksiPdfController;

/*
|--------------------------------------------------------------------------
| Viewer / Public Routes (no authentication required)
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('viewer.dashboard');
Route::get('/education', [EducationController::class, 'index'])->name('viewer.education');

// Prediction page removed from viewer — moved to Admin panel only

/*
|--------------------------------------------------------------------------
| Admin PDF Download Routes (auth required via Filament session)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/admin/prediksi/pdf', [PrediksiPdfController::class, 'download'])
         ->name('admin.prediksi.pdf');
});
