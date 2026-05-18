<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'code'           => $this->code,
            'name'           => $this->name,
            'name_es'        => $this->name_es,
            'slug'           => $this->slug,
            'flag_emoji'     => $this->flag_emoji,
            'latitude'       => $this->latitude,
            'longitude'      => $this->longitude,
            'default_zoom'   => $this->default_zoom,
            'regions_count'  => $this->regions_count ?? null,
            'outlets_count'  => $this->media_outlets_count ?? null,
            'regions'        => $this->whenLoaded('regions', fn() =>
                $this->regions->map(fn($r) => [
                    'id'      => $r->id,
                    'name'    => $r->name,
                    'name_es' => $r->name_es,
                    'slug'    => $r->slug,
                    'cities'  => $r->relationLoaded('cities') ? $r->cities->map(fn($c) => [
                        'id'   => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                    ]) : [],
                ])
            ),
        ];
    }
}
