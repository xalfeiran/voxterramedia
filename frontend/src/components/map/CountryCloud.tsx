import { useMemo } from 'react'
import type { MapOutlet } from '@/types'
import { useFilterStore } from '@/stores/filterStore'

/** Convert ISO 3166-1 alpha-2 to flag emoji */
function toFlagEmoji(code: string): string {
  if (code.length !== 2) return '🌐'
  return code.toUpperCase().replace(/./g, c =>
    String.fromCodePoint(0x1f1e6 + c.charCodeAt(0) - 65)
  )
}

/** Stable accent color from a country code */
const PALETTE = [
  '#3b82f6', '#10b981', '#ef4444', '#6366f1', '#f59e0b', '#8b5cf6',
  '#22c55e', '#f97316', '#06b6d4', '#ec4899', '#a3e635', '#e11d48',
  '#14b8a6', '#d946ef', '#f43f5e', '#84cc16', '#0ea5e9', '#fb923c',
  '#a78bfa', '#34d399', '#fbbf24', '#60a5fa', '#f472b6', '#4ade80',
]

function colorFor(code: string): string {
  const n = code.split('').reduce((s, c) => s + c.charCodeAt(0), 0)
  return PALETTE[n % PALETTE.length]
}

/** Hex color → rgba string with given alpha (0–1) */
function hex2rgba(hex: string, alpha: number): string {
  const r = parseInt(hex.slice(1, 3), 16)
  const g = parseInt(hex.slice(3, 5), 16)
  const b = parseInt(hex.slice(5, 7), 16)
  return `rgba(${r},${g},${b},${alpha})`
}

interface Props {
  /** All outlets (unfiltered) — used to compute per-country counts */
  outlets: MapOutlet[]
  /** Called after a country tag is clicked (e.g. to scroll results list to top) */
  onSelect?: () => void
}

export default function CountryCloud({ outlets, onSelect }: Props) {
  const { country: selected, setCountry } = useFilterStore()

  /** Sorted country counts from all outlets */
  const items = useMemo(() => {
    const map = new Map<string, number>()
    outlets.forEach(o => map.set(o.country, (map.get(o.country) ?? 0) + 1))
    return Array.from(map.entries())
      .map(([code, count]) => ({ code, count }))
      .sort((a, b) => b.count - a.count)
  }, [outlets])

  const maxCount = items[0]?.count ?? 1

  function handleClick(code: string) {
    setCountry(selected === code ? 'all' : code)
    onSelect?.()
  }

  return (
    <div className="flex flex-wrap gap-1.5 items-center">
      {/* "All" reset pill */}
      <button
        onClick={() => { setCountry('all'); onSelect?.() }}
        className={`px-2.5 py-1 rounded-full text-xs font-semibold transition-all ${
          selected === 'all'
            ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30'
            : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-slate-200'
        }`}
      >
        All
      </button>

      {items.map(({ code, count }) => {
        const ratio = Math.log(count + 1) / Math.log(maxCount + 1)
        const color  = colorFor(code)
        const isSelected = selected === code

        // Scale font-size 10 → 19 px and padding proportionally
        const fs = Math.round(10 + ratio * 9)
        const px = Math.round(6  + ratio * 5)
        const py = Math.round(2  + ratio * 2)

        return (
          <button
            key={code}
            onClick={() => handleClick(code)}
            title={`${code} — ${count} outlet${count !== 1 ? 's' : ''}`}
            style={{
              fontSize:        `${fs}px`,
              paddingLeft:     `${px}px`,
              paddingRight:    `${px}px`,
              paddingTop:      `${py}px`,
              paddingBottom:   `${py}px`,
              color:           isSelected ? '#fff' : color,
              backgroundColor: isSelected ? color : hex2rgba(color, 0.12),
              border:          isSelected
                ? 'none'
                : `1px solid ${hex2rgba(color, 0.3)}`,
              boxShadow:       isSelected
                ? `0 0 8px ${hex2rgba(color, 0.4)}`
                : 'none',
            }}
            className="rounded-full font-medium transition-all hover:opacity-90 leading-tight whitespace-nowrap"
          >
            {toFlagEmoji(code)}&nbsp;{code}
            &nbsp;<span style={{ opacity: 0.65 }}>({count})</span>
          </button>
        )
      })}
    </div>
  )
}
