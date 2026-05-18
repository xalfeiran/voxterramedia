import { useState, useMemo, useCallback } from 'react'
import { Search, Globe, Filter } from 'lucide-react'
import { useMapOutlets } from '@/api/queries'
import { useFilterStore } from '@/stores/filterStore'
import { COUNTRY_COLORS, COUNTRY_NAMES, TYPE_LABELS, formatUrl } from '@/lib/utils'
import type { CountryCode, MapOutlet, MediaType } from '@/types'
import ExploreMap from '@/components/map/ExploreMap'
import BotStatusPanel from '@/components/map/BotStatusPanel'

export default function ExplorePage() {
  const { data: outlets = [], isLoading } = useMapOutlets()
  const { country, type, search, setCountry, setType, setSearch } = useFilterStore()
  const [flyTo, setFlyTo] = useState<{ lat: number; lon: number } | null>(null)

  const filtered = useMemo(() => {
    let result = outlets
    if (country !== 'all') result = result.filter(o => o.country === country)
    if (type) result = result.filter(o => o.type === type)
    if (search) {
      const q = search.toLowerCase()
      result = result.filter(o =>
        o.name.toLowerCase().includes(q) ||
        o.city.toLowerCase().includes(q) ||
        o.region.toLowerCase().includes(q)
      )
    }
    return result
  }, [outlets, country, type, search])

  const counts = useMemo(() => {
    const c: Record<string, number> = { all: outlets.length, CA: 0, US: 0, MX: 0 }
    outlets.forEach(o => { if (c[o.country] !== undefined) c[o.country]++ })
    return c
  }, [outlets])

  const handleItemClick = useCallback((outlet: MapOutlet) => {
    setFlyTo({ lat: outlet.lat, lon: outlet.lon })
  }, [])

  return (
    <div className="flex h-screen bg-slate-950 text-slate-200 overflow-hidden">
      {/* Sidebar */}
      <aside className="w-80 flex-none bg-slate-900 border-r border-slate-800 flex flex-col">
        {/* Header */}
        <div className="p-4 border-b border-slate-800">
          <h1 className="text-base font-semibold text-white flex items-center gap-2">
            <Globe className="w-4 h-4 text-blue-400" />
            News Media Explorer
          </h1>
          <p className="text-xs text-slate-500 mt-0.5">Worldwide · {outlets.length} outlets</p>
        </div>

        {/* Filters */}
        <div className="p-4 border-b border-slate-800 space-y-3">
          {/* Search */}
          <div className="relative">
            <Search className="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-slate-500" />
            <input
              type="text"
              placeholder="Search outlets, cities..."
              value={search}
              onChange={e => setSearch(e.target.value)}
              className="w-full bg-slate-800 border border-slate-700 rounded-md pl-8 pr-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
            />
          </div>

          {/* Country pills */}
          <div className="flex flex-wrap gap-1.5">
            {(['all', 'CA', 'US', 'MX'] as const).map(c => (
              <button
                key={c}
                onClick={() => setCountry(c)}
                className={`px-2.5 py-1 rounded-full text-xs font-medium transition-colors ${
                  country === c
                    ? 'text-white'
                    : 'bg-slate-800 text-slate-400 hover:bg-slate-700'
                }`}
                style={country === c ? { backgroundColor: c === 'all' ? '#3b82f6' : COUNTRY_COLORS[c as CountryCode] } : {}}
              >
                {c === 'all' ? 'All' : COUNTRY_NAMES[c as CountryCode]}
                <span className="ml-1 opacity-70">({counts[c] ?? 0})</span>
              </button>
            ))}
          </div>

          {/* Type filter */}
          <select
            value={type}
            onChange={e => setType(e.target.value as MediaType | '')}
            className="w-full bg-slate-800 border border-slate-700 rounded-md px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors"
          >
            <option value="">All types</option>
            {(['national','newspaper','digital','tv','radio','magazine'] as MediaType[]).map(t => (
              <option key={t} value={t}>{TYPE_LABELS[t]}</option>
            ))}
          </select>
        </div>

        {/* Results list */}
        <div className="flex-1 overflow-y-auto p-4 space-y-2 min-h-0">
          <p className="text-xs text-slate-500 mb-3 flex items-center gap-1">
            <Filter className="w-3 h-3" />
            {filtered.length} result{filtered.length !== 1 ? 's' : ''}
          </p>

          {isLoading && (
            <div className="space-y-2">
              {Array.from({ length: 6 }).map((_, i) => (
                <div key={i} className="bg-slate-800 rounded-lg p-3 animate-pulse">
                  <div className="h-3 bg-slate-700 rounded w-3/4 mb-2" />
                  <div className="h-2 bg-slate-700 rounded w-1/2" />
                </div>
              ))}
            </div>
          )}

          {!isLoading && filtered.length === 0 && (
            <div className="text-center py-12 text-slate-500">
              <Globe className="w-8 h-8 mx-auto mb-2 opacity-40" />
              <p className="text-sm">No outlets match your filters.</p>
            </div>
          )}

          {!isLoading && filtered.map(outlet => (
            <button
              key={outlet.id}
              onClick={() => handleItemClick(outlet)}
              className="w-full text-left bg-slate-800 hover:bg-slate-750 border border-slate-700 hover:border-blue-500/50 rounded-lg p-3 transition-all group"
            >
              <div className="flex items-start gap-2">
                <div
                  className="w-2 h-2 rounded-full mt-1.5 flex-none"
                  style={{ backgroundColor: COUNTRY_COLORS[outlet.country as CountryCode] }}
                />
                <div className="min-w-0">
                  <p className="font-medium text-sm text-slate-100 truncate group-hover:text-white">
                    {outlet.name}
                  </p>
                  <p className="text-xs text-slate-500 mt-0.5">{outlet.city} · {outlet.region}</p>
                  <div className="flex items-center gap-2 mt-1.5">
                    <span className="text-xs bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">
                      {TYPE_LABELS[outlet.type]}
                    </span>
                    <a
                      href={outlet.url}
                      target="_blank"
                      rel="noopener noreferrer"
                      onClick={e => e.stopPropagation()}
                      className="text-xs text-blue-400 hover:underline truncate"
                    >
                      {formatUrl(outlet.url)}
                    </a>
                  </div>
                </div>
              </div>
            </button>
          ))}
        </div>
        {/* Bot scout panel — pinned to bottom of sidebar */}
        <BotStatusPanel />
      </aside>

      {/* Map */}
      <main className="flex-1">
        <ExploreMap outlets={filtered} onMarkerClick={handleItemClick} flyTo={flyTo} />
      </main>
    </div>
  )
}
