<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_quality_trends', function (Blueprint $table) {
            $table->id();
            // NULL faculty_id = campus-wide aggregate (used in dashboard chart)
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->date('recorded_date')->comment('The calendar date this entry represents');
            $table->decimal('avg_co', 8, 2)->nullable()->comment('Average CO in ppm');
            $table->decimal('avg_co2', 8, 2)->nullable()->comment('Average CO₂ in ppm');
            $table->decimal('avg_temperature', 5, 2)->nullable()->comment('Average temperature °C');
            $table->decimal('avg_humidity', 5, 2)->nullable()->comment('Average humidity %');
            $table->smallInteger('avg_aqi')->nullable();
            $table->text('notes')->nullable()->comment('Admin notes for this entry');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Admin user ID');
            $table->timestamps();

            $table->index(['faculty_id', 'recorded_date']);
            $table->unique(['faculty_id', 'recorded_date']); // one entry per faculty per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('air_quality_trends');
    }
};
