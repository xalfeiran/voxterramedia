<?php

namespace App\Services\Flight;

/**
 * Maps an IATA code to the identifiers the operational feeds need:
 *   - ICAO  (AviationWeather METAR/TAF)
 *   - lat/lon (NWS point alerts)
 * FAA NAS status already keys on IATA.
 *
 * Backed by resources/data/airports.json (curated; extend as needed).
 */
class AirportReference
{
    private static ?array $map = null;

    private function map(): array
    {
        if (self::$map === null) {
            $path = resource_path('data/airports.json');
            $raw  = is_file($path) ? json_decode((string) file_get_contents($path), true) : [];
            self::$map = is_array($raw) ? $raw : [];
        }
        return self::$map;
    }

    /** Return the reference row for an IATA code, or null if unknown. */
    public function get(string $iata): ?array
    {
        $iata = strtoupper(trim($iata));
        $row  = $this->map()[$iata] ?? null;
        if (!$row) {
            return null;
        }
        return [
            'iata' => $iata,
            'icao' => $row['icao'] ?? null,
            'lat'  => $row['lat']  ?? null,
            'lon'  => $row['lon']  ?? null,
            'name' => $row['name'] ?? $iata,
        ];
    }

    public function has(string $iata): bool
    {
        return isset($this->map()[strtoupper(trim($iata))]);
    }

    /** Resolve a list of IATA codes to reference rows, silently dropping unknowns. */
    public function many(array $iatas): array
    {
        $out = [];
        foreach ($iatas as $iata) {
            $row = $this->get((string) $iata);
            if ($row) {
                $out[$row['iata']] = $row;
            }
        }
        return $out;
    }
}
