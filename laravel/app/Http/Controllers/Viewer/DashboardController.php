<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\SensorReading;
use App\Models\AirQualityTrend;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function index()
    {
        $sensorReadings = collect();
        $trendData      = collect();
        $facultyCount   = 13; // default from config

        try {
            $sensorReadings = SensorReading::with('faculty')
                ->latestPerFaculty()
                ->get();

            $trendData = AirQualityTrend::campusWide()
                ->lastDays(30)
                ->orderBy('recorded_date', 'asc')
                ->get();

            $facultyCount = Faculty::where('is_active', true)->count() ?: 13;
        } catch (\Exception $e) {
            // DB not yet available — pages render with empty collections
            // Run: php artisan migrate && php artisan db:seed
        }

        return view('viewer.dashboard', compact(
            'sensorReadings',
            'trendData',
            'facultyCount',
        ));
    }
}
