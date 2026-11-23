<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculty extends Model
{
    protected $fillable = [
        'name',
        'code',
        'location_description',
        'latitude',
        'longitude',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    public function airQualityTrends(): HasMany
    {
        return $this->hasMany(AirQualityTrend::class);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(AirQualityPrediction::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getLatestReadingAttribute(): ?SensorReading
    {
        return $this->sensorReadings()->latest('recorded_at')->first();
    }
}
