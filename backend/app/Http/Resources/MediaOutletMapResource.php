<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaOutletMapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'url'         => $this->url,
            'type'        => $this->type,
            'language'    => $this->language,
            'is_featured' => $this->is_featured,
            'lat'         => $this->effective_latitude,
            'lon'         => $this->effective_longitude,
            'country'     => $this->city?->region?->country?->code,
            'city'        => $this->city?->name,
            'region'      => $this->city?->region?->name,
        ];
    }
}
