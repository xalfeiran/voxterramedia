/**
 * RobotExplorers
 *
 * A react-leaflet child component that paints an animated canvas layer
 * on top of the map, simulating autonomous scout bots roaming the world
 * in search of new media outlets.
 *
 * Each bot:
 *  • Moves slowly between random worldwide city waypoints
 *  • Leaves a fading cyan/colour trail
 *  • Emits expanding radar-scan rings
 *  • Shows a targeting crosshair
 *  • Flashes a "discovery" burst when it arrives at a waypoint
 */

import { useEffect, useRef } from 'react'
import { useMap }            from 'react-leaflet'

// ── Worldwide waypoints (major cities across all continents) ───────────────
const WAYPOINTS: [number, number][] = [
  // Americas
  [40.71, -74.01], [34.05, -118.24], [41.88, -87.63], [29.76, -95.37],
  [25.77, -80.19], [47.61, -122.33], [37.77, -122.42], [45.50, -73.57],
  [43.65, -79.38], [49.25, -123.12], [19.43, -99.13], [25.69, -100.32],
  [20.97, -89.62], [9.93,  -84.08],  [8.99,  -79.52], [10.50, -66.92],
  [4.71,  -74.07], [-0.22, -78.51],  [-12.05, -77.04], [-16.50, -68.15],
  [-23.55, -46.63], [-22.91, -43.17], [-15.78, -47.93], [-34.61, -58.38],
  [-33.45, -70.67], [-43.25, -65.31],
  // Europe
  [51.51, -0.13],   [48.85,  2.35],   [52.52, 13.40],  [40.42, -3.70],
  [41.90, 12.50],   [37.98, 23.73],   [59.33, 18.07],  [55.68, 12.57],
  [60.17, 24.94],   [59.44, 24.75],   [56.95, 24.11],  [54.69, 25.28],
  [52.23, 21.01],   [50.07, 14.44],   [47.50, 19.04],  [48.21, 16.37],
  [47.38,  8.54],   [46.95,  7.45],   [50.85,  4.35],  [52.37,  4.90],
  [53.33, -6.25],   [55.95, -3.19],   [57.71, 11.97],  [43.30,  5.37],
  [45.76,  4.83],   [41.39,  2.15],   [38.72, -9.14],  [38.72, -9.14],
  [44.80, 20.46],   [42.70, 23.32],   [44.44, 26.10],  [50.45, 30.52],
  // Russia / Central Asia
  [55.76, 37.62],   [59.93, 30.32],   [56.85, 60.61],  [55.00, 82.95],
  [43.25, 76.89],   [41.30, 69.24],   [37.95, 58.38],  [51.18, 71.45],
  // Middle East & Africa
  [25.20, 55.27],   [24.47, 54.37],   [24.69, 46.72],  [30.06, 31.25],
  [33.89, 35.50],   [31.77, 35.22],   [36.82, 10.17],  [33.99, -6.85],
  [14.73, -17.47],  [12.37, -1.53],   [6.37,   2.39],  [9.05,   7.49],
  [4.05,   9.71],   [3.87,  11.52],   [-4.32, 15.32],  [-8.84, 13.23],
  [-25.97, 32.57],  [-26.20, 28.04],  [-33.93, 18.42], [-1.29, 36.82],
  [2.04,  45.34],   [-18.91, 47.54],  [15.55, 32.53],  [12.37, 43.14],
  [15.34, 38.93],   [9.03,  38.74],   [6.37,   2.39],  [5.35,  -4.00],
  // South & Southeast Asia
  [28.63, 77.22],   [19.08, 72.88],   [13.08, 80.27],  [22.57, 88.36],
  [23.73, 90.40],   [27.70, 85.32],   [33.69, 73.06],  [24.86, 67.01],
  [31.55, 74.35],   [7.87,  80.65],   [6.93,  79.85],
  [13.75, 100.52],  [10.82, 106.63],  [21.03, 105.85], [11.56, 104.92],
  [16.87, 96.19],   [1.35,  103.82],  [3.15,  101.69], [-6.21, 106.85],
  [-7.25, 112.75],  [12.37, 124.02],  [14.60, 120.98], [10.32, 123.91],
  // East Asia & Pacific
  [39.91, 116.39],  [31.23, 121.47],  [23.13, 113.26], [22.27, 114.17],
  [25.05, 121.53],  [37.57, 126.98],  [35.69, 139.69], [34.69, 135.50],
  [33.59, 130.41],  [43.07, 141.35],  [43.11, 131.90], [47.46, 142.74],
  [-33.87, 151.21], [-37.81, 144.96], [-27.47, 153.03], [-31.95, 115.86],
  [-12.43, 130.84], [-36.85, 174.76], [-41.29, 174.78],
  // More spread
  [64.13, -21.82],  [65.01, -25.51],  [63.43, 10.39],  [58.97,  5.73],
  [69.66, 18.96],   [78.22, 15.65],
]

// ── Bot configuration ──────────────────────────────────────────────────────
const BOT_COUNT  = 6
const BOT_SPEED  = [0.20, 0.28, 0.35, 0.22, 0.30, 0.26] // degrees / second
const BOT_COLORS = ['#22d3ee', '#818cf8', '#4ade80', '#fb923c', '#f472b6', '#facc15']

