<?php

namespace App\Http\Controllers\Viewer;

use App\Http\Controllers\Controller;
use App\Models\AirQualityPrediction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PredictionController extends Controller
{
    public function index()
    {
        $today              = Carbon::today();
        $facultyPredictions = collect();
        $hourlyPredictions  = collect();
        $dailyPredictions   = collect();

        try {
            $facultyPredictions = AirQualityPrediction::with('faculty')
                ->daily()
                ->whereNotNull('faculty_id')
                ->whereDate('predicted_for', $today)
                ->get()
                ->groupBy('faculty_id');

            $hourlyPredictions = AirQualityPrediction::campusWide()
                ->hourly()
                ->whereDate('predicted_for', $today)
                ->orderBy('predicted_for', 'asc')
                ->get();

            $dailyPredictions = AirQualityPrediction::campusWide()
                ->daily()
                ->whereBetween('predicted_for', [
                    $today,
                    $today->copy()->addDays(3)->endOfDay(),
                ])
                ->orderBy('predicted_for', 'asc')
                ->get();
        } catch (\Exception $e) {
            // DB not yet available — page renders with static fallback data (defined in Blade)
            // Run: php artisan migrate && php artisan db:seed
        }

        $modelInfo = [
            'accuracy'  => config('aerosense.model_accuracy', 86),
            'name'      => config('aerosense.model_name',     'AeroSense v2'),
            'algorithm' => config('aerosense.model_algorithm','Random Forest'),
        ];

        return view('viewer.prediction.index', compact(
            'facultyPredictions',
            'hourlyPredictions',
            'dailyPredictions',
            'modelInfo',
        ));
    }
}
