<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('air_quality_predictions', function (Blueprint $table) {
            $table->id();
            // NULL faculty_id = campus-wide hourly/daily prediction
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->enum('prediction_type', ['hourly', 'daily']);
            $table->dateTime('predicted_for')->comment('The future datetime this prediction targets');
            $table->decimal('predicted_co2', 8, 2)->comment('Predicted CO₂ in ppm');
            $table->decimal('predicted_temperature', 5, 2)->comment('Predicted temperature °C');
            $table->decimal('predicted_humidity', 5, 2)->comment('Predicted humidity %');
            $table->enum('predicted_status', [
                'Baik',
                'Sedang',
                'Tidak Sehat',
                'Sangat Tidak Sehat',
                'Berbahaya',
            ])->default('Baik');
            $table->decimal('confidence_score', 5, 2)->default(0)->comment('Model confidence 0-100%');
            $table->string('model_version', 30)->default('AeroSense v2');
            $table->timestamp('generated_at')->useCurrent()->comment('When the ML model generated this');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['faculty_id', 'prediction_type', 'predicted_for'], 'aq_predictions_fac_type_time_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('air_quality_predictions');
    }
};
