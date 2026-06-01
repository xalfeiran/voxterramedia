<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id', 'name', 'slug', 'airport_code', 'latitude', 'longitude', 'population',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'population' => 'integer',
    ];

    // Always store/compare IATA codes uppercase (e.g. "dfw" -> "DFW").
    protected function airportCode(): Attribute
    {
        return Attribute::make(
            set: fn($value) => $value !== null ? strtoupper(trim($value)) : null,
        );
    }

    public function scopeByAirport(Builder $query, string $code): Builder
    {
        return $query->where('airport_code', strtoupper(trim($code)));
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function mediaOutlets(): HasMany
    {
        return $this->hasMany(MediaOutlet::class);
    }
}
