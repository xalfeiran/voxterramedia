<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\MediaOutlet;
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

    // Slug of the placeholder city Scout uses for country-wide outlets.
    private const NATIONAL_CITY_SLUG = 'national-online';

    /**
     * Aggregated news feed for an IATA airport/metro code, in three tiers:
     *   - local    : outlets in the cities sharing the code (DFW = Dallas + Fort Worth)
     *   - regional : outlets elsewhere in the same region(s)/state(s)
     *   - national : country-wide outlets (type "national" or the National/Online city)
     *
     * Each news item is tagged with its source's scope. Tiers can be narrowed
     * with ?scope=local,regional,national (default: all three).
     */
    public function news(Request $request, RssReader $rss, string $airport)
    {
        $code = strtoupper(trim($airport));

        $cities = City::with('region.country')->byAirport($code)->get();

        if ($cities->isEmpty()) {
            return response()->json([
                'data'    => [],
                'message' => 'No city found for airport code ' . $code . '.',
            ], 404);
        }

        $cityIds    = $cities->pluck('id');
        $regionIds  = $cities->pluck('region_id')->unique()->values();
        $countryIds = $cities->pluck('region.country_id')->unique()->values();

        // Which tiers were requested (default: all).
        $allScopes = ['local', 'regional', 'national'];
        $scopes = collect(explode(',', strtolower($request->get('scope', implode(',', $allScopes)))))
            ->map(fn($s) => trim($s))
            ->intersect($allScopes)
            ->values();
        if ($scopes->isEmpty()) {
            $scopes = collect($allScopes);
        }

        $outlets = MediaOutlet::query()
            ->with('city:id,region_id,slug')
            ->active()
            ->hasRss()
            ->whereNotNull('rss_url')
            ->where(function ($q) use ($scopes, $cityIds, $regionIds, $countryIds) {
                if ($scopes->contains('local')) {
                    $q->orWhereIn('city_id', $cityIds);
                }
                if ($scopes->contains('regional')) {
                    $q->orWhereHas('city', fn($c) => $c->whereIn('region_id', $regionIds));
                }
                if ($scopes->contains('national')) {
                    $q->orWhere(fn($n) =>
                        $n->whereHas('city.region', fn($r) => $r->whereIn('country_id', $countryIds))
                          ->where(fn($w) =>
                              $w->where('type', 'national')
                                ->orWhereHas('city', fn($c) => $c->where('slug', self::NATIONAL_CITY_SLUG))
                          )
                    );
                }
            })
            ->get(['id', 'city_id', 'name', 'slug', 'type', 'rss_url']);

        // Tag each outlet with its tier (national wins, then local, then regional).
        $classify = function ($o) use ($cityIds, $regionIds) {
            if ($o->type === 'national' || optional($o->city)->slug === self::NATIONAL_CITY_SLUG) {
                return 'national';
            }
            if ($cityIds->contains($o->city_id)) {
                return 'local';
            }
            if ($o->city && $regionIds->contains($o->city->region_id)) {
                return 'regional';
            }
            return 'national';
        };
        $outlets->each(fn($o) => $o->setAttribute('scope', $classify($o)));

        // Drop tiers the caller didn't ask for (national OR-branch can pull extras).
        $outlets = $outlets->filter(fn($o) => $scopes->contains($o->scope))->values();

        $limit   = min((int) $request->get('limit', 30), 100);
        $perFeed = min((int) $request->get('per_feed', 10), 25);

        // Cache the aggregated result briefly — fetching many remote feeds is slow.
        $cacheKey = "city-news:{$code}:{$scopes->sort()->implode(',')}:{$limit}:{$perFeed}";

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
                        'id'    => $o->id,
                        'name'  => $o->name,
                        'slug'  => $o->slug,
                        'type'  => $o->type,
                        'scope' => $o->scope,
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

        $country = $cities->first()->region->country;

        return response()->json([
            'data' => $items,
            'meta' => [
                'airport_code' => $code,
                'scopes'       => $scopes->values(),
                'cities'       => $cities->map(fn($c) => [
                    'id'   => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                ])->values(),
                'country'       => [
                    'code' => $country->code,
                    'name' => $country->name,
                ],
                'outlets_by_scope' => [
                    'local'    => $outlets->where('scope', 'local')->count(),
                    'regional' => $outlets->where('scope', 'regional')->count(),
                    'national' => $outlets->where('scope', 'national')->count(),
                ],
                'outlets_count' => $outlets->count(),
                'count'         => count($items),
            ],
        ]);
    }
}
