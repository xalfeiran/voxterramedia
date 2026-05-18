import { useMapOutlets } from '@/api/queries'

// ── Projection ─────────────────────────────────────────────────────────────
const W = 1440
const H = 720

/** Convert geographic coordinates to SVG pixel coordinates */
function px(lon: number, lat: number) {
  return {
    x: ((lon + 180) / 360) * W,
    y: ((90 - lat) / 180) * H,
  }
}

/** Build an SVG path string from a list of [lon, lat] pairs */
function toSvgPath(pts: [number, number][]) {
  return pts
    .map(([lon, lat], i) => {
      const { x, y } = px(lon, lat)
      return `${i === 0 ? 'M' : 'L'} ${x.toFixed(1)} ${y.toFixed(1)}`
    })
    .join(' ') + ' Z'
}

// ── Simplified continent polygons [lon, lat][] ─────────────────────────────
// Deliberately rough — at low opacity they read as atmospheric shapes,
// not a geography textbook.  Accuracy within ~3–5° is fine here.
const CONTINENTS: [number, number][][] = [
  // ── North America ──────────────────────────────────────────────────────
  [
    [-168, 54], [-165, 62], [-160, 71], [-140, 70],
    [-100, 74], [-85,  82], [-68,  70], [-65,  62],
    [-55,  53], [-52,  47], [-70,  43], [-77,  35],
    [-80,  25], [-79,   8], [-90,  16], [-105, 23],
    [-117, 32], [-122, 37], [-124, 49], [-130, 55],
  ],
  // ── Greenland ──────────────────────────────────────────────────────────
  [
    [-42, 83], [-18, 77], [-18, 70],
    [-25, 62], [-44, 59], [-52, 67], [-55, 72],
  ],
  // ── South America ──────────────────────────────────────────────────────
  [
    [-79,  8], [-65, 11], [-35, -5],
    [-43,-23], [-53,-33], [-58,-34],
    [-67,-55], [-72,-41], [-71,-18],
    [-80, -4], [-80,  2],
  ],
  // ── Europe ─────────────────────────────────────────────────────────────
  [
    [ -9, 37], [ -9, 43], [ -4, 48], [  2, 51], [  5, 58],
    [ 15, 69], [ 28, 71], [ 33, 70], [ 28, 65], [ 25, 59],
    [ 21, 52], [ 16, 48], [ 12, 45], [ 12, 42], [ 17, 40],
    [ 23, 37], [ 29, 41], [ 36, 37], [ 40, 42], [ 40, 37],
  ],
  // ── Africa ─────────────────────────────────────────────────────────────
  [
    [ -6, 36], [-17, 15], [-13,  8], [ -4,  5],
    [  3,  6], [  9,  4], [  9,  0], [ 13, -9],
    [ 18,-34], [ 33,-28], [ 36,-24], [ 40,-10],
    [ 42,  2], [ 45,  2], [ 43, 12], [ 42, 16],
    [ 36, 22], [ 32, 30], [ 25, 33], [ 10, 37], [  2, 37],
  ],
  // ── Russia / North Asia ────────────────────────────────────────────────
  [
    [ 40, 42], [ 40, 65], [ 50, 68], [ 70, 72],
    [100, 72], [140, 72], [170, 72], [175, 65],
    [163, 55], [150, 56], [145, 44], [130, 60],
    [100, 60], [ 80, 55], [ 60, 55],
  ],
  // ── South & East Asia (Middle East → India → SE Asia → China) ──────────
  [
    [ 36, 37], [ 40, 37], [ 40, 42], [ 60, 55],
    [ 80, 55], [100, 60], [130, 60], [130, 42],
    [126, 38], [121, 25], [110, 18], [107, 20],
    [104,  2], [ 98, 16], [ 92, 22], [ 80,  8],
    [ 62,  8], [ 62, 24], [ 50, 30],
  ],
  // ── Japan ──────────────────────────────────────────────────────────────
  [
    [130, 32], [132, 34], [137, 36],
    [140, 40], [141, 44], [143, 44],
    [141, 42], [136, 36],
  ],
  // ── Australia ──────────────────────────────────────────────────────────
  [
    [114,-22], [114,-35], [119,-38], [130,-34],
    [138,-37], [148,-38], [153,-28], [152,-22],
    [147,-18], [138,-14], [130,-12], [122,-18],
  ],
  // ── New Zealand (simplified) ───────────────────────────────────────────
  [
    [172,-34], [174,-37], [173,-41],
    [171,-44], [168,-46], [170,-44], [172,-41],
  ],
  // ── Iceland ────────────────────────────────────────────────────────────
  [
    [-24, 64], [-14, 66], [-13, 65],
    [-18, 63], [-24, 63],
  ],
  // ── UK & Ireland (simplified blobs) ───────────────────────────────────
  [[-5, 50], [-3, 51], [0, 51], [2, 52], [0, 53], [-3, 54], [-5, 58], [-6, 57], [-5, 50]],
  [[-10, 52], [-6, 52], [-6, 55], [-8, 55], [-10, 53]],
]

