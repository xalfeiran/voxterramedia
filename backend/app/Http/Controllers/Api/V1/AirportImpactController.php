<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Flight\AirportReference;
use App\Services\Flight\AviationWeather;
use App\Services\Flight\FaaNasStatus;
use App\Services\Flight\NwsAlerts;
use Illuminate\Http\Request;

/**
 * Operational flight-impact status for an airport (Tier-1 feeds).
 *
 *   GET /api/v1/airports/DFW/impact
 *   GET /api/v1/airports/DFW/impact?destinations=ORD,LGA,DEN
 *
 * Combines FAA NAS status (delays/ground stops/closures), AviationWeather
 * METAR + TAF, and NWS active alerts for the origin and any destinations,
 * and assigns each a 0-100 impact score with a coarse level.
 */
class AirportImpactController extends Controller
{
    public function __construct(
        private AirportReference $airports,
        private FaaNasStatus $faa,
        private AviationWeather $awc,
        private NwsAlerts $nws,
    ) {}

    public function impact(Request $request, string $iata)
    {
        $origin = $this->airports->get($iata);
        if (!$origin) {
            return response()->json([
                'message' => 'Unknown airport code "' . strtoupper(trim($iata)) . '". '
                    . 'Add it to resources/data/airports.json to enable.',
            ], 404);
        }

        // Optional destination set (?destinations=ORD,LGA).
        $destInput = collect(explode(',', (string) $request->get('destinations', '')))
            ->map(fn($d) => strtoupper(trim($d)))
            ->filter()
            ->reject(fn($d) => $d === $origin['iata'])
            ->unique()
            ->values();
        $destinations = $this->airports->many($destInput->all());

        // Everything we need to query, indexed by IATA.
        $set  = array_merge([$origin['iata'] => $origin], $destinations);
        $iatas = array_keys($set);
        $icaos = array_values(array_filter(array_map(fn($a) => $a['icao'] ?? null, $set)));

        // Batched lookups.
        $faaByAirport = $this->faa->forAirports($iatas);
        $metar        = $this->awc->metar($icaos);
        $taf          = $this->awc->taf($icaos);

        $build = function (array $a) use ($faaByAirport, $metar, $taf) {
            $icao   = $a['icao'] ?? null;
            $events = $faaByAirport[$a['iata']] ?? [];
            $mx     = $icao ? ($metar[$icao] ?? null) : null;
            $tf     = $icao ? ($taf[$icao]   ?? null) : null;
            $alerts = ($a['lat'] !== null && $a['lon'] !== null)
                ? $this->nws->forPoint((float) $a['lat'], (float) $a['lon'])
                : [];

            [$score, $reasons] = $this->score($events, $mx, $tf, $alerts);

            return [
                'airport' => [
                    'iata' => $a['iata'],
                    'icao' => $icao,
                    'name' => $a['name'],
                ],
                'impact_score' => $score,
                'impact_level' => $this->level($score),
                'reasons'      => $reasons,
                'faa'          => $events,
                'weather'      => $mx,
                'forecast'     => $tf,
                'alerts'       => $alerts,
            ];
        };

        $originReport = $build($origin);
        $destReports  = array_values(array_map($build, $destinations));

        // Overall = worst of origin and any destination (a destination storm
        // still grounds the outbound flight).
        $overall = $originReport['impact_score'];
        foreach ($destReports as $d) {
            $overall = max($overall, $d['impact_score']);
        }

        return response()->json([
            'data' => [
                'origin'       => $originReport,
                'destinations' => $destReports,
                'overall'      => [
                    'impact_score' => $overall,
                    'impact_level' => $this->level($overall),
                ],
            ],
            'meta' => [
                'faa_updated'       => $this->faa->all()['updated'] ?? null,
                'destination_count' => count($destReports),
                'generated_at'      => now()->toIso8601String(),
            ],
        ]);
    }

    /**
     * Heuristic 0-100 impact score from the combined signals, plus reason tags.
     */
    private function score(array $events, ?array $metar, ?array $taf, array $alerts): array
    {
        $score   = 0;
        $reasons = [];

        foreach ($events as $e) {
            switch ($e['kind'] ?? '') {
                case 'ground_stop':
                    $score += 100;
                    $reasons[] = 'FAA ground stop' . ($e['reason'] ? ' (' . $e['reason'] . ')' : '');
                    break;
                case 'closure':
                    $score += 80;
                    $reasons[] = 'Airport closure';
                    break;
                case 'ground_delay':
                    $score += 50 + $this->minutes($e['avg'] ?? '');
                    $reasons[] = 'Ground delay program' . (!empty($e['avg']) ? ' avg ' . $e['avg'] : '');
                    break;
                case 'delay':
                    $m = $this->minutes($e['max'] ?? '');
                    $score += min($m, 60);
                    $reasons[] = trim(($e['direction'] ? $e['direction'] . ' ' : '') . 'delay'
                        . (!empty($e['max']) ? ' up to ' . $e['max'] : ''));
                    break;
            }
        }

        // Flight category (ceiling/visibility) from current METAR.
        $cat = strtoupper((string) ($metar['flight_category'] ?? ''));
        $score += match ($cat) {
            'LIFR'  => 40,
            'IFR'   => 25,
            'MVFR'  => 10,
            default => 0,
        };
        if (in_array($cat, ['IFR', 'LIFR'], true)) {
            $reasons[] = "Low ceilings/visibility ({$cat})";
        }

        // Strong wind / gusts.
        $gust = (int) ($metar['wind_gust'] ?? 0);
        if ($gust >= 30) {
            $score += 15;
            $reasons[] = "Strong wind gusts ({$gust} kt)";
        }

        // Thunderstorms in the forecast window.
        if (!empty($taf['storm_window'])) {
            $score += 20;
            $reasons[] = 'Thunderstorms in forecast';
        }

        // NWS active alerts.
        foreach ($alerts as $al) {
            $sev = strtolower((string) ($al['severity'] ?? ''));
            $add = match ($sev) {
                'extreme' => 60,
                'severe'  => 40,
                'moderate'=> 20,
                default   => 10,
            };
            $score += $add;
            if (!empty($al['event'])) {
                $reasons[] = 'NWS ' . $al['event'];
            }
        }

        return [min($score, 100), array_values(array_unique($reasons))];
    }

    /** Parse "1 hour and 44 minutes" / "29 minutes" into total minutes. */
    private function minutes(string $text): int
    {
        if ($text === '') {
            return 0;
        }
        $min = 0;
        if (preg_match('/(\d+)\s*hour/i', $text, $h)) {
            $min += ((int) $h[1]) * 60;
        }
        if (preg_match('/(\d+)\s*min/i', $text, $m)) {
            $min += (int) $m[1];
        }
        return $min;
    }

    private function level(int $score): string
    {
        return match (true) {
            $score >= 80 => 'severe',
            $score >= 50 => 'high',
            $score >= 25 => 'moderate',
            $score >= 10 => 'low',
            default      => 'none',
        };
    }
}
