<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirQualityTrend extends Model
{
    protected $fillable = [
        'faculty_id',
        'recorded_date',
        'avg_co',
        'avg_co2',
        'avg_temperature',
        'avg_humidity',
        'avg_aqi',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'recorded_date'   => 'date',
        'avg_co'          => 'float',
        'avg_co2'         => 'float',
        'avg_temperature' => 'float',
        'avg_humidity'    => 'float',
        'avg_aqi'         => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    /** Campus-wide aggregate records (no faculty) — used for dashboard chart */
    public function scopeCampusWide($query)
    {
        return $query->whereNull('faculty_id');
    }

    /** Filter by number of past days */
    public function scopeLastDays($query, int $days = 30)
    {
        return $query->where('recorded_date', '>=', now()->subDays($days)->toDateString());
    }
}
