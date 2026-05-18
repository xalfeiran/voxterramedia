<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id', 'name', 'slug', 'latitude', 'longitude', 'population',
    ];

    protected $casts = [
        'latitude'   => 'float',
        'longitude'  => 'float',
        'population' => 'integer',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function mediaOutlets(): HasMany
    {
        return $this->hasMany(MediaOutlet::class);
    }
}
