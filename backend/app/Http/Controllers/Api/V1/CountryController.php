<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::withCount(['regions', 'mediaOutlets' => fn($q) => $q->active()])
            ->orderBy('name')
            ->get();

        return CountryResource::collection($countries);
    }

    public function show(string $code)
    {
        $country = Country::where('code', strtoupper($code))
            ->with(['regions.cities'])
            ->withCount(['regions'])
            ->firstOrFail();

        return new CountryResource($country);
    }
}
