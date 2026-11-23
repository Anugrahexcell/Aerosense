<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\SensorReading;
use Illuminate\Database\Seeder;

class SensorReadingSeeder extends Seeder
{
    public function run(): void
    {
        $faculties = Faculty::all();

        // Sample readings matching the UI/UX design values
        $samples = [
            ['co2' => 412, 'temperature' => 28, 'humidity' => 65, 'status' => 'Baik'],
            ['co2' => 405, 'temperature' => 27, 'humidity' => 68, 'status' => 'Baik'],
            ['co2' => 485, 'temperature' => 29, 'humidity' => 70, 'status' => 'Sedang'],
            ['co2' => 395, 'temperature' => 26, 'humidity' => 62, 'status' => 'Baik'],
            ['co2' => 520, 'temperature' => 30, 'humidity' => 72, 'status' => 'Sedang'],
            ['co2' => 580, 'temperature' => 31, 'humidity' => 75, 'status' => 'Tidak Sehat'],
            ['co2' => 430, 'temperature' => 28, 'humidity' => 64, 'status' => 'Baik'],
            ['co2' => 390, 'temperature' => 26, 'humidity' => 61, 'status' => 'Baik'],
            ['co2' => 460, 'temperature' => 29, 'humidity' => 67, 'status' => 'Sedang'],
            ['co2' => 410, 'temperature' => 27, 'humidity' => 63, 'status' => 'Baik'],
            ['co2' => 445, 'temperature' => 28, 'humidity' => 66, 'status' => 'Baik'],
            ['co2' => 398, 'temperature' => 27, 'humidity' => 64, 'status' => 'Baik'],
            ['co2' => 422, 'temperature' => 28, 'humidity' => 65, 'status' => 'Baik'],
        ];

        foreach ($faculties as $index => $faculty) {
            $sample = $samples[$index % count($samples)];
            SensorReading::create([
                'faculty_id'         => $faculty->id,
                'co2'                => $sample['co2'],
                'temperature'        => $sample['temperature'],
                'humidity'           => $sample['humidity'],
                'air_quality_status' => $sample['status'],
                'aqi_index'          => (int) ($sample['co2'] / 5),
                'recorded_at'        => now()->subMinutes(rand(1, 10)),
            ]);
        }
    }
}
