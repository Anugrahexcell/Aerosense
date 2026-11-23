<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    public $timestamps = false; // uses recorded_at + created_at manually

    protected $fillable = [
        'faculty_id',
        'co2',
        'temperature',
        'humidity',
        'air_quality_status',
        'aqi_index',
        'recorded_at',
    ];

    protected $casts = [
        'co2'         => 'float',
        'temperature' => 'float',
        'humidity'    => 'float',
        'aqi_index'   => 'integer',
        'recorded_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    /**
     * Returns the latest reading per faculty using a subquery.
     * Used by DashboardController to build the real-time grid.
     */
    public function scopeLatestPerFaculty(Builder $query): Builder
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('MAX(id)')
                ->from('sensor_readings')
                ->groupBy('faculty_id');
        })->orderBy('faculty_id');
    }

    /**
     * Compute air quality status live from the current CO2 reading.
     * Aligned with XGBoost v4 zone thresholds and AirQualityPrediction labels.
     *   ≤ 1,000 ppm  → Baik
     *  1,001–2,000 ppm → Sedang
     *  > 2,000 ppm  → Tidak Sehat
     */
    public function getComputedStatusAttribute(): string
    {
        if ($this->co2 <= 1000) return 'Baik';
        if ($this->co2 <= 2000) return 'Sedang';
        return 'Tidak Sehat';
    }

    /**
     * Returns the CSS class based on live computed status (not stored string).
     */
    public function getStatusClassAttribute(): string
    {
        return match ($this->computed_status) {
            'Baik'        => 'baik',
            'Sedang'      => 'sedang',
            'Tidak Sehat' => 'tidak-sehat',
            default       => 'baik',
        };
    }
}
