<?php

namespace App\Services\Flight;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * AviationWeather.gov (AWC) METAR (current) + TAF (forecast) reader.
 * Source: https://aviationweather.gov/api/data/{metar,taf}?ids=...&format=json
 * No auth; a custom User-Agent is required to avoid filtering.
 */
class AviationWeather
{
    private const BASE       = 'https://aviationweather.gov/api/data';
    private const CACHE_SECS = 300;

    /**
     * Current conditions for a list of ICAO codes: [ ICAO => metar[] ].
     * Each: ['flight_category','wind_dir','wind_speed','visibility','wx','raw','report_time'].
     */
    public function metar(array $icaos): array
    {
        $rows = $this->fetch('metar', $icaos);
        $out  = [];
        foreach ($rows as $r) {
            $id = strtoupper((string) ($r['icaoId'] ?? ''));
            if ($id === '') {
                continue;
            }
            $out[$id] = [
                'flight_category' => $r['fltCat']   ?? null,
                'wind_dir'        => $r['wdir']     ?? null,
                'wind_speed'      => $r['wspd']     ?? null,
                'wind_gust'       => $r['wgst']     ?? null,
                'visibility'      => $r['visib']    ?? null,
                'temp_c'          => $r['temp']     ?? null,
                'raw'             => $r['rawOb']    ?? null,
                'report_time'     => $r['reportTime'] ?? null,
            ];
        }
        return $out;
    }

    /**
     * Forecasts for a list of ICAO codes: [ ICAO => ['raw'=>..,'storm_window'=>bool,'periods'=>[]] ].
     * storm_window flags any thunderstorm signal (TS/TSRA/VCTS) in the forecast.
     */
    public function taf(array $icaos): array
    {
        $rows = $this->fetch('taf', $icaos);
        $out  = [];
        foreach ($rows as $r) {
            $id = strtoupper((string) ($r['icaoId'] ?? ''));
            if ($id === '') {
                continue;
            }

            $periods = [];
            $storm   = false;
            foreach ($r['fcsts'] ?? [] as $f) {
                $wx = (string) ($f['wxString'] ?? '');
                if ($wx !== '' && preg_match('/TS|VCTS|TSRA/i', $wx)) {
                    $storm = true;
                }
                $periods[] = [
                    'from'       => $f['timeFrom'] ?? null,
                    'to'         => $f['timeTo'] ?? null,
                    'wind_speed' => $f['wspd'] ?? null,
                    'wind_gust'  => $f['wgst'] ?? null,
                    'visibility' => $f['visib'] ?? null,
                    'wx'         => $wx ?: null,
                ];
            }

            $out[$id] = [
                'raw'          => $r['rawTAF'] ?? null,
                'storm_window' => $storm,
                'periods'      => $periods,
            ];
        }
        return $out;
    }

    private function fetch(string $kind, array $icaos): array
    {
        $icaos = array_values(array_unique(array_filter(array_map(
            fn($c) => strtoupper(trim((string) $c)),
            $icaos
        ))));
        if (empty($icaos)) {
            return [];
        }

        $ids = implode(',', $icaos);
        $key = "awc-{$kind}:" . md5($ids);

        return Cache::remember($key, self::CACHE_SECS, function () use ($kind, $ids) {
            try {
                $resp = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'VoxTerra.media/1.0 (flight-impact)'])
                    ->get(self::BASE . "/{$kind}", ['ids' => $ids, 'format' => 'json']);

                if (!$resp->successful()) {
                    return [];
                }
                $json = $resp->json();
                return is_array($json) ? $json : [];
            } catch (\Throwable $e) {
                return [];
            }
        });
    }
}
