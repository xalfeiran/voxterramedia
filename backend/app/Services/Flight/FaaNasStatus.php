<?php

namespace App\Services\Flight;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * FAA National Airspace System status — ground stops, ground delay programs,
 * arrival/departure delays, and airport closures, keyed by IATA code.
 *
 * Source: https://nasstatus.faa.gov/api/airport-status-information  (XML, no auth)
 */
class FaaNasStatus
{
    private const URL        = 'https://nasstatus.faa.gov/api/airport-status-information';
    private const CACHE_KEY  = 'faa-nas-status';
    private const CACHE_SECS = 120;

    /**
     * Full feed parsed into [ 'updated' => str, 'airports' => [ IATA => [events...] ] ].
     * Each event: ['kind','reason',...]. Returns empty airports on any failure.
     */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_SECS, function () {
            try {
                $resp = Http::timeout(10)
                    ->withHeaders(['User-Agent' => 'VoxTerra.media/1.0 (flight-impact)'])
                    ->get(self::URL);

                if (!$resp->successful()) {
                    return ['updated' => null, 'airports' => []];
                }

                return $this->parse($resp->body());
            } catch (\Throwable $e) {
                return ['updated' => null, 'airports' => []];
            }
        });
    }

    /** Events for a specific set of IATA codes: [ IATA => [events...] ]. */
    public function forAirports(array $iatas): array
    {
        $all = $this->all()['airports'];
        $out = [];
        foreach ($iatas as $iata) {
            $iata = strtoupper(trim($iata));
            $out[$iata] = $all[$iata] ?? [];
        }
        return $out;
    }

    private function parse(string $xml): array
    {
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if (!$doc) {
            return ['updated' => null, 'airports' => []];
        }

        $airports = [];
        $push = function (string $iata, array $event) use (&$airports) {
            $iata = strtoupper(trim($iata));
            if ($iata === '') {
                return;
            }
            $airports[$iata][] = $event;
        };

        foreach ($doc->Delay_type ?? [] as $type) {
            $name = trim((string) $type->Name);

            // Ground Delay Programs
            foreach ($type->Ground_Delay_List->Ground_Delay ?? [] as $g) {
                $push((string) $g->ARPT, [
                    'kind'   => 'ground_delay',
                    'label'  => 'Ground Delay Program',
                    'reason' => trim((string) $g->Reason),
                    'avg'    => trim((string) $g->Avg),
                    'max'    => trim((string) $g->Max),
                ]);
            }

            // Ground Stop Programs
            foreach ($type->Ground_Stop_List->Ground_Stop ?? [] as $g) {
                $push((string) $g->ARPT, [
                    'kind'      => 'ground_stop',
                    'label'     => 'Ground Stop',
                    'reason'    => trim((string) $g->Reason),
                    'end_time'  => trim((string) $g->End_Time),
                ]);
            }

            // General Arrival/Departure Delay Info
            foreach ($type->Arrival_Departure_Delay_List->Delay ?? [] as $d) {
                $ad = $d->Arrival_Departure;
                $push((string) $d->ARPT, [
                    'kind'      => 'delay',
                    'label'     => 'Arrival/Departure Delay',
                    'reason'    => trim((string) $d->Reason),
                    'direction' => isset($ad['Type']) ? (string) $ad['Type'] : null,
                    'min'       => trim((string) $ad->Min),
                    'max'       => trim((string) $ad->Max),
                    'trend'     => trim((string) $ad->Trend),
                ]);
            }

            // Airport Closures
            foreach ($type->Airport_Closure_List->Airport ?? [] as $c) {
                $push((string) $c->ARPT, [
                    'kind'   => 'closure',
                    'label'  => 'Airport Closure',
                    'reason' => trim((string) $c->Reason),
                    'start'  => trim((string) $c->Start),
                    'reopen' => trim((string) $c->Reopen),
                ]);
            }
        }

        return [
            'updated'  => trim((string) ($doc->Update_Time ?? '')) ?: null,
            'airports' => $airports,
        ];
    }
}
