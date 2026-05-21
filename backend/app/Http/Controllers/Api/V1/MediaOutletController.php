<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaOutletRequest;
use App\Http\Resources\MediaOutletResource;
use App\Http\Resources\MediaOutletMapResource;
use App\Models\MediaOutlet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaOutletController extends Controller
{
    public function index(Request $request)
    {
        $query = MediaOutlet::with(['city.region.country'])
            ->active();

        // Filters
        if ($request->filled('country')) {
            $query->whereHas('city.region.country', fn($q) =>
                $q->where('code', strtoupper($request->country)));
        }
        if ($request->filled('region')) {
            $query->whereHas('city.region', fn($q) =>
                $q->where('id', $request->region));
        }
        if ($request->filled('city')) {
            $query->where('city_id', $request->city);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }
        if ($request->filled('featured')) {
            $query->featured();
        }
        if ($request->boolean('has_rss')) {
            $query->hasRss();
        }
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(fn($q) =>
                $q->where('name', 'like', $term)
                  ->orWhere('description', 'like', $term)
                  ->orWhereHas('city', fn($cq) => $cq->where('name', 'like', $term))
            );
        }
        if ($request->filled('bbox')) {
            [$minLon, $minLat, $maxLon, $maxLat] = explode(',', $request->bbox);
            $query->inBbox((float)$minLon, (float)$minLat, (float)$maxLon, (float)$maxLat);
        }

        $perPage = min((int)$request->get('per_page', 20), 100);
        $outlets = $query->orderBy('is_featured', 'desc')
                         ->orderBy('name')
                         ->paginate($perPage);

        return MediaOutletResource::collection($outlets)->additional([
            'meta' => ['filters_applied' => $request->only('country','region','city','type','language','search','featured','has_rss','bbox')],
        ]);
    }

    public function map()
    {
        $outlets = MediaOutlet::with(['city.region.country'])
            ->active()
            ->get(['id', 'city_id', 'name', 'slug', 'url', 'type', 'language', 'latitude', 'longitude', 'is_featured', 'has_rss']);

        return MediaOutletMapResource::collection($outlets);
    }

    public function show(string $slug)
    {
        $outlet = MediaOutlet::with(['city.region.country'])
            ->where('slug', $slug)
            ->firstOrFail();

        return new MediaOutletResource($outlet);
    }

    public function store(MediaOutletRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);
        $outlet = MediaOutlet::create($data);

        return (new MediaOutletResource($outlet->load('city.region.country')))
            ->response()->setStatusCode(201);
    }

    public function update(MediaOutletRequest $request, MediaOutlet $mediaOutlet)
    {
        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        $mediaOutlet->update($data);

        return new MediaOutletResource($mediaOutlet->load('city.region.country'));
    }

    public function destroy(MediaOutlet $mediaOutlet)
    {
        $mediaOutlet->delete();
        return response()->json(['message' => 'Outlet deleted.']);
    }

    public function toggleFeatured(MediaOutlet $mediaOutlet)
    {
        $mediaOutlet->update(['is_featured' => !$mediaOutlet->is_featured]);
        return new MediaOutletResource($mediaOutlet->load('city.region.country'));
    }
}