// ── Graticule lines ────────────────────────────────────────────────────────
const LAT_LINES  = [-60, -30, 0, 30, 60]
const LON_LINES  = [-150, -120, -90, -60, -30, 0, 30, 60, 90, 120, 150]

// ── Component ──────────────────────────────────────────────────────────────
export default function WorldMapBackground() {
  const { data: outlets } = useMapOutlets()

  return (
    <div
      className="absolute inset-0 overflow-hidden pointer-events-none select-none"
      aria-hidden="true"
    >
      <svg
        viewBox={`0 0 ${W} ${H}`}
        className="w-full h-full"
        xmlns="http://www.w3.org/2000/svg"
        preserveAspectRatio="xMidYMid slice"
      >
        <defs>
          {/* Soft vignette mask so the map fades toward the edges */}
          <radialGradient id="vignette" cx="50%" cy="50%" r="65%">
            <stop offset="0%"   stopColor="white" stopOpacity="1" />
            <stop offset="100%" stopColor="white" stopOpacity="0" />
          </radialGradient>
          <mask id="fade-mask">
            <rect width={W} height={H} fill="url(#vignette)" />
          </mask>
        </defs>

        <g mask="url(#fade-mask)">
          {/* ── Graticule ──────────────────────────────────────────────── */}
          <g stroke="#1e293b" strokeWidth="0.6">
            {LAT_LINES.map(lat => {
              const { y } = px(0, lat)
              return <line key={`lat${lat}`} x1={0} y1={y} x2={W} y2={y} />
            })}
            {LON_LINES.map(lon => {
              const { x } = px(lon, 0)
              return <line key={`lon${lon}`} x1={x} y1={0} x2={x} y2={H} />
            })}
            {/* Equator — slightly brighter */}
            <line
              x1={0} y1={H / 2} x2={W} y2={H / 2}
              stroke="#1e3a5f" strokeWidth="1"
            />
          </g>

          {/* ── Continent fills ────────────────────────────────────────── */}
          <g
            fill="#1e3a5f"
            fillOpacity="0.55"
            stroke="#1d4ed8"
            strokeWidth="0.7"
            strokeOpacity="0.35"
          >
            {CONTINENTS.map((pts, i) => (
              <path key={i} d={toSvgPath(pts)} />
            ))}
          </g>

          {/* ── Outlet dots (data from the live catalog) ───────────────── */}
          {outlets?.map(o => {
            if (!o.lat || !o.lon) return null
            const { x, y } = px(o.lon, o.lat)
            return (
              <g key={o.id}>
                {/* Soft glow halo */}
                <circle cx={x} cy={y} r={6}  fill="#3b82f6" opacity={0.12} />
                {/* Core dot */}
                <circle cx={x} cy={y} r={2}  fill={o.is_featured ? '#f59e0b' : '#93c5fd'} opacity={o.is_featured ? 0.9 : 0.65} />
              </g>
            )
          })}
        </g>
      </svg>

      {/* Top & bottom gradient fade into slate-950 */}
      <div className="absolute inset-0 bg-gradient-to-b from-slate-950 via-slate-950/10 to-slate-950" />
      {/* Left & right gradient fade */}
      <div className="absolute inset-0 bg-gradient-to-r from-slate-950/70 via-transparent to-slate-950/70" />
    </div>
  )
}
