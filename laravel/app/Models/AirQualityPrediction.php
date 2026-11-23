<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirQualityPrediction extends Model
{
    public $timestamps = false; // uses generated_at + created_at manually

    protected $fillable = [
        'faculty_id',
        'prediction_type',
        'predicted_for',
        'predicted_co2',
        'predicted_temperature',
        'predicted_humidity',
        'predicted_status',
        'confidence_score',
        'model_version',
        'generated_at',
    ];

    protected $casts = [
        'predicted_for'          => 'datetime',
        'predicted_co2'          => 'float',
        'predicted_temperature'  => 'float',
        'predicted_humidity'     => 'float',
        'confidence_score'       => 'float',
        'generated_at'           => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeHourly($query)
    {
        return $query->where('prediction_type', 'hourly');
    }

    public function scopeDaily($query)
    {
        return $query->where('prediction_type', 'daily');
    }

    public function scopeCampusWide($query)
    {
        return $query->whereNull('faculty_id');
    }

    // ── Accessors ──────────────────────────────────────────────

    /**
     * Returns a CSS class matching the predicted status.
     * Used in Blade for status badge styling on prediction cards.
     */
    public function getStatusClassAttribute(): string
    {
        // 3 categories aligned with XGBoost v4 zone classification
        return match ($this->predicted_status) {
            'Baik'         => 'baik',
            'Sedang'       => 'sedang',
            'Tidak Sehat'  => 'tidak-sehat',
            default        => 'baik',
        };
    }
}
