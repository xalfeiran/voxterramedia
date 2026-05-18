<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('region.country')->withCount('mediaOutlets');

        if ($request->filled('region')) {
            $query->where('region_id', $request->region);
        }

        if ($request->filled('country')) {
            $query->whereHas('region.country', fn($q) =>
                $q->where('code', strtoupper($request->country)));
        }

        return response()->json([
            'data' => $query->orderBy('name')->get()->map(fn($c) => [
                'id'             => $c->id,
                'name'           => $c->name,
                'slug'           => $c->slug,
                'latitude'       => $c->latitude,
                'longitude'      => $c->longitude,
                'outlets_count'  => $c->media_outlets_count,
                'region'         => ['id' => $c->region->id, 'name' => $c->region->name],
                'country'        => ['code' => $c->region->country->code, 'name' => $c->region->country->name],
            ]),
        ]);
    }
}
