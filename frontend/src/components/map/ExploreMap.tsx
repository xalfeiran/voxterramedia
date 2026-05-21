import { useEffect, useRef } from 'react'
import { MapContainer, TileLayer, CircleMarker, Popup } from 'react-leaflet'
import 'leaflet/dist/leaflet.css'
import type { MapOutlet, CountryCode, MediaType } from '@/types'
import { COUNTRY_COLORS, TYPE_LABELS, TYPE_COLORS, formatUrl } from '@/lib/utils'
import RobotExplorers from './RobotExplorers'
import BotMarkers from './BotMarkers'

interface Props {
  outlets: MapOutlet[]
  onMarkerClick?: (outlet: MapOutlet) => void
  flyTo?: { lat: number; lon: number } | null
}

/** Convert any ISO 3166-1 alpha-2 code to its flag emoji */
function toFlagEmoji(code: string): string {
  return code.toUpperCase().replace(/./g, c =>
    String.fromCodePoint(0x1f1e6 + c.charCodeAt(0) - 65)
  )
}

/** Language code → readable label (common codes only, falls back to uppercase) */
const LANG_LABELS: Record<string, string> = {
  en: 'English', es: 'Spanish', fr: 'French', de: 'German', pt: 'Portuguese',
  ar: 'Arabic', zh: 'Chinese', ja: 'Japanese', ko: 'Korean', ru: 'Russian',
  it: 'Italian', nl: 'Dutch', pl: 'Polish', sv: 'Swedish', tr: 'Turkish',
  hi: 'Hindi', fa: 'Persian', he: 'Hebrew', id: 'Indonesian', ms: 'Malay',
  th: 'Thai', vi: 'Vietnamese', uk: 'Ukrainian', ro: 'Romanian', hu: 'Hungarian',
}

export default function ExploreMap({ outlets, onMarkerClick, flyTo }: Props) {
  const mapRef = useRef<L.Map | null>(null)

  useEffect(() => {
    if (flyTo && mapRef.current) {
      mapRef.current.flyTo([flyTo.lat, flyTo.lon], 10, { duration: 1 })
    }
  }, [flyTo])

  return (
    <MapContainer
      center={[38, -95]}
      zoom={3}
      className="h-full w-full"
      ref={mapRef}
    >
      <TileLayer
        url="https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png"
        attribution='&copy; <a href="https://www.openstreetmap.org">OpenStreetMap</a> &copy; <a href="https://carto.com">CARTO</a>'
        maxZoom={19}
      />
      <RobotExplorers />
      <BotMarkers />

      {outlets.map((outlet) => {
        const accentColor = COUNTRY_COLORS[outlet.country as CountryCode] ?? '#6366f1'
        const typeColorClass = TYPE_COLORS[outlet.type as MediaType] ?? 'bg-slate-700 text-slate-300'

        return (
          <CircleMarker
            key={outlet.id}
            center={[outlet.lat, outlet.lon]}
            radius={outlet.is_featured ? 8 : 6}
            pathOptions={{
              color: '#fff',
              weight: 1.5,
              fillColor: accentColor,
              fillOpacity: 0.9,
            }}
            eventHandlers={{ click: () => onMarkerClick?.(outlet) }}
          >
            <Popup maxWidth={300} minWidth={270}>
              {/* ── Color bar at top ──────────────────────────────────────── */}
              <div
                className="h-1 rounded-t-[13px]"
                style={{ backgroundColor: accentColor }}
              />

              <div className="p-4">
                {/* ── Header: flag + name ───────────────────────────────── */}
                <div className="flex items-start gap-3 mb-3 pr-4">
                  <span className="text-2xl leading-none mt-0.5 flex-none">
                    {toFlagEmoji(outlet.country)}
                  </span>
                  <div className="min-w-0">
                    <p className="font-bold text-white text-base leading-tight">
                      {outlet.name}
                    </p>
                    <p className="text-xs text-slate-400 mt-1">
                      📍 {outlet.city}{outlet.region && outlet.region !== outlet.city ? ` · ${outlet.region}` : ''}
                    </p>
                  </div>
                </div>

                {/* ── Divider ────────────────────────────────────────────── */}
                <div className="border-t border-slate-800 mb-3" />

                {/* ── Badges ────────────────────────────────────────────── */}
                <div className="flex flex-wrap gap-1.5 mb-3">
                  <span className={`text-xs font-medium px-2.5 py-0.5 rounded-full ${typeColorClass}`}>
                    {TYPE_LABELS[outlet.type as MediaType] ?? outlet.type}
                  </span>
                  <span className="text-xs font-medium px-2.5 py-0.5 rounded-full bg-slate-800 text-slate-300">
                    {LANG_LABELS[outlet.language] ?? outlet.language.toUpperCase()}
                  </span>
                  {outlet.has_rss && (
                    <span className="text-xs font-medium px-2.5 py-0.5 rounded-full bg-orange-900/60 text-orange-300">
                      📡 RSS
                    </span>
                  )}
                  {outlet.is_featured && (
                    <span className="text-xs font-medium px-2.5 py-0.5 rounded-full bg-amber-900/60 text-amber-300">
                      ★ Featured
                    </span>
                  )}
                </div>

                {/* ── URL ───────────────────────────────────────────────── */}
                <p className="text-xs text-slate-500 mb-4 truncate" title={outlet.url}>
                  🔗 {formatUrl(outlet.url)}
                </p>

                {/* ── Action buttons ────────────────────────────────────── */}
                <div className="flex gap-2">
                  <a
                    href={outlet.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex-1 text-center text-xs font-semibold bg-blue-600 hover:bg-blue-500 text-white px-3 py-2 rounded-lg transition-colors"
                  >
                    Visit site ↗
                  </a>
                  <a
                    href={`/outlets/${outlet.slug}`}
                    className="flex-1 text-center text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-2 rounded-lg transition-colors border border-slate-700"
                  >
                    View details
                  </a>
                </div>
              </div>
            </Popup>
          </CircleMarker>
        )
      })}
    </MapContainer>
  )
}
