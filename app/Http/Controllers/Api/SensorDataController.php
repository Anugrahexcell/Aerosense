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

            // ── Limit receiving to once per hour per faculty ──
            $lastReading = SensorReading::where('faculty_id', $facultyId)->latest('recorded_at')->first();
            $diffSeconds = $lastReading ? abs(now()->diffInSeconds($lastReading->recorded_at)) : 99999;
            if ($lastReading && $diffSeconds < 3600) {
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
     * GET /api/nodes/clean-and-reassign
     *
     * Membersihkan Sekolah Pascasarjana secara permanen dan
     * memindahkan Node Prototype (Test) dari ID 14 ke ID 13.
     */
    public function cleanAndReassignNodes(): JsonResponse
    {
        try {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0');

            // 1. Hapus Sekolah Pascasarjana
            $pasca = Faculty::where('name', 'like', '%Pascasarjana%')
                ->orWhere('code', 'SPs')
                ->orWhere('id', 13)
                ->first();

            if ($pasca && (str_contains($pasca->name, 'Pascasarjana') || $pasca->code === 'SPs')) {
                SensorReading::where('faculty_id', $pasca->id)->delete();
                \App\Models\AirQualityTrend::where('faculty_id', $pasca->id)->delete();
                \App\Models\AirQualityPrediction::where('faculty_id', $pasca->id)->delete();
                $pasca->delete();
            }

            // 2. Pindahkan data Prototype (jika ada di ID 14 atau ID lain) ke ID 13
            $proto14 = Faculty::find(14);
            $protoName = Faculty::where('name', 'like', '%Prototype%')->first();
            $proto = $proto14 ?: $protoName;

            if ($proto) {
                SensorReading::where('faculty_id', $proto->id)->update(['faculty_id' => 13]);
                \App\Models\AirQualityTrend::where('faculty_id', $proto->id)->update(['faculty_id' => 13]);
                \App\Models\AirQualityPrediction::where('faculty_id', $proto->id)->update(['faculty_id' => 13]);
                $proto->delete();
            }

            // Buat / update ID 13 sebagai Node Prototype (Test)
            Faculty::unguarded(function () {
                Faculty::updateOrCreate(
                    ['id' => 13],
                    [
                        'name'      => 'Node Prototype (Test)',
                        'code'      => 'TEST',
                        'is_active' => true,
                    ]
                );
            });

            // Pastikan ID 14 terhapus
            Faculty::where('id', 14)->delete();

            \Illuminate\Support\Facades\DB::statement('ALTER TABLE faculties AUTO_INCREMENT = 14');
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $allFaculties = Faculty::orderBy('id')->get(['id', 'name', 'code', 'is_active']);

            return response()->json([
                'success' => true,
                'message' => 'Sekolah Pascasarjana berhasil dihapus permanen. Node Prototype (Test) berhasil dipindahkan ke ID 13.',
                'nodes'   => $allFaculties,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui node: ' . $e->getMessage(),
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

    /**
     * POST /api/nodes/{facultyId}/data
     *
     * Endpoint per-node: FacultyId diambil langsung dari URL, TIDAK perlu dikirim di body.
     * Payload cukup: { "Temp": "27.5", "Hum": "65.2", "CO2": "850" }
     *
     * Contoh penggunaan di ESP32 Fakultas Teknik (ID=1):
     *   http.begin("http://server/api/nodes/1/data");
     *
     * Contoh penggunaan di ESP32 Sekolah Vokasi (ID=12):
     *   http.begin("http://server/api/nodes/12/data");
     */
    public function storeByNode(Request $request, int $facultyId): JsonResponse
    {
        // Pastikan node/fakultas benar-benar ada di database
        $faculty = Faculty::find($facultyId);
        if (! $faculty) {
            return response()->json([
                'success' => false,
                'message' => "Node tidak ditemukan: Faculty ID {$facultyId} tidak ada di database.",
                'hint'    => 'Cek daftar ID yang valid di GET /api/nodes/list',
            ], 404);
        }

        if (! $faculty->is_active) {
            return response()->json([
                'success' => false,
                'message' => "Node Faculty ID {$facultyId} ({$faculty->name}) sedang nonaktif.",
            ], 403);
        }

        try {
            $body = $request->all();
            $data = isset($body[0]) ? $body[0] : $body;

            $temp = $this->extractFloat($data, ['Temp', 'temp', 'temperature']);
            $hum  = $this->extractFloat($data, ['Hum', 'hum', 'humidity']);
            $co2  = $this->extractFloat($data, ['CO2', 'co2', 'Co2']);

            if ($temp === null || $hum === null || $co2 === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field wajib tidak lengkap. Kirimkan: Temp, Hum, CO2',
                    'example' => ['Temp' => 27.5, 'Hum' => 65.2, 'CO2' => 850],
                ], 422);
            }

            if ($temp < -50 || $temp > 100) {
                return response()->json(['success' => false, 'message' => 'Suhu di luar rentang (-50 s/d 100°C)'], 422);
            }
            if ($hum < 0 || $hum > 100) {
                return response()->json(['success' => false, 'message' => 'Kelembaban di luar rentang (0–100%)'], 422);
            }

            $status = $this->calculateAirQuality((float) $co2, (float) $temp, (float) $hum);

            // Throttle: maksimal 1 data per jam per node
            $lastReading = SensorReading::where('faculty_id', $facultyId)->latest('recorded_at')->first();
            $diffSeconds = $lastReading ? abs(now()->diffInSeconds($lastReading->recorded_at)) : 99999;
            if ($lastReading && $diffSeconds < 3600) {
                $sisaMenit = (int) ceil((3600 - $diffSeconds) / 60);
                return response()->json([
                    'success'      => true,
                    'message'      => "Data diskip — throttle aktif. Kirim lagi dalam {$sisaMenit} menit.",
                    'node'         => $faculty->name,
                    'faculty_id'   => $facultyId,
                    'next_allowed' => now()->addSeconds(3600 - $diffSeconds)->toIso8601String(),
                ], 201);
            }

            $reading = SensorReading::create([
                'faculty_id'         => $facultyId,
                'temperature'        => round((float) $temp, 2),
                'humidity'           => round((float) $hum, 2),
                'co2'                => round((float) $co2, 2),
                'air_quality_status' => $status,
                'recorded_at'        => now(),
            ]);

            Log::info("[Node #{$facultyId}] {$faculty->name} | T={$temp} H={$hum} CO2={$co2} | Status={$status}");

            return response()->json([
                'success'    => true,
                'message'    => 'Data berhasil diterima',
                'id'         => $reading->id,
                'node'       => $faculty->name,
                'faculty_id' => $facultyId,
                'status'     => $status,
                'timestamp'  => $reading->recorded_at->toIso8601String(),
            ], 201);

        } catch (\Throwable $e) {
            Log::error("[Node #{$facultyId}] Error: " . $e->getMessage(), ['payload' => $request->all()]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/nodes/{facultyId}/latest
     * Mengembalikan data sensor terbaru untuk satu node/fakultas tertentu.
     */
    public function latestByNode(int $facultyId): JsonResponse
    {
        $faculty = Faculty::find($facultyId);
        if (! $faculty) {
            return response()->json([
                'success' => false,
                'message' => "Faculty ID {$facultyId} tidak ditemukan.",
            ], 404);
        }

        $reading = SensorReading::where('faculty_id', $facultyId)
            ->latest('recorded_at')
            ->first();

        if (! $reading) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data untuk node ini.',
                'node'    => $faculty->name,
                'data'    => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'node'    => [
                'faculty_id'   => $faculty->id,
                'faculty_name' => $faculty->name,
                'code'         => $faculty->code,
            ],
            'data' => [
                'temperature'        => $reading->temperature,
                'humidity'           => $reading->humidity,
                'co2'                => $reading->co2,
                'air_quality_status' => $reading->air_quality_status,
                'recorded_at'        => $reading->recorded_at->toIso8601String(),
            ],
        ]);
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
                $faculty = Faculty::find((int)$data[$key]);
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
