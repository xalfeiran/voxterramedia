<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'name_es', 'slug', 'flag_emoji',
        'latitude', 'longitude', 'default_zoom',
    ];

    protected $casts = [
        'latitude'     => 'float',
        'longitude'    => 'float',
        'default_zoom' => 'integer',
    ];

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }

    public function mediaOutlets(): HasMany
    {
        return $this->hasManyThrough(MediaOutlet::class, City::class, 'region_id', 'city_id')
            ->join('regions', 'cities.region_id', '=', 'regions.id')
            ->where('regions.country_id', $this->id);
    }
}
