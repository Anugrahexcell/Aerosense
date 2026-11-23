<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Order matters — faculties must exist before FK-dependent tables
        $this->call([
            AdminUserSeeder::class,
            FacultySeeder::class,
            SensorReadingSeeder::class,
            AirQualityTrendSeeder::class,
            EducationArticleSeeder::class,
            AirQualityPredictionSeeder::class,
        ]);
    }
}
