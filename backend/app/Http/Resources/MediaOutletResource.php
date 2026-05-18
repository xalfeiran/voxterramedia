<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaOutletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'url'          => $this->url,
            'type'         => $this->type,
            'language'     => $this->language,
            'description'  => $this->description,
            'logo_url'     => $this->logo_url,
            'founded_year' => $this->founded_year,
            'is_active'    => $this->is_active,
            'is_featured'  => $this->is_featured,
            'latitude'     => $this->effective_latitude,
            'longitude'    => $this->effective_longitude,
            'city'         => [
                'id'   => $this->city?->id,
                'name' => $this->city?->name,
                'slug' => $this->city?->slug,
            ],
            'region'       => [
                'id'      => $this->city?->region?->id,
                'name'    => $this->city?->region?->name,
                'name_es' => $this->city?->region?->name_es,
                'code'    => $this->city?->region?->code,
                'slug'    => $this->city?->region?->slug,
            ],
            'country'      => [
                'code'      => $this->city?->region?->country?->code,
                'name'      => $this->city?->region?->country?->name,
                'name_es'   => $this->city?->region?->country?->name_es,
                'flag_emoji'=> $this->city?->region?->country?->flag_emoji,
            ],
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
