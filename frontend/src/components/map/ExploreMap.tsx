import { useEffect, useRef } from 'react'
import { MapContainer, TileLayer, CircleMarker, Popup } from 'react-leaflet'
import 'leaflet/dist/leaflet.css'
import type { MapOutlet, CountryCode } from '@/types'
import { COUNTRY_COLORS, TYPE_LABELS } from '@/lib/utils'
import RobotExplorers from './RobotExplorers'
import BotMarkers from './BotMarkers'

interface Props {
  outlets: MapOutlet[]
  onMarkerClick?: (outlet: MapOutlet) => void
  flyTo?: { lat: number; lon: number } | null
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
      {outlets.map((outlet) => (
        <CircleMarker
          key={outlet.id}
          center={[outlet.lat, outlet.lon]}
          radius={outlet.is_featured ? 8 : 6}
          pathOptions={{
            color: '#fff',
            weight: 1.5,
            fillColor: COUNTRY_COLORS[outlet.country as CountryCode] ?? '#888',
            fillOpacity: 0.9,
          }}
          eventHandlers={{ click: () => onMarkerClick?.(outlet) }}
        >
          <Popup className="dark-popup">
            <div className="min-w-[180px]">
              <p className="font-semibold text-sm text-white mb-1">{outlet.name}</p>
              <p className="text-xs text-slate-400 mb-2">{outlet.city}, {outlet.region}</p>
              <span className="text-xs bg-slate-700 text-slate-300 px-2 py-0.5 rounded mr-2">
                {TYPE_LABELS[outlet.type] ?? outlet.type}
              </span>
              <a
                href={outlet.url}
                target="_blank"
                rel="noopener noreferrer"
                className="text-blue-400 text-xs hover:underline block mt-2"
              >
                Visit site →
              </a>
            </div>
          </Popup>
        </CircleMarker>
      ))}
    </MapContainer>
  )
}
