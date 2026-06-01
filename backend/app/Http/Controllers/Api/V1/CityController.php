<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Services\RssReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
                'airport_code'   => $c->airport_code,
                'latitude'       => $c->latitude,
                'longitude'      => $c->longitude,
                'outlets_count'  => $c->media_outlets_count,
                'region'         => ['id' => $c->region->id, 'name' => $c->region->name],
                'country'        => ['code' => $c->region->country->code, 'name' => $c->region->country->name],
            ]),
        ]);
    }

    /**
     * Aggregated news feed for a city, looked up by IATA airport/metro code.
     * e.g. GET /api/v1/cities/DFW/news  ->  merged items from every active,
     * RSS-enabled outlet in Dallas–Fort Worth, newest first.
     */
    public function news(Request $request, RssReader $rss, string $airport)
    {
        $city = City::with('region.country')->byAirport($airport)->first();

        if (!$city) {
            return response()->json([
                'data'    => [],
                'message' => 'No city found for airport code ' . strtoupper(trim($airport)) . '.',
            ], 404);
        }

        $outlets = $city->mediaOutlets()
            ->active()
            ->hasRss()
            ->whereNotNull('rss_url')
            ->get(['id', 'city_id', 'name', 'slug', 'type', 'rss_url']);

        $limit   = min((int) $request->get('limit', 30), 100);
        $perFeed = min((int) $request->get('per_feed', 10), 25);

        // Cache the aggregated result briefly — fetching many remote feeds is slow.
        $cacheKey = "city-news:{$city->id}:{$limit}:{$perFeed}";

        $items = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($outlets, $rss, $limit, $perFeed) {
            if ($outlets->isEmpty()) {
                return [];
            }

            // Fetch every outlet's feed in parallel.
            $responses = Http::pool(fn ($pool) =>
                $outlets->map(fn ($o) =>
                    $pool->as((string) $o->id)
                        ->timeout(10)
                        ->withHeaders(['User-Agent' => 'VoxTerra.media/1.0 (RSS Reader)'])
                        ->get($o->rss_url)
                )->all()
            );

            $merged = [];
            foreach ($outlets as $o) {
                $resp = $responses[(string) $o->id] ?? null;

                // A failed request in a pool is returned as an exception instance.
                if (!$resp || $resp instanceof \Throwable || !$resp->successful()) {
                    continue;
                }

                foreach ($rss->parse($resp->body(), $perFeed) as $item) {
                    $item['source'] = [
                        'id'   => $o->id,
                        'name' => $o->name,
                        'slug' => $o->slug,
                        'type' => $o->type,
                    ];
                    $item['_ts'] = strtotime($item['pub_date'] ?? '') ?: 0;
                    $merged[]    = $item;
                }
            }

            // Newest first, then trim to the requested limit.
            usort($merged, fn($a, $b) => $b['_ts'] <=> $a['_ts']);
            $merged = array_slice($merged, 0, $limit);

            return array_map(function ($i) {
                unset($i['_ts']);
                return $i;
            }, $merged);
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'city' => [
                    'id'           => $city->id,
                    'name'         => $city->name,
                    'slug'         => $city->slug,
                    'airport_code' => $city->airport_code,
                ],
                'country'       => [
                    'code' => $city->region->country->code,
                    'name' => $city->region->country->name,
                ],
                'outlets_count' => $outlets->count(),
                'count'         => count($items),
            ],
        ]);
    }
}
