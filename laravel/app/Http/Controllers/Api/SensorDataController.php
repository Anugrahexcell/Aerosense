<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faculty;
use App\Models\SensorReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SensorDataController extends Controller
{
    /**
     * POST /api/send-data
     *
     * Accepts the exact payload format sent by the Arduino/ESP32:
     *   [{"Temp":"27.5","Hum":"65.2","CO2":"234","FacultyId":"1"}]
     *
     * Also supports a simpler flat format:
     *   {"Temp":"27.5","Hum":"65.2","CO2":"234","FacultyId":"1"}
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $body = $request->all();

            // Support both array-wrapped [{ ... }] and flat { ... } payloads
            $data = isset($body[0]) ? $body[0] : $body;

            // Validate required fields (case-insensitive key normalisation below)
            $temp     = $this->extractFloat($data, ['Temp', 'temp', 'temperature']);
            $hum      = $this->extractFloat($data, ['Hum', 'hum', 'humidity']);
            $co2      = $this->extractFloat($data, ['CO2', 'co2', 'Co2']);
            $facultyId = $this->extractFacultyId($data, $request);

            // Validate ranges
            if ($temp === null || $hum === null || $co2 === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields: Temp, Hum, CO2',
                ], 422);
            }

            if ($temp < -50 || $temp > 100) {
                return response()->json(['success' => false, 'message' => 'Temperature out of range'], 422);
            }
            if ($hum < 0 || $hum > 100) {
                return response()->json(['success' => false, 'message' => 'Humidity out of range'], 422);
            }

            // Determine air quality status from CO2 level
            $status = $this->calculateAirQuality((float)$co2, (float)$temp, (float)$hum);

            // ── Limit receiving to once per hour per faculty (exempt TEST node) ──
            $faculty = Faculty::find($facultyId);
            $isTestNode = $faculty && ($faculty->code === 'TEST' || str_contains(strtolower($faculty->name), 'test'));
            $lastReading = SensorReading::where('faculty_id', $facultyId)->latest('recorded_at')->first();
            $diffSeconds = $lastReading ? abs(now()->diffInSeconds($lastReading->recorded_at)) : 99999;
            if (!$isTestNode && $lastReading && $diffSeconds < 3600) {
                return response()->json([
                    'success'    => true,
                    'message'    => 'Data skipped (1 hour throttling active)',
                    'id'         => $lastReading->id,
                    'faculty_id' => $facultyId,
                    'status'     => $status,
                    'timestamp'  => now()->toIso8601String(),
                ], 201);
            }

            // Save to database
            $reading = SensorReading::create([
                'faculty_id'         => $facultyId,
                'temperature'        => round((float)$temp, 2),
                'humidity'           => round((float)$hum, 2),
                'co2'                => round((float)$co2, 2),
                'air_quality_status' => $status,
                'recorded_at'        => now(),
            ]);

            Log::info("Sensor data saved: Faculty #{$facultyId} | T={$temp} H={$hum} CO2={$co2}");


            return response()->json([
                'success'    => true,
                'message'    => 'Data received successfully',
                'id'         => $reading->id,
                'faculty_id' => $facultyId,
                'status'     => $status,
                'timestamp'  => $reading->recorded_at->toIso8601String(),
            ], 201);

        } catch (\Throwable $e) {
            Log::error('Sensor API error: ' . $e->getMessage(), ['payload' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/sensor-readings/latest
     * Returns the latest reading per faculty for the public viewer.
     */
    public function latest(): JsonResponse
    {
        $readings = SensorReading::with('faculty')
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('sensor_readings')
                    ->groupBy('faculty_id');
            })
            ->orderBy('faculty_id')
            ->get()
            ->map(fn ($r) => [
                'faculty_id'         => $r->faculty_id,
                'faculty_name'       => $r->faculty->name ?? 'Unknown',
                'temperature'        => $r->temperature,
                'humidity'           => $r->humidity,
                'co2'                => $r->co2,
                'air_quality_status' => $r->air_quality_status,
                'recorded_at'        => $r->recorded_at->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'data' => $readings]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    private function extractFloat(array $data, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($data[$key]) && $data[$key] !== '') {
                return (float)$data[$key];
            }
        }
        return null;
    }

    private function extractFacultyId(array $data, Request $request): int
    {
        // 1. Try from payload (FacultyId, faculty_id, facultyId)
        foreach (['FacultyId', 'faculty_id', 'facultyId', 'FId'] as $key) {
            if (!empty($data[$key])) {
                $val = $data[$key];
                $faculty = is_numeric($val)
                    ? Faculty::find((int)$val)
                    : Faculty::where('code', strtoupper($val))->first();
                if ($faculty) {
                    return $faculty->id;
                }
            }
        }

        // 2. Try from request header X-Faculty-Id
        if ($request->hasHeader('X-Faculty-Id')) {
            $faculty = Faculty::find((int)$request->header('X-Faculty-Id'));
            if ($faculty) {
                return $faculty->id;
            }
        }

        // 3. Fallback: first faculty in DB
        return Faculty::orderBy('id')->value('id') ?? 1;
    }

    private function calculateAirQuality(float $co2, float $temp, float $hum): string
    {
        // CO2-based air quality indicator:
        //   400–1,000 ppm  → Bagus
        //  1,000–2,000 ppm → Sedang
        //  2,000–5,000 ppm → Buruk
        if ($co2 <= 1000) return 'Bagus';
        if ($co2 <= 2000) return 'Sedang';
        return 'Buruk';
    }
}
