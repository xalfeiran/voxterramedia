<?php

namespace App\Services\Flight;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * NWS active weather alerts (CAP) for a geographic point.
 * Source: https://api.weather.gov/alerts/active?point=lat,lon  (GeoJSON, no auth;
 * a User-Agent header is required by the NWS API).
 */
class NwsAlerts
{
    private const URL        = 'https://api.weather.gov/alerts/active';
    private const CACHE_SECS = 120;

    /**
     * Active alerts near a point, simplified:
     * [ ['event','severity','urgency','headline','area','onset','expires'] ].
     */
    public function forPoint(float $lat, float $lon): array
    {
        $key = 'nws-alerts:' . round($lat, 3) . ',' . round($lon, 3);

        return Cache::remember($key, self::CACHE_SECS, function () use ($lat, $lon) {
            try {
                $resp = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'VoxTerra.media/1.0 (flight-impact; contact admin@voxterra.media)',
                        'Accept'     => 'application/geo+json',
                    ])
                    ->get(self::URL, ['point' => round($lat, 4) . ',' . round($lon, 4)]);

                if (!$resp->successful()) {
                    return [];
                }

                $features = $resp->json('features') ?? [];
                $alerts = [];
                foreach ($features as $f) {
                    $p = $f['properties'] ?? [];
                    $alerts[] = [
                        'event'    => $p['event']    ?? null,
                        'severity' => $p['severity'] ?? null,
                        'urgency'  => $p['urgency']  ?? null,
                        'headline' => $p['headline'] ?? null,
                        'area'     => $p['areaDesc'] ?? null,
                        'onset'    => $p['onset']    ?? null,
                        'expires'  => $p['expires']  ?? null,
                    ];
                }
                return $alerts;
            } catch (\Throwable $e) {
                return [];
            }
        });
    }
}
