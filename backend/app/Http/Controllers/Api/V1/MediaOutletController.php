<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\MediaOutletRequest;
use App\Http\Resources\MediaOutletResource;
use App\Http\Resources\MediaOutletMapResource;
use App\Models\MediaOutlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

    public function feed(string $slug)
    {
        $outlet = MediaOutlet::where('slug', $slug)->firstOrFail();

        if (!$outlet->rss_url) {
            return response()->json(['data' => [], 'message' => 'No RSS feed available for this outlet.'], 200);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'VoxTerra.media/1.0 (RSS Reader)'])
                ->get($outlet->rss_url);

            if (!$response->successful()) {
                return response()->json(['data' => [], 'message' => 'Could not fetch RSS feed.'], 200);
            }

            $items = $this->parseRssFeed($response->body());

            return response()->json(['data' => $items]);
        } catch (\Exception $e) {
            return response()->json(['data' => [], 'message' => 'Failed to parse RSS feed.'], 200);
        }
    }

    private function parseRssFeed(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$doc) return [];

        $items = [];

        // ── RSS 2.0 ────────────────────────────────────────────────────────
        if (isset($doc->channel->item)) {
            foreach ($doc->channel->item as $item) {
                $ns       = $item->getNamespaces(true);
                $media    = isset($ns['media'])   ? $item->children($ns['media'])   : null;
                $content  = isset($ns['content']) ? $item->children($ns['content']) : null;

                $image = null;
                if ($media && isset($media->thumbnail)) {
                    $image = (string) $media->thumbnail->attributes()['url'] ?? null;
                } elseif ($media && isset($media->content)) {
                    $image = (string) $media->content->attributes()['url'] ?? null;
                } elseif ($content && isset($content->encoded)) {
                    preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) $content->encoded, $m);
                    $image = $m[1] ?? null;
                }

                $items[] = [
                    'title'       => (string) $item->title,
                    'link'        => (string) $item->link,
                    'description' => strip_tags((string) $item->description),
                    'pub_date'    => (string) $item->pubDate,
                    'image'       => $image,
                ];

                if (count($items) >= 10) break;
            }
            return $items;
        }

        // ── Atom ───────────────────────────────────────────────────────────
        $ns   = $doc->getNamespaces(true);
        $atom = $doc->children($ns[''] ?? 'http://www.w3.org/2005/Atom');

        foreach ($doc->entry ?? [] as $entry) {
            $link = '';
            foreach ($entry->link as $l) {
                $rel = (string) $l->attributes()['rel'];
                if ($rel === 'alternate' || $rel === '') {
                    $link = (string) $l->attributes()['href'];
                    break;
                }
            }

            $image = null;
            if (isset($entry->content)) {
                preg_match('/<img[^>]+src=["\']([^"\']+)["\']/', (string) $entry->content, $m);
                $image = $m[1] ?? null;
            }

            $items[] = [
                'title'       => (string) $entry->title,
                'link'        => $link,
                'description' => strip_tags((string) ($entry->summary ?? $entry->content ?? '')),
                'pub_date'    => (string) ($entry->updated ?? $entry->published ?? ''),
                'image'       => $image,
            ];

            if (count($items) >= 10) break;
        }

        return $items;
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
