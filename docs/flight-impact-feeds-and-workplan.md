# DFW Flight-Impact News — Feed Sources & Workplan

_Last updated: 2026-06-01. Endpoints below were verified against live docs in June 2026; aviation APIs change, so re-check before each integration._

## 1. The reframe (why this isn't "Dallas news")

A flight **leaving DFW** is affected by conditions at three layers, not one:

- **Origin** — DFW/Dallas weather, airport ops, ground stops.
- **Destinations** — every metro DFW flies to (a storm at the arrival airport cancels the departure).
- **National / carrier** — FAA airspace-wide events, and American Airlines (the DFW megahub) operational status.

So the unit of analysis is an **airport's route graph**, and the strongest signal comes from **structured operational feeds**, not general newspaper RSS. Local media is a useful _soft_ signal layered on top — not the core.

## 2. The mapping problem (do this first)

The operational feeds key off identifiers VoxTerra doesn't store yet:

| Need | Example for DFW | Source |
|------|-----------------|--------|
| IATA (we have this) | `DFW` | `cities.airport_code` |
| ICAO (weather/NAS) | `KDFW` | airport reference table |
| Lat/Lon (NWS point) | `32.8998, -97.0403` | airport reference table |
| NWS forecast zone | `TXZ119` | derive once via `api.weather.gov/points/{lat},{lon}` |

**Action:** add a small `airports` reference (IATA, ICAO, lat, lon, name) — a bundled JSON like the existing `airport_codes.json`, or a DB table. Everything else depends on it.

## 3. Feed catalog

### Tier 1 — Operational, structured, free (the core signal)

**FAA NAS Status** — _"is this airport melting down right now?"_
- Gives: ground stops, ground delay programs, departure/arrival delays, airport closures, by airport.
- Endpoint: `https://nasstatus.faa.gov/api/airport-status-information`
- Format: XML · Auth: none · Cadence: near real-time (poll every 2–5 min)
- Use: query for origin + every destination airport. This is the single best disruption indicator.

**AviationWeather.gov (AWC) API** — _best predictor of delays_
- Gives: METAR (current conditions) + TAF (forecast) — ceilings, visibility, wind, storms.
- Endpoints: `https://aviationweather.gov/api/data/metar?ids=KDFW&format=json` · `.../api/data/taf?ids=KDFW&format=json` (supports comma-separated ICAO lists)
- Format: JSON · Auth: none (set a custom `User-Agent`) · Cadence: METAR hourly+, TAF ~6h
- Note: response schema changed Sept 2025 — build against the current OpenAPI.

**NWS Alerts (CAP)** — _severe weather warnings_
- Gives: tornado/thunderstorm/winter-storm/flood warnings for a point or zone.
- Endpoints: `https://api.weather.gov/alerts/active?point={lat},{lon}` or `/alerts/active/zone/{zoneId}`
- Format: GeoJSON · Auth: none (requires `User-Agent` header) · Cadence: real-time
- Use: origin + destination metros.

### Tier 2 — Soft signal, free (already partly built)

**VoxTerra media RSS** — local/regional/national outlets (the current endpoint)
- Gives: human-reported context (events, strikes, local disruptions) the structured feeds miss.
- Source: existing catalog, now scoped to **origin + destination metros**, filtered by a disruption lexicon and recency. Treat as supporting evidence, not primary.

### Tier 3 — Enhanced, auth or paid (optional / later)

**FAA NOTAMs (NMS-API)** — runway/taxiway/navaid closures
- Endpoint: FAA API portal `https://api.faa.gov` · Auth: register for an API key (access request) · Cost: free
- Friction: requires approval; FAA is mid-migration to NMS through 2026. Add when access is granted.

**FlightAware AeroAPI** — live route map + per-flight status + delay boards
- Solves the route-graph problem authoritatively (which destinations DFW actually flies today) and gives live departure boards.
- Endpoint: `https://aeroapi.flightaware.com/aeroapi/` · Auth: API key · Cost: ~$100/mo (business) up to ~$1,000/mo (premium)
- Use only if a curated/static route list isn't good enough.

**Airline travel alerts (American)** — `https://www.aa.com/i18n/travel-info/travel-alerts.jsp`
- No clean public API; page is HTML (likely JS-rendered). Scrape-only, brittle. Low priority.

