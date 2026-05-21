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
            <Popup maxWidth={280} minWidth={240}>
              {/* Accent bar */}
              <div className="h-0.5 rounded-t-[13px]" style={{ backgroundColor: accentColor }} />

              <div className="px-3 py-2.5">
                {/* Row 1: flag + name + location */}
                <div className="flex items-center gap-2 mb-2 pr-4">
                  <span className="text-xl leading-none flex-none">{toFlagEmoji(outlet.country)}</span>
                  <div className="min-w-0">
                    <p className="font-bold text-white text-sm leading-tight truncate">{outlet.name}</p>
                    <p className="text-[11px] text-slate-400 truncate">
                      {outlet.city}{outlet.region && outlet.region !== outlet.city ? ` · ${outlet.region}` : ''}
                    </p>
                  </div>
                </div>

                {/* Row 2: badges inline */}
                <div className="flex flex-wrap gap-1 mb-2">
                  <span className={`text-[10px] font-medium px-2 py-0.5 rounded-full ${typeColorClass}`}>
                    {TYPE_LABELS[outlet.type as MediaType] ?? outlet.type}
                  </span>
                  <span className="text-[10px] font-medium px-2 py-0.5 rounded-full bg-slate-800 text-slate-400">
                    {LANG_LABELS[outlet.language] ?? outlet.language.toUpperCase()}
                  </span>
                  {outlet.has_rss && (
                    <span className="text-[10px] font-medium px-2 py-0.5 rounded-full bg-orange-900/50 text-orange-300">
                      RSS
                    </span>
                  )}
                  {outlet.is_featured && (
                    <span className="text-[10px] font-medium px-2 py-0.5 rounded-full bg-amber-900/50 text-amber-300">
                      ★ Featured
                    </span>
                  )}
                </div>

                {/* Row 3: URL */}
                <p className="text-[10px] text-slate-500 mb-2.5 truncate" title={outlet.url}>
                  {formatUrl(outlet.url)}
                </p>

                {/* Row 4: buttons */}
                <div className="flex gap-1.5">
                  <a
                    href={outlet.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="flex-1 text-center text-[11px] font-semibold bg-blue-600 hover:bg-blue-500 text-white px-2 py-1.5 rounded-md transition-colors"
                  >
                    Visit ↗
                  </a>
                  <a
                    href={`/outlets/${outlet.slug}`}
                    className="flex-1 text-center text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 px-2 py-1.5 rounded-md transition-colors border border-slate-700"
                  >
                    Details
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
