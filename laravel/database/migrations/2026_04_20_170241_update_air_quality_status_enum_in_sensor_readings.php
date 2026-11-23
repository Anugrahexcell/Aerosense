<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update air_quality_status column to accept new labels:
     * Bagus, Sedang, Buruk
     * (replacing old: Baik, Tidak Sehat, Sangat Tidak Sehat, Berbahaya)
     */
    public function up(): void
    {
        // MODIFY COLUMN is MySQL-only; SQLite uses loose typing so no ALTER needed
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sensor_readings MODIFY COLUMN air_quality_status VARCHAR(30) NOT NULL DEFAULT 'Baik'");
        }

        // Backfill stored column with unified labels (works on all drivers)
        DB::statement("UPDATE sensor_readings SET air_quality_status = CASE
            WHEN co2 <= 1000 THEN 'Baik'
            WHEN co2 <= 2000 THEN 'Sedang'
            ELSE 'Tidak Sehat'
        END");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sensor_readings MODIFY COLUMN air_quality_status ENUM('Baik','Sedang','Tidak Sehat','Sangat Tidak Sehat','Berbahaya') NOT NULL DEFAULT 'Baik'");
        }
    }
};