// ── Types ──────────────────────────────────────────────────────────────────
interface TrailPoint { lat: number; lon: number; age: number }

interface Bot {
  id:        number
  lat:       number
  lon:       number
  tLat:      number
  tLon:      number
  speed:     number
  color:     string
  rgb:       string           // pre-computed "r,g,b"
  trail:     TrailPoint[]
  scanPhase: number           // 0–1 continuous
  blipAge:   number           // seconds since last arrival flash, -1 = none
}

interface Burst { lat: number; lon: number; age: number; rgb: string }

// ── Helpers ────────────────────────────────────────────────────────────────
function rnd(arr: unknown[]) { return arr[Math.floor(Math.random() * arr.length)] }

function hexRgb(hex: string): string {
  return [1, 3, 5].map(i => parseInt(hex.slice(i, i + 2), 16)).join(',')
}

function randomPair(): [number, number] {
  return rnd(WAYPOINTS) as [number, number]
}

function makeBot(id: number): Bot {
  const [lat, lon] = randomPair()
  const [tLat, tLon] = randomPair()
  const color = BOT_COLORS[id % BOT_COLORS.length]
  return {
    id, lat, lon, tLat, tLon,
    speed:     BOT_SPEED[id % BOT_SPEED.length],
    color,
    rgb:       hexRgb(color),
    trail:     [],
    scanPhase: Math.random(),
    blipAge:   -1,
  }
}

