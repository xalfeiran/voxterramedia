<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $query = Region::with('country')->withCount('cities');

        if ($request->filled('country')) {
            $query->whereHas('country', fn($q) =>
                $q->where('code', strtoupper($request->country)));
        }

        return response()->json([
            'data' => $query->orderBy('name')->get()->map(fn($r) => [
                'id'          => $r->id,
                'name'        => $r->name,
                'name_es'     => $r->name_es,
                'slug'        => $r->slug,
                'code'        => $r->code,
                'cities_count'=> $r->cities_count,
                'country'     => [
                    'code'  => $r->country->code,
                    'name'  => $r->country->name,
                    'emoji' => $r->country->flag_emoji,
                ],
            ]),
        ]);
    }
}
