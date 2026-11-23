<?php

namespace Database\Seeders;

use App\Models\AirQualityPrediction;
use App\Models\Faculty;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AirQualityPredictionSeeder extends Seeder
{
    public function run(): void
    {
        $today     = Carbon::today();
        $faculties = Faculty::orderBy('id')->get();

        // ── Confidence score formula ───────────────────────────────────
        // Base confidence decreases as prediction day gets farther out.
        // Also penalised when CO2 is near a classification boundary.
        $calcConf = function (float $co2, int $dayOffset): int {
            $dist    = min(abs($co2 - 1000.0), abs($co2 - 2000.0));
            $bonus   = min($dist / 200.0 * 10.0, 15.0);
            $penalty = $dist < 100 ? (100.0 - $dist) / 10.0 : 0.0;
            $base    = 91.0 - ($dayOffset * 0.32); // decays from 91% → ~62% over 90 days
            return (int) max(55, min(round($base + $bonus - $penalty), 91));
        };

        // ── Base samples per faculty (XGBoost v4 anchor values) ────────
        $facultySamples = [
            // FT   — high occupancy lecture halls
            ['co2' => 1235, 'temp' => 26.1, 'hum' => 62.1, 'drift' => 0.9, 'status_base' => 'Sedang'],
            // FEB  — busy admin + classrooms
            ['co2' => 1245, 'temp' => 26.6, 'hum' => 63.1, 'drift' => 0.8, 'status_base' => 'Sedang'],
            // FH   — moderate occupancy
            ['co2' =>  880, 'temp' => 25.6, 'hum' => 60.1, 'drift' => 0.4, 'status_base' => 'Baik'],
            // FK   — well-ventilated clinical areas
            ['co2' =>  714, 'temp' => 26.4, 'hum' => 64.1, 'drift' => 0.3, 'status_base' => 'Baik'],
            // FMIPA — labs with ventilation
            ['co2' =>  838, 'temp' => 25.8, 'hum' => 61.1, 'drift' => 0.4, 'status_base' => 'Baik'],
            // FPP  — outdoor-adjacent spaces
            ['co2' =>  806, 'temp' => 26.8, 'hum' => 65.1, 'drift' => 0.3, 'status_base' => 'Baik'],
            // FISIP — crowded seminar rooms
            ['co2' => 1190, 'temp' => 26.2, 'hum' => 63.5, 'drift' => 0.7, 'status_base' => 'Sedang'],
            // FPsi  — small group rooms
            ['co2' =>  720, 'temp' => 24.8, 'hum' => 62.0, 'drift' => 0.3, 'status_base' => 'Baik'],
            // FIB   — near boundary — uncertain
            ['co2' =>  960, 'temp' => 25.9, 'hum' => 61.8, 'drift' => 0.5, 'status_base' => 'Baik'],
            // FKM   — just above boundary
            ['co2' => 1050, 'temp' => 26.5, 'hum' => 63.2, 'drift' => 0.6, 'status_base' => 'Sedang'],
            // FPIK  — outdoor field areas
            ['co2' =>  875, 'temp' => 26.0, 'hum' => 64.5, 'drift' => 0.3, 'status_base' => 'Baik'],
            // SV    — vocational workshops
            ['co2' => 1320, 'temp' => 26.8, 'hum' => 62.8, 'drift' => 1.0, 'status_base' => 'Sedang'],
            // P1    — quiet postgrad offices
            ['co2' =>  755, 'temp' => 25.2, 'hum' => 60.5, 'drift' => 0.3, 'status_base' => 'Baik'],
        ];

        $statusFromCo2 = function (float $co2): string {
            if ($co2 <= 1000) return 'Baik';
            if ($co2 <= 2000) return 'Sedang';
            return 'Tidak Sehat';
        };

        foreach ($faculties->take(13) as $index => $faculty) {
            $s = $facultySamples[$index] ?? end($facultySamples);

            for ($day = 0; $day < 90; $day++) {
                $date = $today->copy()->addDays($day);

                // Deterministic variation using faculty index + day offset
                $wave    = sin(($index * 7 + $day) * 0.4) * 0.04;   // ±4% sinusoidal
                $co2     = round($s['co2'] + ($day * $s['drift']) + ($s['co2'] * $wave), 1);
                $temp    = round($s['temp'] + ($day * 0.008) + (sin($day * 0.3 + $index) * 0.3), 1);
                $hum     = round($s['hum']  - ($day * 0.02)  + (cos($day * 0.25 + $index) * 0.5), 1);
                $conf    = $calcConf($co2, $day);
                $status  = $statusFromCo2($co2);

                AirQualityPrediction::updateOrCreate(
                    [
                        'faculty_id'      => $faculty->id,
                        'prediction_type' => 'daily',
                        'predicted_for'   => $date->toDateTimeString(),
                    ],
                    [
                        'predicted_co2'         => $co2,
                        'predicted_temperature' => $temp,
                        'predicted_humidity'    => max(30, min(95, $hum)),
                        'predicted_status'      => $status,
                        'confidence_score'      => $conf,
                        'model_version'         => 'XGBoost v4',
                        'generated_at'          => now(),
                    ]
                );
            }
        }

        $this->command->info('AirQualityPredictionSeeder: 90-day predictions generated for all faculties.');
    }
}
