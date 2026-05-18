import { useEffect, useRef } from 'react'
import L from 'leaflet'
import { Marker, Popup, useMap } from 'react-leaflet'
import { useBotStore } from '@/stores/botStore'

/** Build a custom DivIcon for each bot */
function makeBotIcon(emoji: string, color: string, jumping: boolean, active: boolean) {
  const border  = jumping ? '1.5px dashed' : active ? '2px solid' : '1.5px solid'
  const opacity = jumping ? '0.5' : '1'
  const glow    = active  ? `0 0 14px ${color}, 0 0 4px ${color}` : `0 0 10px ${color}66`
  const size    = active  ? 40 : 34
  const fontSize= active  ? 19 : 16

  const pulse = jumping
    ? `<span style="
        position:absolute;inset:-6px;border-radius:50%;
        border:2px solid ${color};opacity:0.4;
        animation:bot-ping 0.8s ease-out infinite;
      "></span>`
    : active
    ? `<span style="
        position:absolute;inset:-8px;border-radius:50%;
        border:2px solid ${color};opacity:0.35;
        animation:bot-ping 1.4s ease-out infinite;
      "></span>`
    : ''

  return L.divIcon({
    className: '',
    iconSize:   [size, size],
    iconAnchor: [size / 2, size / 2],
    popupAnchor:[0, -(size / 2 + 4)],
    html: `
      <style>
        @keyframes bot-ping {
          0%   { transform:scale(1);   opacity:0.4; }
          100% { transform:scale(1.9); opacity:0;   }
        }
      </style>
      <div style="
        position:relative;
        width:${size}px;height:${size}px;
        display:flex;align-items:center;justify-content:center;
        background:${color}22;
        border:${border} ${color};
        border-radius:50%;
        opacity:${opacity};
        box-shadow:${glow};
        font-size:${fontSize}px;
        transition:all 0.35s;
      ">${pulse}${emoji}</div>
    `,
  })
}

export default function BotMarkers() {
  const bots        = useBotStore(s => s.bots)
  const activeBotId = useBotStore(s => s.activeBotId)
  const map         = useMap()

  // Keep refs to each Marker instance so we can open its popup programmatically
  const markerRefs = useRef<Map<number, L.Marker>>(new Map())

  // Fly to active bot and open its popup whenever activeBotId changes
  useEffect(() => {
    if (activeBotId === null) return
    const bot = useBotStore.getState().bots.find(b => b.id === activeBotId)
    if (!bot) return

    map.flyTo([bot.location.lat, bot.location.lon], 6, { duration: 1.1 })

    // Wait for the fly animation to finish before opening the popup
    const t = setTimeout(() => {
      markerRefs.current.get(activeBotId)?.openPopup()
    }, 1300)
    return () => clearTimeout(t)
  }, [activeBotId, map])

  return (
    <>
      {bots.map(bot => {
        const isActive = bot.id === activeBotId
        return (
          <Marker
            key={bot.id}
            position={[bot.location.lat, bot.location.lon]}
            icon={makeBotIcon(bot.emoji, bot.color, bot.jumping, isActive)}
            zIndexOffset={isActive ? 2000 : 1000}
            ref={el => {
              if (el) markerRefs.current.set(bot.id, el)
              else    markerRefs.current.delete(bot.id)
            }}
          >
            <Popup
              closeButton={false}
              className="bot-popup"
              minWidth={200}
            >
              <div style={{
                background: '#0f172a',
                border: `1px solid ${bot.color}55`,
                borderRadius: 8,
                padding: '10px 12px',
                lineHeight: 1.5,
              }}>
                <p style={{ margin: 0, fontWeight: 700, color: bot.color, fontSize: 13 }}>
                  {bot.emoji} {bot.name}
                </p>
                {bot.jumping ? (
                  <p style={{ margin: '4px 0 0', color: '#94a3b8', fontSize: 11 }}>
                    ✈ In transit to next location…
                  </p>
                ) : (
                  <>
                    <p style={{ margin: '4px 0 0', color: '#cbd5e1', fontSize: 11 }}>
                      {bot.status}
                    </p>
                    <p style={{ margin: '2px 0 0', color: '#64748b', fontSize: 11 }}>
                      {bot.location.flag} {bot.location.city}, {bot.location.country}
                    </p>
                  </>
                )}
              </div>
            </Popup>
          </Marker>
        )
      })}
    </>
  )
}
