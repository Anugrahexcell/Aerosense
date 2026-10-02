<?php

namespace App\Console\Commands;

use App\Models\Faculty;
use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateHourlySensorReadings extends Command
{
    protected $signature   = 'sensor:generate-hourly
                                {--faculty=      : Generate only for a specific faculty ID}
                                {--quiet-mode    : Suppress per-faculty output}
                                {--backfill=0    : Backfill the last N hours (e.g. --backfill=8 fills 03:00–10:00)}
                                {--at=           : Generate for a specific datetime (Y-m-d H:i, overrides --backfill)}';

    protected $description = 'Generate one hourly sensor reading per active faculty. '
                           . 'Runs automatically every hour via Laravel scheduler. '
                           . 'Use --backfill=N to fill missing hours, or --at="Y-m-d H:i" for a specific time.';

    /**
     * Base CO₂ profiles per faculty (keyed by code from FacultySeeder).
     *
     * CO₂ thresholds:
     *   400 – 1 000 ppm → Baik
     * 1 001 – 2 000 ppm → Sedang
     * 2 001 – 5 000 ppm → Buruk
     */
    private array $baseProfiles = [
        'FT'    => ['co2' => 750,  'temp' => 27.5, 'hum' => 64.0],
        'FEB'   => ['co2' => 820,  'temp' => 27.8, 'hum' => 63.5],
        'FH'    => ['co2' => 620,  'temp' => 26.8, 'hum' => 61.0],
        'FK'    => ['co2' => 550,  'temp' => 26.2, 'hum' => 65.0],
        'FMIPA' => ['co2' => 680,  'temp' => 26.5, 'hum' => 62.0],
        'FPP'   => ['co2' => 590,  'temp' => 27.0, 'hum' => 66.0],
        'FISIP' => ['co2' => 780,  'temp' => 27.3, 'hum' => 63.0],
        'FPsi'  => ['co2' => 510,  'temp' => 25.8, 'hum' => 62.5],
        'FIB'   => ['co2' => 700,  'temp' => 26.6, 'hum' => 61.8],
        'FKM'   => ['co2' => 840,  'temp' => 27.1, 'hum' => 63.2],
        'FPIK'  => ['co2' => 640,  'temp' => 26.9, 'hum' => 64.5],
        'SV'    => ['co2' => 1050, 'temp' => 27.6, 'hum' => 62.8],
    ];

    public function handle(): int
    {
        $faculties = Faculty::where('is_active', true)
            ->when($this->option('faculty'), fn ($q) => $q->where('id', $this->option('faculty')))
            ->orderBy('id')
            ->get();

        if ($faculties->isEmpty()) {
            $this->warn('No active faculties found.');
            return self::FAILURE;
        }

        // ── Determine which timestamps to generate for ──────────────
        $timestamps = $this->resolveTimestamps();

        $totalRows = 0;

        foreach ($timestamps as $at) {
            $count = $this->generateForTimestamp($at, $faculties);
            $totalRows += $count;
        }

        $this->info(sprintf(
            '✓ %d readings inserted across %d hour(s) for %d faculties.',
            $totalRows,
            count($timestamps),
            $faculties->count(),
        ));

        return self::SUCCESS;
    }

    // ── Resolve which hours to generate ────────────────────────────────
    private function resolveTimestamps(): array
    {
        // --at="2026-05-22 08:00" → single specific timestamp
        if ($at = $this->option('at')) {
            return [Carbon::parse($at)];
        }

        // --backfill=N → generate for the last N complete hours
        $n = (int) $this->option('backfill');
        if ($n > 0) {
            $timestamps = [];
            // Start from N hours ago up to (but not including) the current hour
            for ($i = $n; $i >= 1; $i--) {
                $timestamps[] = now()->startOfHour()->subHours($i);
            }
            return $timestamps;
        }

        // Default: current hour (normal scheduler run)
        return [now()];
    }

    // ── Generate readings for one timestamp ────────────────────────────
    private function generateForTimestamp(Carbon $at, $faculties): int
    {
        $hour = (int) $at->format('G'); // 0–23

        // Time-of-day CO₂ multiplier: peaks during lecture hours (08:00–14:00)
        $dayMultiplier = ($hour >= 8 && $hour <= 14)
            ? 1.0 + (0.15 * sin(($hour - 8) / 6 * M_PI))
            : 0.85;

        $rows = [];

        foreach ($faculties as $faculty) {
            $profile = $this->baseProfiles[$faculty->code] ?? $this->baseProfiles['SV'];

            // Use the last stored reading BEFORE this timestamp for continuity
            $lastReading = SensorReading::where('faculty_id', $faculty->id)
                ->where('recorded_at', '<', $at->toDateTimeString())
                ->latest('recorded_at')
                ->first();

            $baseCo2  = $lastReading ? $lastReading->co2          : $profile['co2'];
            $baseTemp = $lastReading ? $lastReading->temperature   : $profile['temp'];
            $baseHum  = $lastReading ? $lastReading->humidity      : $profile['hum'];

            // Mean-reversion drift toward profile baseline + small noise
            $targetCo2 = $profile['co2'] * $dayMultiplier;
            $co2  = $baseCo2  + ($targetCo2 - $baseCo2) * 0.3 + (mt_rand(-30, 30) / 1000 * $baseCo2);
            $temp = $baseTemp + (mt_rand(-15, 15) / 100);   // ±0.15 °C
            $hum  = $baseHum  + (mt_rand(-20, 20) / 100);   // ±0.20 %

            // Clamp to realistic ranges
            $co2  = round(max(400,  min(5000, $co2)),  1);
            $temp = round(max(22.0, min(38.0, $temp)), 1);
            $hum  = round(max(30.0, min(95.0, $hum)),  1);

            $status = $this->statusFromCo2($co2);

            $rows[] = [
                'faculty_id'         => $faculty->id,
                'co2'                => $co2,
                'temperature'        => $temp,
                'humidity'           => $hum,
                'air_quality_status' => $status,
                'aqi_index'          => (int) min(500, $co2 / 10),
                'recorded_at'        => $at->toDateTimeString(),
                'created_at'         => $at->toDateTimeString(),
            ];

            if (! $this->option('quiet-mode')) {
                $this->line(sprintf(
                    '  [%s] <fg=cyan>%-8s</> CO₂: <fg=%s>%6.1f ppm</> | %4.1f°C | %4.1f%% | <fg=%s>%s</>',
                    $at->format('d/m H:i'),
                    $faculty->code,
                    $co2 <= 1000 ? 'green' : ($co2 <= 2000 ? 'yellow' : 'red'),
                    $co2, $temp, $hum,
                    $co2 <= 1000 ? 'green' : ($co2 <= 2000 ? 'yellow' : 'red'),
                    $status,
                ));
            }
        }

        SensorReading::insert($rows);
        return count($rows);
    }

    private function statusFromCo2(float $co2): string
    {
        if ($co2 <= 1000) return 'Baik';
        if ($co2 <= 2000) return 'Sedang';
        return 'Buruk';
    }
}