## 4. Route-graph source (a decision)

"Destinations from DFW" has no free, current, structured feed (OpenFlights route data was discontinued ~2014). Options:

- **Curated JSON (free, MVP):** hand-list DFW's top ~30–50 destinations. Fast, good enough to prove the model. Stale over time.
- **AeroAPI (paid, live):** real destinations + today's schedule. Production-grade. ~$100/mo+.

Recommendation: start curated, upgrade to AeroAPI only if the feature earns it.

## 5. Target architecture

```
GET /api/v1/airports/DFW/impact
  → resolve DFW: ICAO=KDFW, lat/lon, NWS zone, destinations[]
  → for {origin + destinations}:
        FAA NAS status   (delays/ground stops)
        AWC METAR/TAF    (weather now + forecast)
        NWS CAP alerts   (warnings)
  → national/carrier:    FAA NAS national events + (later) AA alerts
  → VoxTerra RSS:        origin+dest metros, disruption-scored
  → score + rank every signal → impact board
```

Output = a ranked **impact board**: each item with a score, a reason tag (`weather@KDFW`, `groundstop@ORD-dest`, `carrier:AA`, `warning:tornado`), affected route(s), and source. Cache structured feeds 2–5 min; prefetch on a Scout-style cron so requests are instant.

## 6. Workplan

**Phase 0 — Fix the data linkage (0.5 day) — _do now_**
- Deploy the two pending migrations; re-run `scout.py --backfill-airports`.
- Verify `DFW` resolves to Dallas/Fort Worth cities **and** that those cities have active RSS outlets.
- Deliverable: `/cities/DFW/news` returns non-empty. _This unblocks Tier 2._

**Phase 1 — Airport reference + route graph (1–2 days)**
- Add `airports` reference (IATA, ICAO, lat, lon, NWS zone) + curated `airport_routes` (DFW → destinations).
- Backfill NWS zones via `api.weather.gov/points`.
- Deliverable: given `DFW`, the API can list ICAO + destination airports.

**Phase 2 — Operational connectors (2–4 days) — _highest payoff_**
- Service classes: `FaaNasStatus`, `AviationWeather`, `NwsAlerts` (fetch, parse, cache, fail-soft).
- New endpoint `/api/v1/airports/{iata}/impact` returning structured status for origin + destinations.
- Deliverable: live delays + weather + warnings for DFW and its destinations, no RSS needed.

**Phase 3 — Disruption scoring + RSS merge (1–2 days)**
- Disruption lexicon (ground stop, deice, runway closed, IROPS, strike, outage, tornado…) + recency weighting.
- Tag and score VoxTerra items; merge into the impact board. Add `?impact=true`.
- Deliverable: one ranked board blending operational + media signals.

**Phase 4 — Enhancements (optional, as needed)**
- FAA NOTAM (once API access granted); FlightAware AeroAPI for live routes/boards; Haiku pass to cut false positives and extract a one-line "impact reason"; airline-alert scraping.

**Phase 5 — Productionize (1–2 days)**
- Scheduled prefetch (cron pulls NAS status every few min), cache tuning, rate-limit handling, `User-Agent` compliance, monitoring/alerting on feed failures.

## 7. Decisions needed from you

1. **Route data:** start with a curated destination list (free) or go straight to FlightAware AeroAPI (paid)?
2. **International destinations:** DFW flies to London, Tokyo, etc. FAA/NWS are US-only — do we cover international (needs other authorities/global weather) or scope v1 to US destinations?
3. **NOTAMs:** want me to start the FAA API access request now (it has lead time), or defer?
4. **Scope of v1:** DFW only as a pilot, or any airport in the catalog with a route graph?

## Sources
- FAA NAS Status API — https://nasstatus.faa.gov/api/airport-status-information
- AviationWeather Data API — https://aviationweather.gov/data/api/
- NWS Alerts Web Service — https://www.weather.gov/documentation/services-web-alerts
- FAA API Portal (NOTAM/NMS) — https://api.faa.gov/s/
- FlightAware AeroAPI — https://www.flightaware.com/commercial/aeroapi
- American Airlines travel alerts — https://www.aa.com/i18n/travel-info/travel-alerts.jsp