// ── Component ──────────────────────────────────────────────────────────────
export default function RobotExplorers() {
  const map = useMap()

  const stateRef = useRef<{
    bots:    Bot[]
    bursts:  Burst[]
    canvas:  HTMLCanvasElement | null
    raf:     number
    prevT:   number
  }>({ bots: [], bursts: [], canvas: null, raf: 0, prevT: 0 })

  useEffect(() => {
    const s = stateRef.current
    s.bots   = Array.from({ length: BOT_COUNT }, (_, i) => makeBot(i))
    s.bursts = []

    // ── Canvas setup ────────────────────────────────────────────────────────
    const canvas = document.createElement('canvas')
    // Sit inside overlayPane so latLngToLayerPoint coordinates align
    canvas.style.cssText =
      'position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:10;'
    map.getPanes().overlayPane.appendChild(canvas)
    s.canvas = canvas

    function syncSize() {
      const sz = map.getSize()
      canvas.width  = sz.x
      canvas.height = sz.y
    }
    syncSize()
    map.on('resize', syncSize)

    // ── Animation loop ────────────────────────────────────────────────────
    function frame(t: number) {
      const dt = Math.min((t - s.prevT) / 1000, 0.1) // seconds, cap 100 ms
      s.prevT = t

      const ctx = canvas.getContext('2d')
      if (!ctx) { s.raf = requestAnimationFrame(frame); return }
      ctx.clearRect(0, 0, canvas.width, canvas.height)

      // ── Bursts (arrival flashes) ─────────────────────────────────────────
      s.bursts = s.bursts.filter(b => b.age < 1.8)
      s.bursts.forEach(b => {
        b.age += dt
        const progress = b.age / 1.8
        const r        = progress * 55             // expands to 55 px
        const a        = (1 - progress) * 0.6
        const pt       = map.latLngToLayerPoint([b.lat, b.lon])
        ctx.beginPath()
        ctx.arc(pt.x, pt.y, r, 0, Math.PI * 2)
        ctx.strokeStyle = `rgba(${b.rgb},${a})`
        ctx.lineWidth   = 1.5
        ctx.stroke()

        // Inner ring slightly offset in phase
        const r2 = progress * 30
        const a2 = (1 - progress) * 0.9
        ctx.beginPath()
        ctx.arc(pt.x, pt.y, r2, 0, Math.PI * 2)
        ctx.strokeStyle = `rgba(${b.rgb},${a2})`
        ctx.lineWidth   = 2
        ctx.stroke()
      })

      // ── Bots ─────────────────────────────────────────────────────────────
      s.bots.forEach(bot => {

        // ── Move ────────────────────────────────────────────────────────────
        const dLat = bot.tLat - bot.lat
        const dLon = bot.tLon - bot.lon
        const dist = Math.sqrt(dLat * dLat + dLon * dLon)

        if (dist < 0.25) {
          // Arrived — spawn a discovery burst
          s.bursts.push({ lat: bot.lat, lon: bot.lon, age: 0, rgb: bot.rgb })
          // Pick a new waypoint that isn't too close
          let attempts = 0
          let [nLat, nLon] = randomPair()
          while (attempts++ < 6) {
            const d = Math.sqrt((nLat - bot.lat) ** 2 + (nLon - bot.lon) ** 2)
            if (d > 15) break
            ;[nLat, nLon] = randomPair()
          }
          bot.tLat = nLat
          bot.tLon = nLon
          bot.blipAge = 0
        } else {
          const step = bot.speed * dt
          bot.lat += (dLat / dist) * step
          bot.lon += (dLon / dist) * step
        }

        // ── Trail ────────────────────────────────────────────────────────────
        const TRAIL_LIFE = 7 // seconds
        bot.trail.push({ lat: bot.lat, lon: bot.lon, age: 0 })
        bot.trail.forEach(tp => (tp.age += dt))
        bot.trail = bot.trail.filter(tp => tp.age < TRAIL_LIFE)

        // ── Blip age ──────────────────────────────────────────────────────────
        if (bot.blipAge >= 0) {
          bot.blipAge += dt
          if (bot.blipAge > 0.6) bot.blipAge = -1
        }

        // ── Advance scan phase ─────────────────────────────────────────────
        bot.scanPhase = (bot.scanPhase + dt * 0.5) % 1

        // ── Pixel position ────────────────────────────────────────────────
        const pt = map.latLngToLayerPoint([bot.lat, bot.lon])

        // ── Draw trail ───────────────────────────────────────────────────
        bot.trail.forEach(tp => {
          const pp = map.latLngToLayerPoint([tp.lat, tp.lon])
          const a  = Math.max(0, 1 - tp.age / TRAIL_LIFE) * 0.55
          ctx.beginPath()
          ctx.arc(pp.x, pp.y, 1.5, 0, Math.PI * 2)
          ctx.fillStyle = `rgba(${bot.rgb},${a})`
          ctx.fill()
        })

        // ── Draw scan rings (2 rings offset by 0.5 in phase) ──────────────
        for (let k = 0; k < 2; k++) {
          const p = (bot.scanPhase + k * 0.5) % 1
          const r = p * 32
          const a = (1 - p) * 0.5
          ctx.beginPath()
          ctx.arc(pt.x, pt.y, r, 0, Math.PI * 2)
          ctx.strokeStyle = `rgba(${bot.rgb},${a})`
          ctx.lineWidth   = 1
          ctx.stroke()
        }

        // ── Draw crosshair ────────────────────────────────────────────────
        const chLen = 12
        const chGap = 4
        ctx.save()
        ctx.strokeStyle = `rgba(${bot.rgb},0.45)`
        ctx.lineWidth   = 0.8
        ctx.setLineDash([2, 3])
        // horizontal
        ctx.beginPath()
        ctx.moveTo(pt.x - chLen, pt.y)
        ctx.lineTo(pt.x - chGap, pt.y)
        ctx.moveTo(pt.x + chGap, pt.y)
        ctx.lineTo(pt.x + chLen, pt.y)
        // vertical
        ctx.moveTo(pt.x, pt.y - chLen)
        ctx.lineTo(pt.x, pt.y - chGap)
        ctx.moveTo(pt.x, pt.y + chGap)
        ctx.lineTo(pt.x, pt.y + chLen)
        ctx.stroke()
        ctx.restore()

        // ── Draw glow halo ────────────────────────────────────────────────
        const glow = ctx.createRadialGradient(pt.x, pt.y, 0, pt.x, pt.y, 12)
        glow.addColorStop(0, `rgba(${bot.rgb},0.85)`)
        glow.addColorStop(1, `rgba(${bot.rgb},0)`)
        ctx.beginPath()
        ctx.arc(pt.x, pt.y, 12, 0, Math.PI * 2)
        ctx.fillStyle = glow
        ctx.fill()

        // ── Draw core dot ─────────────────────────────────────────────────
        ctx.beginPath()
        ctx.arc(pt.x, pt.y, 2.5, 0, Math.PI * 2)
        ctx.fillStyle = '#ffffff'
        ctx.fill()

        // ── Draw heading arrow toward target ──────────────────────────────
        const tPt  = map.latLngToLayerPoint([bot.tLat, bot.tLon])
        const dx   = tPt.x - pt.x
        const dy   = tPt.y - pt.y
        const len  = Math.sqrt(dx * dx + dy * dy)
        if (len > 30) {
          const nx   = dx / len
          const ny   = dy / len
          const arrowStart = 16
          const arrowEnd   = 28
          ctx.beginPath()
          ctx.moveTo(pt.x + nx * arrowStart, pt.y + ny * arrowStart)
          ctx.lineTo(pt.x + nx * arrowEnd,   pt.y + ny * arrowEnd)
          ctx.strokeStyle = `rgba(${bot.rgb},0.25)`
          ctx.lineWidth   = 1
          ctx.setLineDash([2, 4])
          ctx.stroke()
          ctx.setLineDash([])
        }

        // ── Label: Bot ID (tiny, subtle) ──────────────────────────────────
        ctx.font        = '9px monospace'
        ctx.fillStyle   = `rgba(${bot.rgb},0.55)`
        ctx.textAlign   = 'left'
        ctx.fillText(`BOT-${bot.id + 1}`, pt.x + 10, pt.y - 8)
      })

      s.raf = requestAnimationFrame(frame)
    }

    s.raf = requestAnimationFrame(frame)

    // ── Cleanup ──────────────────────────────────────────────────────────
    return () => {
      cancelAnimationFrame(s.raf)
      if (canvas.parentNode) canvas.parentNode.removeChild(canvas)
      map.off('resize', syncSize)
    }
  }, [map])

  return null
}
