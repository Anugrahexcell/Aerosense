<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EducationArticle extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'external_url',
        'excerpt',
        'content',
        'category',
        'reading_time_minutes',
        'cover_image',
        'icon_type',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'is_published'          => 'boolean',
        'published_at'          => 'datetime',
        'reading_time_minutes'  => 'integer',
    ];

    // ── Scopes ─────────────────────────────────────────────────

    /** Only return articles visible to the public viewer */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    // ── Accessors ──────────────────────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
