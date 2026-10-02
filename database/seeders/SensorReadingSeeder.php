<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\SensorReading;
use Illuminate\Database\Seeder;

class SensorReadingSeeder extends Seeder
{
    /**
     * Seeds 7 days of historical hourly readings for all 12 faculties.
     * This gives the chart meaningful data from day one.
     *
     * CO₂ classification (3-tier):
     *   400 – 1 000 ppm → Baik
     * 1 001 – 2 000 ppm → Sedang
     * 2 001 – 5 000 ppm → Buruk
     */
    public function run(): void
    {
        $faculties = Faculty::orderBy('id')->get();

        // Base CO₂ profile per faculty (realistic for UNDIP campus)
        $baseProfiles = [
            ['co2' => 750,  'temp' => 27.5, 'hum' => 64.0], // FT    — lecture halls
            ['co2' => 820,  'temp' => 27.8, 'hum' => 63.5], // FEB   — admin + classes
            ['co2' => 620,  'temp' => 26.8, 'hum' => 61.0], // FH    — moderate
            ['co2' => 550,  'temp' => 26.2, 'hum' => 65.0], // FK    — clinical, ventilated
            ['co2' => 680,  'temp' => 26.5, 'hum' => 62.0], // FMIPA — labs
            ['co2' => 590,  'temp' => 27.0, 'hum' => 66.0], // FPP   — outdoor-adjacent
            ['co2' => 780,  'temp' => 27.3, 'hum' => 63.0], // FISIP — seminar rooms
            ['co2' => 510,  'temp' => 25.8, 'hum' => 62.5], // FPsi  — small rooms
            ['co2' => 700,  'temp' => 26.6, 'hum' => 61.8], // FIB   — mixed
            ['co2' => 840,  'temp' => 27.1, 'hum' => 63.2], // FKM   — just above threshold
            ['co2' => 640,  'temp' => 26.9, 'hum' => 64.5], // FPIK  — outdoor field
            ['co2' => 1050, 'temp' => 27.6, 'hum' => 62.8], // SV    — vocational workshops
        ];

        $statusFromCo2 = function (float $co2): string {
            if ($co2 <= 1000) return 'Baik';
            if ($co2 <= 2000) return 'Sedang';
            return 'Buruk';
        };

        // Seed 7 days of hourly data (every hour = 168 rows per faculty)
        $daysBack = 7;
        $rows = [];

        foreach ($faculties->take(12) as $index => $faculty) {
            $profile = $baseProfiles[$index] ?? end($baseProfiles);

            for ($hoursAgo = $daysBack * 24; $hoursAgo >= 0; $hoursAgo--) {
                $recordedAt = now()->subHours($hoursAgo);
                $hour = (int) $recordedAt->format('G'); // 0–23

                // Time-of-day multiplier: peaks 08:00–14:00 (lecture hours)
                $dayMultiplier = ($hour >= 8 && $hour <= 14)
                    ? 1.0 + (0.15 * sin(($hour - 8) / 6 * M_PI))
                    : 0.85;

                // Add sinusoidal variation + small noise per reading
                $noise  = (mt_rand(-30, 30) / 1000); // ±3%
                $co2    = round($profile['co2'] * ($dayMultiplier + $noise), 1);
                $temp   = round($profile['temp'] + sin($hoursAgo * 0.3) * 0.4 + (mt_rand(-5, 5) / 10), 1);
                $hum    = round($profile['hum']  - cos($hoursAgo * 0.25) * 0.5 + (mt_rand(-5, 5) / 10), 1);

                $co2  = max(400, min(5000, $co2));
                $temp = max(22.0, min(38.0, $temp));
                $hum  = max(30.0, min(95.0, $hum));

                $rows[] = [
                    'faculty_id'         => $faculty->id,
                    'co2'                => $co2,
                    'temperature'        => $temp,
                    'humidity'           => $hum,
                    'air_quality_status' => $statusFromCo2($co2),
                    'aqi_index'          => (int) min(500, $co2 / 10),
                    'recorded_at'        => $recordedAt->toDateTimeString(),
                    'created_at'         => $recordedAt->toDateTimeString(),
                ];
            }
        }

        // Chunk inserts for performance
        foreach (array_chunk($rows, 500) as $chunk) {
            SensorReading::insert($chunk);
        }

        $total = count($rows);
        $this->command->info("SensorReadingSeeder: {$total} hourly readings inserted (7 days × 12 faculties).");
    }
}
