<?php

namespace Database\Seeders;

use App\Models\AirQualityTrend;
use Illuminate\Database\Seeder;

class AirQualityTrendSeeder extends Seeder
{
    public function run(): void
    {
        // Seed 30 days of campus-wide historical trend data (faculty_id = null)
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();

            // Simulate realistic trend fluctuations
            $baseCo2  = 420 + rand(-30, 40);
            $baseTemp = 28  + rand(-2, 3);
            $baseHum  = 65  + rand(-5, 8);

            AirQualityTrend::firstOrCreate(
                ['faculty_id' => null, 'recorded_date' => $date],
                [
                    'avg_co'          => round($baseCo2 * 0.08, 2),
                    'avg_co2'         => $baseCo2,
                    'avg_temperature' => $baseTemp,
                    'avg_humidity'    => $baseHum,
                    'avg_aqi'         => (int) ($baseCo2 / 5),
                    'notes'           => 'Seeded: campus-wide monthly trend',
                ]
            );
        }
    }
}
