<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaOutlet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'city_id', 'name', 'slug', 'url', 'type', 'language',
        'description', 'logo_url', 'founded_year',
        'is_active', 'is_featured', 'latitude', 'longitude',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_featured'  => 'boolean',
        'latitude'     => 'float',
        'longitude'    => 'float',
        'founded_year' => 'integer',
    ];

    // Enums
    const TYPES = ['national', 'newspaper', 'digital', 'tv', 'radio', 'magazine'];

    /**
     * Supported ISO 639-1 language codes.
     * Expanded from the original en/es/fr to support the worldwide catalog.
     */
    const LANGUAGES = [
        'en', // English
        'es', // Spanish
        'fr', // French
        'de', // German
        'pt', // Portuguese
        'ar', // Arabic
        'zh', // Chinese
        'ja', // Japanese
        'ru', // Russian
        'it', // Italian
        'nl', // Dutch
        'ko', // Korean
        'hi', // Hindi
        'tr', // Turkish
        'pl', // Polish
        'sv', // Swedish
        'fa', // Persian/Farsi
        'he', // Hebrew
        'id', // Indonesian
        'uk', // Ukrainian
        'sw', // Swahili
        'ms', // Malay
        'th', // Thai
        'vi', // Vietnamese
        'ur', // Urdu
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    // Accessor: falls back to city coordinates when outlet has none
    public function getEffectiveLatitudeAttribute(): ?float
    {
        return $this->latitude ?? $this->city?->latitude;
    }

    public function getEffectiveLongitudeAttribute(): ?float
    {
        return $this->longitude ?? $this->city?->longitude;
    }

    // Scope helpers
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInBbox($query, float $minLon, float $minLat, float $maxLon, float $maxLat)
    {
        return $query->whereHas('city', function ($q) use ($minLon, $minLat, $maxLon, $maxLat) {
            $q->whereBetween('latitude', [$minLat, $maxLat])
              ->whereBetween('longitude', [$minLon, $maxLon]);
        });
    }
}
