<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| AeroSense Scheduled Tasks
|--------------------------------------------------------------------------
|
| The sensor:generate-hourly command runs automatically every hour,
| inserting one reading per active faculty into sensor_readings.
|
| To activate the scheduler, run in a separate terminal:
|   php artisan schedule:work
|
| Or use: composer run dev  (starts serve + queue + vite + scheduler)
|
*/

Schedule::command('sensor:generate-hourly --quiet-mode')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/sensor-scheduler.log'));
