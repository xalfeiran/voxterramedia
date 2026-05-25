import { useState, useMemo, useCallback, useEffect, useRef } from 'react'
import { Search, Globe, Filter, Menu, ChevronLeft, Rss, Building2 } from 'lucide-react'
import { useMapOutlets } from '@/api/queries'
import { useFilterStore } from '@/stores/filterStore'
import { COUNTRY_COLORS, TYPE_LABELS, formatUrl } from '@/lib/utils'
import type { CountryCode, MapOutlet, MediaType } from '@/types'
import ExploreMap from '@/components/map/ExploreMap'
import BotStatusPanel from '@/components/map/BotStatusPanel'
import CountryCloud from '@/components/map/CountryCloud'
import { useSeo } from '@/hooks/useSeo'

export default function ExplorePage() {
  const { data: outlets = [], isLoading } = useMapOutlets()
  const {
    country, type, search, city, hasRss,
    setType, setSearch, setCity, setHasRss,
  } = useFilterStore()
  const [flyTo, setFlyTo] = useState<{ lat: number; lon: number } | null>(null)
  const resultsRef = useRef<HTMLDivElement | null>(null)

  // Sidebar open: default open on desktop, closed on mobile
  const [sidebarOpen, setSidebarOpen] = useState(() => window.innerWidth >= 768)

  // Close sidebar when resizing down to mobile
  useEffect(() => {
    function handleResize() {
      if (window.innerWidth < 768) setSidebarOpen(false)
      else setSidebarOpen(true)
    }
    window.addEventListener('resize', handleResize)
    return () => window.removeEventListener('resize', handleResize)
  }, [])

  useSeo({
    title      : 'Explore — Interactive World Media Map',
    description: 'Browse thousands of newspapers, TV channels, radio stations, and digital outlets on an interactive world map. Filter by country, type, or language.',
    canonical  : 'https://voxterra.media/explore',
    keywords   : 'interactive media map, world news map, explore media outlets, global journalism map, news by country',
    hreflangs  : [
      { hreflang: 'en',        href: 'https://voxterra.media/explore' },
      { hreflang: 'es',        href: 'https://voxterra.media/explore' },
      { hreflang: 'x-default', href: 'https://voxterra.media/explore' },
    ],
  })

  const filtered = useMemo(() => {
    let result = outlets
    if (country !== 'all') result = result.filter(o => o.country === country)
    if (type) result = result.filter(o => o.type === type)
    if (hasRss) result = result.filter(o => o.has_rss)
    if (city) {
      const q = city.toLowerCase()
      result = result.filter(o => o.city.toLowerCase().includes(q))
    }
    if (search) {
      const q = search.toLowerCase()
      result = result.filter(o =>
        o.name.toLowerCase().includes(q) ||
        o.city.toLowerCase().includes(q) ||
        o.region.toLowerCase().includes(q)
      )
    }
    return result
  }, [outlets, country, type, hasRss, city, search])

  const handleItemClick = useCallback((outlet: MapOutlet) => {
    setFlyTo({ lat: outlet.lat, lon: outlet.lon })
    // Auto-close sidebar on mobile after selecting an outlet
    if (window.innerWidth < 768) setSidebarOpen(false)
  }, [])

  // Clicking a pin on the map should NOT zoom — just open the popup (handled by Leaflet)
  const handleMarkerClick = useCallback((_outlet: MapOutlet) => {
    // intentionally left empty: popup opens via Leaflet's built-in behavior
  }, [])

  return (
    <div className="flex h-screen bg-slate-950 text-slate-200 overflow-hidden relative">

      {/* ── Mobile backdrop ────────────────────────────────────────────────── */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-30 bg-black/60 backdrop-blur-sm md:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* ── Sidebar ────────────────────────────────────────────────────────── */}
      <aside
        className={`
          fixed md:relative inset-y-0 left-0 z-40
          w-80 flex-none bg-slate-900 border-r border-slate-800 flex flex-col
          transform transition-transform duration-300 ease-in-out
          ${sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'}
          ${!sidebarOpen ? 'md:w-0 md:overflow-hidden md:border-0' : ''}
        `}
        style={{ willChange: 'transform' }}
      >
        {/* Header */}
        <div className="p-4 border-b border-slate-800 flex items-center justify-between">
          <div>
            <h1 className="text-base font-semibold text-white flex items-center gap-2">
              <Globe className="w-4 h-4 text-blue-400" />
              News Media Explorer
            </h1>
            <p className="text-xs text-slate-500 mt-0.5">Worldwide · {outlets.length} outlets</p>
          </div>
          {/* Close button — visible on all sizes when sidebar is open */}
          <button
            onClick={() => setSidebarOpen(false)}
            className="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
            aria-label="Close sidebar"
          >
            <ChevronLeft className="w-4 h-4" />
          </button>
        </div>

        {/* Filters */}
        <div className="p-4 border-b border-slate-800 space-y-3">
          {/* Search */}
          <div className="relative">
            <Search className="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-slate-500" />
            <input
              type="text"
              placeholder="Search outlets, regions..."
              value={search}
              onChange={e => setSearch(e.target.value)}
              className="w-full bg-slate-800 border border-slate-700 rounded-md pl-8 pr-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
            />
          </div>

          {/* City filter */}
          <div className="relative">
            <Building2 className="absolute left-2.5 top-2.5 w-3.5 h-3.5 text-slate-500" />
            <input
              type="text"
              placeholder="Filter by city..."
              value={city}
              onChange={e => setCity(e.target.value)}
              className="w-full bg-slate-800 border border-slate-700 rounded-md pl-8 pr-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors"
            />
          </div>

          {/* Country cloud */}
          <CountryCloud
            outlets={outlets}
            onSelect={() => resultsRef.current?.scrollTo({ top: 0, behavior: 'smooth' })}
          />

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

          {/* Quick-filter toggles: National media + RSS */}
          <div className="flex gap-2">
            {/* National media shortcut */}
            <button
              onClick={() => setType(type === 'national' ? '' : 'national')}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors flex-1 justify-center ${
                type === 'national'
                  ? 'bg-amber-600 text-white'
                  : 'bg-slate-800 text-slate-400 hover:bg-slate-700 border border-slate-700'
              }`}
            >
              <Globe className="w-3 h-3" />
              National
            </button>

            {/* RSS toggle */}
            <button
              onClick={() => setHasRss(!hasRss)}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium transition-colors flex-1 justify-center ${
                hasRss
                  ? 'bg-orange-600 text-white'
                  : 'bg-slate-800 text-slate-400 hover:bg-slate-700 border border-slate-700'
              }`}
            >
              <Rss className="w-3 h-3" />
              RSS only
            </button>
          </div>
        </div>

        {/* Results list */}
        <div ref={resultsRef} className="flex-1 overflow-y-auto p-4 space-y-2 min-h-0">
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
                <div className="min-w-0 w-full">
                  <p className="font-medium text-sm text-slate-100 truncate group-hover:text-white">
                    {outlet.name}
                  </p>
                  <p className="text-xs text-slate-500 mt-0.5">{outlet.city} · {outlet.region}</p>
                  <div className="flex items-center gap-2 mt-1.5 flex-wrap">
                    <span className="text-xs bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">
                      {TYPE_LABELS[outlet.type]}
                    </span>
                    {outlet.has_rss && (
                      <span className="text-xs bg-orange-900/50 text-orange-400 px-1.5 py-0.5 rounded flex items-center gap-1">
                        <Rss className="w-2.5 h-2.5" />
                        RSS
                      </span>
                    )}
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

      {/* ── Map area ───────────────────────────────────────────────────────── */}
      <main className="flex-1 relative min-w-0">

        {/* Hamburger — shown when sidebar is closed */}
        {!sidebarOpen && (
          <button
            onClick={() => setSidebarOpen(true)}
            className="absolute top-3 left-3 z-20 flex items-center gap-2 bg-slate-900/90 hover:bg-slate-800 border border-slate-700 text-slate-200 px-3 py-2 rounded-lg shadow-lg backdrop-blur-sm transition-all text-sm font-medium"
            aria-label="Open sidebar"
          >
            <Menu className="w-4 h-4" />
            <span className="hidden sm:inline">Filters</span>
          </button>
        )}

        <ExploreMap outlets={filtered} onMarkerClick={handleMarkerClick} flyTo={flyTo} />
      </main>
    </div>
  )
}
