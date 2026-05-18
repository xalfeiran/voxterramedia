// ── Shared bot roster & location data ──────────────────────────────────────
// Imported by BotStatusPanel (UI) and BotMarkers (map) so state stays in sync.

export interface BotLocation {
  city: string
  country: string
  flag: string
  lat: number
  lon: number
}

export const BOTS = [
  { id: 0, name: 'Scoop McScanBot',   emoji: '🤖', color: '#22d3ee' },
  { id: 1, name: 'Señor Pixel',        emoji: '🛸', color: '#818cf8' },
  { id: 2, name: 'Madame Headline',    emoji: '🦾', color: '#4ade80' },
  { id: 3, name: 'Sir Refreshalot',    emoji: '⚡', color: '#fb923c' },
  { id: 4, name: 'Bytes McGee',        emoji: '🔬', color: '#f472b6' },
  { id: 5, name: 'Glitch Wellington',  emoji: '🌐', color: '#facc15' },
] as const

export const LOCATIONS: BotLocation[] = [
  { city: 'Tokyo',        country: 'Japan',        flag: '🇯🇵', lat:  35.68, lon:  139.69 },
  { city: 'London',       country: 'UK',           flag: '🇬🇧', lat:  51.50, lon:   -0.12 },
  { city: 'New York',     country: 'USA',          flag: '🇺🇸', lat:  40.71, lon:  -74.00 },
  { city: 'São Paulo',    country: 'Brazil',       flag: '🇧🇷', lat: -23.55, lon:  -46.63 },
  { city: 'Paris',        country: 'France',       flag: '🇫🇷', lat:  48.85, lon:    2.35 },
  { city: 'Lagos',        country: 'Nigeria',      flag: '🇳🇬', lat:   6.52, lon:    3.38 },
  { city: 'Sydney',       country: 'Australia',    flag: '🇦🇺', lat: -33.87, lon:  151.21 },
  { city: 'Mumbai',       country: 'India',        flag: '🇮🇳', lat:  19.07, lon:   72.88 },
  { city: 'Cairo',        country: 'Egypt',        flag: '🇪🇬', lat:  30.04, lon:   31.24 },
  { city: 'Mexico City',  country: 'Mexico',       flag: '🇲🇽', lat:  19.43, lon:  -99.13 },
  { city: 'Berlin',       country: 'Germany',      flag: '🇩🇪', lat:  52.52, lon:   13.40 },
  { city: 'Seoul',        country: 'South Korea',  flag: '🇰🇷', lat:  37.57, lon:  126.98 },
  { city: 'Buenos Aires', country: 'Argentina',    flag: '🇦🇷', lat: -34.61, lon:  -58.38 },
  { city: 'Nairobi',      country: 'Kenya',        flag: '🇰🇪', lat:  -1.29, lon:   36.82 },
  { city: 'Istanbul',     country: 'Turkey',       flag: '🇹🇷', lat:  41.01, lon:   28.95 },
  { city: 'Jakarta',      country: 'Indonesia',    flag: '🇮🇩', lat:  -6.21, lon:  106.85 },
  { city: 'Toronto',      country: 'Canada',       flag: '🇨🇦', lat:  43.65, lon:  -79.38 },
  { city: 'Johannesburg', country: 'South Africa', flag: '🇿🇦', lat: -26.20, lon:   28.04 },
  { city: 'Moscow',       country: 'Russia',       flag: '🇷🇺', lat:  55.75, lon:   37.62 },
  { city: 'Dubai',        country: 'UAE',          flag: '🇦🇪', lat:  25.20, lon:   55.27 },
  { city: 'Singapore',    country: 'Singapore',    flag: '🇸🇬', lat:   1.35, lon:  103.82 },
  { city: 'Warsaw',       country: 'Poland',       flag: '🇵🇱', lat:  52.23, lon:   21.01 },
  { city: 'Amsterdam',    country: 'Netherlands',  flag: '🇳🇱', lat:  52.37, lon:    4.89 },
  { city: 'Bogotá',       country: 'Colombia',     flag: '🇨🇴', lat:   4.71, lon:  -74.07 },
  { city: 'Madrid',       country: 'Spain',        flag: '🇪🇸', lat:  40.42, lon:   -3.70 },
  { city: 'Shanghai',     country: 'China',        flag: '🇨🇳', lat:  31.23, lon:  121.47 },
  { city: 'Karachi',      country: 'Pakistan',     flag: '🇵🇰', lat:  24.86, lon:   67.01 },
  { city: 'Stockholm',    country: 'Sweden',       flag: '🇸🇪', lat:  59.33, lon:   18.07 },
  { city: 'Casablanca',   country: 'Morocco',      flag: '🇲🇦', lat:  33.59, lon:   -7.62 },
  { city: 'Tel Aviv',     country: 'Israel',       flag: '🇮🇱', lat:  32.08, lon:   34.78 },
]

export const STATUSES = [
  'Scanning feeds',
  'Wrangling RSS',
  'Sniffing headlines',
  'Indexing sources',
  'Cross-referencing',
  'Pinging servers',
  'Archiving articles',
  'Mapping coverage',
  'Chasing bylines',
  'Tasting the news',
  'Detecting bias',
  'Counting words',
  'Hoarding metadata',
  'Befriending APIs',
  'Slurping sitemaps',
]

export function pickOther<T>(arr: T[], exclude: T): T {
  const pool = arr.filter(x => x !== exclude)
  return pool[Math.floor(Math.random() * pool.length)]
}

/** 30–55 s, randomised per bot so they don't all jump together */
export function randomInterval() {
  return 30_000 + Math.random() * 25_000
}
