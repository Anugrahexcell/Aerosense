<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->decimal('co2', 8, 2)->comment('CO₂ in ppm');
            $table->decimal('temperature', 5, 2)->comment('Temperature in °C');
            $table->decimal('humidity', 5, 2)->comment('Humidity in %');
            $table->enum('air_quality_status', [
                'Baik',
                'Sedang',
                'Tidak Sehat',
                'Sangat Tidak Sehat',
                'Berbahaya',
            ])->default('Baik');
            $table->smallInteger('aqi_index')->nullable()->comment('Calculated AQI score');
            $table->timestamp('recorded_at')->useCurrent()->comment('When the IoT sensor recorded this');
            $table->timestamp('created_at')->useCurrent();

            // Composite index for fastest "latest per faculty" query
            $table->index(['faculty_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};
