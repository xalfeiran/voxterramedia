import { useMemo, useCallback } from 'react'
import {
  WordCloud,
  AnimatedWordRenderer,
  type WordCloudProps,
  type FinalWordData,
  type Word,
  type WordRendererData,
} from '@isoterik/react-word-cloud'
import type { Ref } from 'react'
import type { MapOutlet } from '@/types'
import { useFilterStore } from '@/stores/filterStore'

// ── Palette ──────────────────────────────────────────────────────────────────
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

// ── Props ─────────────────────────────────────────────────────────────────────
interface Props {
  /** All outlets (unfiltered) to compute per-country counts */
  outlets: MapOutlet[]
  /** Called after a selection so the parent can scroll the results list */
  onSelect?: () => void
}

// ── Component ─────────────────────────────────────────────────────────────────
export default function CountryCloud({ outlets, onSelect }: Props) {
  const { country: selected, setCountry } = useFilterStore()

  // Build word list + track max for font scaling
  const { words, maxCount } = useMemo(() => {
    const map = new Map<string, number>()
    outlets.forEach(o => map.set(o.country, (map.get(o.country) ?? 0) + 1))
    const entries = Array.from(map.entries())
    const max = Math.max(...entries.map(([, c]) => c), 1)
    return {
      words: entries.map(([code, count]) => ({ text: code, value: count })),
      maxCount: max,
    }
  }, [outlets])

  // Font size: log scale 11 px → 46 px
  const fontSize: WordCloudProps['fontSize'] = useCallback(
    (word: Word) => {
      const ratio = Math.log(word.value + 1) / Math.log(maxCount + 1)
      return Math.round(11 + ratio * 35)
    },
    [maxCount],
  )

  // Fill: full color for selected / "all", dimmed for others
  const fill: WordCloudProps['fill'] = useCallback(
    (word: Word) => {
      const color = colorFor(word.text)
      if (selected === 'all' || selected === word.text) return color
      return color + '38' // ~22 % opacity — dim unselected words
    },
    [selected],
  )

  // Animated word renderer (entrance animation, stable ref)
  const renderWord: WordCloudProps['renderWord'] = useCallback(
    (data: WordRendererData, ref?: Ref<SVGTextElement>) => (
      <AnimatedWordRenderer
        ref={ref ?? null}
        data={data}
        animationDelay={(_w: Word, i: number) => i * 35}
        textStyle={{ cursor: 'pointer' }}
      />
    ),
    [],
  )

  // Click: toggle filter for the clicked country
  const handleWordClick = useCallback(
    (word: FinalWordData) => {
      setCountry(selected === word.text ? 'all' : word.text)
      onSelect?.()
    },
    [selected, setCountry, onSelect],
  )

  return (
    <div className="space-y-1.5">
      {/* "All" reset pill — sits above the cloud */}
      <button
        onClick={() => { setCountry('all'); onSelect?.() }}
        className={`px-2.5 py-1 rounded-full text-xs font-semibold transition-all ${
          selected === 'all'
            ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30'
            : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-slate-200'
        }`}
      >
        All countries
      </button>

      {/* Word cloud — horizontal layout, rectangular spiral for density */}
      <WordCloud
        words={words}
        width={272}
        height={220}
        fontSize={fontSize}
        fill={fill}
        padding={4}
        rotate={() => 0}
        font="Inter, ui-sans-serif, system-ui, sans-serif"
        fontWeight="700"
        spiral="rectangular"
        renderWord={renderWord}
        onWordClick={handleWordClick}
        svgProps={{ style: { overflow: 'visible' } }}
      />
    </div>
  )
}
