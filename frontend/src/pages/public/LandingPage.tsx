import { useEffect, useRef, useState, FormEvent, useMemo } from 'react'
import { Link } from 'react-router-dom'
import { Globe, Newspaper, Radio, Tv, MapPin, ChevronRight, ExternalLink, Star, GitBranch, Send, CheckCircle2, Menu, X } from 'lucide-react'
import { useStats } from '@/api/queries'
import WorldMapBackground from '@/components/WorldMapBackground'
import { useSeo } from '@/hooks/useSeo'

/**
 * Mindware logo — replace /mindware-logo.png in frontend/public/ with the
 * actual logo file. Keep the same filename and it will update automatically.
 */
function MindwareLogo({ className = '' }: { className?: string }) {
  return (
    <img
      src="/mindware-logo.png"
      alt="Mindware"
      className={className}
      draggable={false}
    />
  )
}

// ── Top Mindware banner ───────────────────────────────────────────────────
function MindwareBanner() {
  return (
    <div className="bg-slate-900 border-b border-slate-800 py-1.5 px-4">
      <div className="max-w-6xl mx-auto flex items-center justify-center gap-2.5 text-xs text-slate-400">
        <MindwareLogo className="w-4 h-4 rounded-sm object-contain opacity-70" />
        <span>A product by</span>
        <a
          href="https://mindware.com.mx"
          target="_blank"
          rel="noopener noreferrer"
          className="text-slate-300 hover:text-white font-medium transition-colors hover:underline"
        >
          mindware.com.mx
        </a>
        <span className="text-slate-600 hidden sm:inline mx-1">·</span>
        <span className="hidden sm:inline text-slate-500">Building tools for a more informed world</span>
      </div>
    </div>
  )
}

// ── Contribute contact form ───────────────────────────────────────────────
type Topic = 'outlet' | 'error' | 'code' | 'other'

const TOPICS: { id: Topic; label: string; hint: string }[] = [
  { id: 'outlet', label: 'Submit an outlet', hint: 'I know a news outlet that should be listed' },
  { id: 'error',  label: 'Report an error',  hint: 'Something in the catalog is wrong or outdated' },
  { id: 'code',   label: 'Contribute code',  hint: 'I\'d like to help build or improve the platform' },
  { id: 'other',  label: 'Other',            hint: 'General feedback, partnerships, or anything else' },
]

function ContributeForm() {
  const [topic,      setTopic]      = useState<Topic>('outlet')
  const [name,       setName]       = useState('')
  const [email,      setEmail]      = useState('')
  const [url,        setUrl]        = useState('')
  const [message,    setMessage]    = useState('')
  const [submitting, setSubmitting] = useState(false)
  const [submitted,  setSubmitted]  = useState(false)

  async function handleSubmit(e: FormEvent) {
    e.preventDefault()
    setSubmitting(true)
    // TODO: wire up to /api/contact or your preferred endpoint.
    // For now we fall back to a mailto so no message is lost.
    const body = [
      `Topic: ${topic}`,
      `Name: ${name}`,
      url ? `URL: ${url}` : '',
      `Message: ${message}`,
    ].filter(Boolean).join('\n')

    window.location.href =
      `mailto:hola@mindware.com.mx?subject=${encodeURIComponent(`[VoxTerra] ${TOPICS.find(t => t.id === topic)?.label}`)}&body=${encodeURIComponent(body)}`

    setTimeout(() => { setSubmitting(false); setSubmitted(true) }, 600)
  }

  if (submitted) {
    return (
      <div className="flex flex-col items-center justify-center py-12 gap-3 text-center">
        <CheckCircle2 className="w-12 h-12 text-emerald-400" />
        <p className="text-lg font-semibold text-white">Thanks for reaching out!</p>
        <p className="text-slate-400 text-sm max-w-xs">We'll get back to you at <span className="text-slate-300">{email}</span> as soon as possible.</p>
        <button onClick={() => { setSubmitted(false); setName(''); setEmail(''); setUrl(''); setMessage('') }}
          className="mt-2 text-sm text-blue-400 hover:text-blue-300 transition-colors">
          Send another message
        </button>
      </div>
    )
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-5">
      {/* Topic pills */}
      <div>
        <p className="text-xs text-slate-400 uppercase tracking-widest mb-3 font-medium">I want to…</p>
        <div className="flex flex-wrap gap-2">
          {TOPICS.map(t => (
            <button
              key={t.id}
              type="button"
              onClick={() => setTopic(t.id)}
              className={`px-3.5 py-1.5 rounded-full text-sm font-medium border transition-all ${
                topic === t.id
                  ? 'bg-emerald-500/20 border-emerald-500/60 text-emerald-300'
                  : 'bg-slate-800 border-slate-700 text-slate-400 hover:border-slate-500 hover:text-slate-300'
              }`}
            >
              {t.label}
            </button>
          ))}
        </div>
        <p className="text-xs text-slate-500 mt-2 italic">{TOPICS.find(t => t.id === topic)?.hint}</p>
      </div>

      {/* Name + Email */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label className="block text-xs text-slate-400 mb-1.5 font-medium">Name</label>
          <input
            required
            type="text"
            value={name}
            onChange={e => setName(e.target.value)}
            placeholder="Your name"
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500/60 transition-colors"
          />
        </div>
        <div>
          <label className="block text-xs text-slate-400 mb-1.5 font-medium">Email</label>
          <input
            required
            type="email"
            value={email}
            onChange={e => setEmail(e.target.value)}
            placeholder="you@example.com"
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500/60 transition-colors"
          />
        </div>
      </div>

      {/* URL — shown for outlet submissions */}
      {topic === 'outlet' && (
        <div>
          <label className="block text-xs text-slate-400 mb-1.5 font-medium">Outlet URL</label>
          <input
            type="url"
            value={url}
            onChange={e => setUrl(e.target.value)}
            placeholder="https://example.com"
            className="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500/60 transition-colors"
          />
        </div>
      )}

      {/* Message */}
      <div>
        <label className="block text-xs text-slate-400 mb-1.5 font-medium">Message</label>
        <textarea
          required
          rows={4}
          value={message}
          onChange={e => setMessage(e.target.value)}
          placeholder={
            topic === 'outlet' ? 'Country, city, language, media type… anything you know about it.' :
            topic === 'error'  ? 'What\'s wrong and where did you spot it?' :
            topic === 'code'   ? 'What you\'d like to build or fix, your stack background, GitHub handle…' :
                                 'What\'s on your mind?'
          }
          className="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-emerald-500/60 transition-colors resize-none"
        />
      </div>

      <button
        type="submit"
        disabled={submitting}
        className="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-60 text-white font-semibold px-6 py-2.5 rounded-xl transition-all text-sm"
      >
        <Send className="w-4 h-4" />
        {submitting ? 'Sending…' : 'Send message'}
      </button>
    </form>
  )
}

// ── Animated counter ─────────────────────────────────────────────────────
function useCounter(target: number, duration = 2000) {
  const [count, setCount] = useState(0)
  const ref = useRef<number | null>(null)
  useEffect(() => {
    if (target === 0) return
    const start = performance.now()
    const tick = (now: number) => {
      const progress = Math.min((now - start) / duration, 1)
      const eased = 1 - Math.pow(1 - progress, 3) // ease-out cubic
      setCount(Math.round(eased * target))
      if (progress < 1) ref.current = requestAnimationFrame(tick)
    }
    ref.current = requestAnimationFrame(tick)
    return () => { if (ref.current) cancelAnimationFrame(ref.current) }
  }, [target, duration])
  return count
}

// ── Stat card ────────────────────────────────────────────────────────────
function StatCard({ label, value, icon: Icon, color }: {
  label: string; value: number; icon: React.ElementType; color: string
}) {
  const count = useCounter(value)
  return (
    <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex items-center gap-4 hover:border-slate-700 transition-colors">
      <div className={`w-12 h-12 rounded-xl flex items-center justify-center ${color}`}>
        <Icon className="w-6 h-6" />
      </div>
      <div>
        <p className="text-3xl font-bold text-white tabular-nums">{count}</p>
        <p className="text-sm text-slate-400 mt-0.5">{label}</p>
      </div>
    </div>
  )
}

// ── Country card ─────────────────────────────────────────────────────────
function CountryCard({ code, emoji, name, outlets, color, maxOutlets }: {
  code: string; emoji: string; name: string; outlets: number; color: string; maxOutlets: number
}) {
  return (
    <Link
      to={`/countries/${code}`}
      className="group bg-slate-900 border border-slate-800 rounded-2xl p-6 hover:border-slate-600 transition-all hover:-translate-y-1"
    >
      <div className="text-4xl mb-4">{emoji}</div>
      <h3 className="text-lg font-semibold text-white mb-1">{name}</h3>
      <p className="text-sm text-slate-400 mb-4">{outlets} media outlet{outlets !== 1 ? 's' : ''}</p>
      <div className="w-full bg-slate-800 rounded-full h-1.5">
        <div
          className="h-1.5 rounded-full transition-all"
          style={{ width: `${Math.min((outlets / maxOutlets) * 100, 100)}%`, backgroundColor: color }}
        />
      </div>
      <p className="text-xs text-slate-500 mt-2 flex items-center gap-1 group-hover:text-slate-300 transition-colors">
        Explore outlets <ChevronRight className="w-3 h-3" />
      </p>
    </Link>
  )
}

// Deterministic accent color for countries not in COUNTRY_META
const PALETTE = [
  '#3b82f6','#10b981','#ef4444','#6366f1','#f59e0b','#8b5cf6',
  '#22c55e','#f97316','#06b6d4','#ec4899','#a3e635','#e11d48',
  '#14b8a6','#d946ef','#f43f5e','#84cc16','#0ea5e9','#fb923c',
]
function colorForCode(code: string, meta: Record<string, { color: string }>): string {
  if (meta[code]) return meta[code].color
  // hash code letters to pick a stable palette color
  const n = code.split('').reduce((acc, c) => acc + c.charCodeAt(0), 0)
  return PALETTE[n % PALETTE.length]
}

// ── Featured outlet card ─────────────────────────────────────────────────
interface FeaturedOutlet {
  name: string; url: string; country: string; city: string; type: string; emoji: string
}

const FEATURED: FeaturedOutlet[] = [
  { name: 'The New York Times',  url: 'https://www.nytimes.com',          country: 'US', city: 'New York',        type: 'National',  emoji: '🇺🇸' },
  { name: 'BBC News',            url: 'https://www.bbc.com/news',         country: 'GB', city: 'London',          type: 'TV/Digital',emoji: '🇬🇧' },
  { name: 'Al Jazeera',          url: 'https://www.aljazeera.com',        country: 'QA', city: 'Doha',            type: 'TV/Digital',emoji: '🇶🇦' },
  { name: 'Le Monde',            url: 'https://www.lemonde.fr',           country: 'FR', city: 'Paris',           type: 'National',  emoji: '🇫🇷' },
  { name: 'El País',             url: 'https://elpais.com',               country: 'ES', city: 'Madrid',          type: 'National',  emoji: '🇪🇸' },
  { name: 'Der Spiegel',         url: 'https://www.spiegel.de',           country: 'DE', city: 'Hamburg',         type: 'National',  emoji: '🇩🇪' },
  { name: 'The Guardian',        url: 'https://www.theguardian.com',      country: 'GB', city: 'London',          type: 'National',  emoji: '🇬🇧' },
  { name: 'Reforma',             url: 'https://www.reforma.com',          country: 'MX', city: 'Ciudad de México', type: 'Nacional', emoji: '🇲🇽' },
  { name: 'Folha de S.Paulo',    url: 'https://www.folha.uol.com.br',     country: 'BR', city: 'São Paulo',       type: 'National',  emoji: '🇧🇷' },
  { name: 'The Hindu',           url: 'https://www.thehindu.com',         country: 'IN', city: 'Chennai',         type: 'National',  emoji: '🇮🇳' },
  { name: 'Sydney Morning Herald',url:'https://www.smh.com.au',           country: 'AU', city: 'Sydney',          type: 'National',  emoji: '🇦🇺' },
  { name: 'Asahi Shimbun',       url: 'https://www.asahi.com',            country: 'JP', city: 'Tokyo',           type: 'National',  emoji: '🇯🇵' },
]

function FeaturedCard({ outlet }: { outlet: FeaturedOutlet }) {
  return (
    <div className="bg-slate-900 border border-slate-800 rounded-xl p-4 hover:border-blue-500/40 transition-all group">
      <div className="flex items-start justify-between mb-2">
        <span className="text-lg">{outlet.emoji}</span>
        <Star className="w-3.5 h-3.5 text-amber-400 fill-amber-400" />
      </div>
      <h4 className="font-semibold text-sm text-white mb-1 leading-snug">{outlet.name}</h4>
      <p className="text-xs text-slate-500 mb-3">{outlet.city}</p>
      <div className="flex items-center justify-between">
        <span className="text-xs bg-slate-800 text-slate-400 px-2 py-0.5 rounded-full">{outlet.type}</span>
        <a
          href={outlet.url}
          target="_blank"
          rel="noopener noreferrer"
          className="text-blue-400 hover:text-blue-300 transition-colors"
        >
          <ExternalLink className="w-3.5 h-3.5" />
        </a>
      </div>
    </div>
  )
}

// ── Main landing page ─────────────────────────────────────────────────────
export default function LandingPage() {
  const { data: stats, isLoading: statsLoading } = useStats()
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)

  useSeo({
    title      : 'VoxTerra.media — Worldwide News Media Catalog',
    description: 'Explore an interactive map of news media outlets from every country. Search, filter, and discover thousands of newspapers, TV channels, radio stations, and digital media around the globe.',
    canonical  : 'https://voxterra.media',
    keywords   : 'news media catalog, global media directory, newspapers worldwide, TV channels, radio stations, digital media, journalism resources, international press',
    hreflangs  : [
      { hreflang: 'en',        href: 'https://voxterra.media' },
      { hreflang: 'es',        href: 'https://voxterra.media' },
      { hreflang: 'x-default', href: 'https://voxterra.media' },
    ],
  })

  // ── JSON-LD: WebSite + Organization ─────────────────────────────────────
  const totalOutlets = stats?.total ?? 0
  const jsonLd = useMemo(() => ([
    {
      '@context'       : 'https://schema.org',
      '@type'          : 'WebSite',
      'name'           : 'VoxTerra.media',
      'url'            : 'https://voxterra.media',
      'description'    : 'Worldwide catalog of news media outlets — newspapers, TV, radio, and digital media from every country.',
      'inLanguage'     : ['en', 'es'],
      'potentialAction': {
        '@type'       : 'SearchAction',
        'target'      : {
          '@type'      : 'EntryPoint',
          'urlTemplate': 'https://voxterra.media/explore?search={search_term_string}',
        },
        'query-input' : 'required name=search_term_string',
      },
    },
    {
      '@context'   : 'https://schema.org',
      '@type'      : 'Organization',
      'name'       : 'VoxTerra.media',
      'url'        : 'https://voxterra.media',
      'logo'       : 'https://voxterra.media/voxterra-logo.svg',
      'sameAs'     : ['https://mindware.com.mx'],
      'description': `A worldwide catalog of ${totalOutlets} news media outlets organized by country, region, and city.`,
      'founder'    : {
        '@type': 'Organization',
        'name' : 'Mindware',
        'url'  : 'https://mindware.com.mx',
      },
    },
  ]), [totalOutlets])

  useEffect(() => {
    const existing = document.getElementById('jsonld-landing')
    if (existing) existing.remove()
    const script   = document.createElement('script')
    script.id      = 'jsonld-landing'
    script.type    = 'application/ld+json'
    script.text    = JSON.stringify(jsonLd)
    document.head.appendChild(script)
    return () => { script.remove() }
  }, [jsonLd])

  // Accent colors for well-known country codes; others get a generated color
  const COUNTRY_META: Record<string, { color: string }> = {
    US: { color: '#3b82f6' }, MX: { color: '#10b981' }, CA: { color: '#ef4444' },
    GB: { color: '#6366f1' }, FR: { color: '#f59e0b' }, DE: { color: '#8b5cf6' },
    BR: { color: '#22c55e' }, ES: { color: '#f97316' }, AR: { color: '#06b6d4' },
    IN: { color: '#ec4899' }, AU: { color: '#a3e635' }, JP: { color: '#e11d48' },
    IT: { color: '#14b8a6' }, CN: { color: '#d946ef' }, RU: { color: '#f43f5e' },
    KR: { color: '#84cc16' }, NL: { color: '#0ea5e9' }, PL: { color: '#fb923c' },
    QA: { color: '#8b5cf6' }, ZA: { color: '#22d3ee' },
  }

  // All countries from live stats, sorted by outlet count
  const sortedCountries = (stats?.by_country ?? [])
    .slice()
    .sort((a, b) => b.outlets - a.outlets)

  // Build country cards: all DB countries, sorted by outlet count
  const countryData = sortedCountries.map(c => ({
    code:    c.code,
    name:    c.name ?? c.code,
    emoji:   c.emoji,
    color:   colorForCode(c.code, COUNTRY_META),
    outlets: c.outlets,
  }))

  return (
    <div className="min-h-screen bg-slate-950 text-slate-200">

      {/* ── Mindware top banner ──────────────────────────────────────────── */}
      <MindwareBanner />

      {/* ── Nav ─────────────────────────────────────────────────────────── */}
      <nav className="border-b border-slate-800 bg-slate-950/80 backdrop-blur-sm sticky top-0 z-50">
        <div className="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
          <img src="/voxterra-logo.svg" alt="VoxTerra.media" className="h-8 sm:h-9 w-auto" />

          {/* Desktop links */}
          <div className="hidden sm:flex items-center gap-6">
            <Link to="/explore"      className="text-sm text-slate-400 hover:text-white transition-colors">Explore</Link>
            <Link to="/countries/US" className="text-sm text-slate-400 hover:text-white transition-colors">Countries</Link>
            <a href="#contribute" className="text-sm text-emerald-400 hover:text-emerald-300 transition-colors flex items-center gap-1">
              <GitBranch className="w-3.5 h-3.5" />
              Contribute
            </a>
            <Link to="/explore" className="bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
              Open Map
            </Link>
          </div>

          {/* Mobile: Open Map + hamburger */}
          <div className="flex sm:hidden items-center gap-2">
            <Link to="/explore" className="bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-3 py-1.5 rounded-lg transition-colors">
              Open Map
            </Link>
            <button
              onClick={() => setMobileMenuOpen(o => !o)}
              className="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors"
              aria-label="Toggle menu"
            >
              {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
            </button>
          </div>
        </div>

        {/* Mobile dropdown */}
        {mobileMenuOpen && (
          <div className="sm:hidden border-t border-slate-800 bg-slate-950 px-4 py-3 flex flex-col gap-1">
            <Link
              to="/explore"
              onClick={() => setMobileMenuOpen(false)}
              className="py-2.5 px-3 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-slate-800 transition-colors"
            >
              Explore Map
            </Link>
            <Link
              to="/countries/US"
              onClick={() => setMobileMenuOpen(false)}
              className="py-2.5 px-3 rounded-lg text-sm text-slate-300 hover:text-white hover:bg-slate-800 transition-colors"
            >
              Countries
            </Link>
            <a
              href="#contribute"
              onClick={() => setMobileMenuOpen(false)}
              className="py-2.5 px-3 rounded-lg text-sm text-emerald-400 hover:text-emerald-300 hover:bg-slate-800 transition-colors flex items-center gap-2"
            >
              <GitBranch className="w-3.5 h-3.5" />
              Contribute
            </a>
          </div>
        )}
      </nav>

      {/* ── Hero ────────────────────────────────────────────────────────── */}
      <section className="relative overflow-hidden">
        {/* World map background — outlet dots are live catalog data */}
        <WorldMapBackground />

        <div className="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center">
          <div className="inline-flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 rounded-full px-4 py-1.5 mb-8">
            <span className="w-2 h-2 bg-blue-400 rounded-full animate-pulse" />
            <span className="text-xs text-blue-300 font-medium">Live catalog · {stats?.total ?? 66} outlets</span>
          </div>

          <h1 className="text-5xl sm:text-6xl md:text-7xl font-extrabold text-white leading-tight mb-6">
            The World's
            <br />
            <span className="bg-gradient-to-r from-blue-400 via-cyan-400 to-emerald-400 bg-clip-text text-transparent">
              News Media Map
            </span>
          </h1>

          <p className="text-lg text-slate-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            Discover and explore {stats?.total ?? 66} news media outlets from every corner of the globe —
            organized by country, region, and city, with an interactive map.
          </p>

          <div className="flex flex-col sm:flex-row items-center justify-center gap-4 flex-wrap">
            <Link
              to="/explore"
              className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-8 py-4 rounded-xl transition-all hover:-translate-y-0.5 shadow-lg shadow-blue-500/25 text-base"
            >
              <MapPin className="w-5 h-5" />
              Explore the Map
            </Link>
            <Link
              to="/explore"
              className="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium px-8 py-4 rounded-xl transition-all border border-slate-700 text-base"
            >
              <Globe className="w-5 h-5 text-cyan-400" />
              Country Cloud
            </Link>
            <a
              href="#countries"
              className="inline-flex items-center gap-2 text-slate-400 hover:text-slate-200 font-medium px-4 py-4 transition-all text-base"
            >
              Browse by Country
              <ChevronRight className="w-4 h-4" />
            </a>
          </div>

          {/* Country flags — live from DB, each links to its country page */}
          <div className="flex items-center justify-center flex-wrap gap-3 mt-10 text-2xl">
            {statsLoading
              ? Array.from({ length: 12 }).map((_, i) => (
                  <span key={i} className="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 w-14 h-10 animate-pulse" />
                ))
              : sortedCountries.slice(0, 20).map(c => (
                  <Link
                    key={c.code}
                    to={`/countries/${c.code}`}
                    title={`${c.name} — ${c.outlets} outlet${c.outlets !== 1 ? 's' : ''}`}
                    className="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2 hover:border-slate-600 hover:-translate-y-0.5 transition-all"
                  >
                    {c.emoji}
                  </Link>
                ))
            }
          </div>
        </div>
      </section>

      {/* ── Stats ───────────────────────────────────────────────────────── */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard label="Total Outlets"    value={stats?.total ?? 66}                            icon={Newspaper} color="bg-blue-500/10 text-blue-400" />
          <StatCard label="National Media"   value={stats?.by_type?.national ?? 12}               icon={Globe}     color="bg-purple-500/10 text-purple-400" />
          <StatCard label="TV & Broadcast"   value={(stats?.by_type?.tv ?? 3)}                    icon={Tv}        color="bg-orange-500/10 text-orange-400" />
          <StatCard label="Digital Outlets"  value={stats?.by_type?.digital ?? 6}                 icon={Radio}     color="bg-cyan-500/10 text-cyan-400" />
        </div>
      </section>

      {/* ── Countries ───────────────────────────────────────────────────── */}
      <section id="countries" className="max-w-6xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16 scroll-mt-20">
        <div className="mb-8">
          <h2 className="text-2xl font-bold text-white">Browse by Country</h2>
          <p className="text-slate-400 mt-1">
            {countryData.length > 0
              ? `${countryData.length} countr${countryData.length !== 1 ? 'ies' : 'y'} in the catalog. Click any to see its outlets.`
              : 'Click a country to see all its outlets grouped by region and city.'
            }
          </p>
        </div>

        {statsLoading ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="bg-slate-900 border border-slate-800 rounded-2xl p-6 animate-pulse">
                <div className="w-10 h-10 bg-slate-800 rounded-lg mb-4" />
                <div className="h-4 bg-slate-800 rounded w-1/2 mb-2" />
                <div className="h-3 bg-slate-800 rounded w-1/3 mb-4" />
                <div className="h-1.5 bg-slate-800 rounded-full w-full" />
              </div>
            ))}
          </div>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {countryData.map(c => (
              <CountryCard
                key={c.code}
                {...c}
                maxOutlets={countryData[0]?.outlets ?? 1}
              />
            ))}
          </div>
        )}
      </section>

      {/* ── Featured ────────────────────────────────────────────────────── */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
        <div className="flex items-center justify-between mb-8">
          <div>
            <h2 className="text-2xl font-bold text-white">Featured Outlets</h2>
            <p className="text-slate-400 mt-1">Some of the most recognized news organizations around the world.</p>
          </div>
          <Link to="/explore?featured=1" className="text-sm text-blue-400 hover:text-blue-300 flex items-center gap-1">
            View all <ChevronRight className="w-4 h-4" />
          </Link>
        </div>
        <div className="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
          {FEATURED.map(o => <FeaturedCard key={o.name} outlet={o} />)}
        </div>
      </section>

      {/* ── Contribute ──────────────────────────────────────────────────── */}
      <section id="contribute" className="max-w-6xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
        <div className="relative bg-gradient-to-br from-emerald-900/20 to-slate-900 border border-emerald-500/15 rounded-3xl overflow-hidden">
          <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_bottom-right,_var(--tw-gradient-stops))] from-emerald-500/8 via-transparent to-transparent pointer-events-none" />

          <div className="relative grid grid-cols-1 lg:grid-cols-2 gap-0">

            {/* Left — intro */}
            <div className="p-10 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-slate-800/60">
              <div>
                <div className="flex items-center gap-2 mb-3">
                  <GitBranch className="w-4 h-4 text-emerald-400" />
                  <span className="text-xs font-semibold text-emerald-400 uppercase tracking-widest">Get involved</span>
                </div>
                <h2 className="text-2xl font-bold text-white mb-3">Help build the world's media catalog</h2>
                <p className="text-slate-400 leading-relaxed">
                  VoxTerra is a community effort. Whether you're a journalist who spotted a missing outlet,
                  a researcher who found wrong data, or a developer who wants to contribute code —
                  we'd love to hear from you.
                </p>

                <ul className="mt-6 space-y-2 text-sm text-slate-400">
                  {[
                    'Submit a news outlet we are missing',
                    'Report wrong coordinates or broken links',
                    'Contribute to the Laravel + React codebase',
                    'Partnerships, press, or general feedback',
                  ].map(item => (
                    <li key={item} className="flex items-center gap-2">
                      <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0" />
                      {item}
                    </li>
                  ))}
                </ul>
              </div>

              {/* Mindware credit */}
              <div className="mt-10 pt-6 border-t border-slate-800 flex items-center gap-3">
                <MindwareLogo className="w-8 h-8 rounded-md object-contain opacity-70" />
                <div>
                  <p className="text-sm text-slate-300 font-medium">Designed &amp; built by Mindware</p>
                  <a href="https://mindware.com.mx" target="_blank" rel="noopener noreferrer"
                    className="text-xs text-slate-500 hover:text-slate-300 transition-colors underline underline-offset-2">
                    mindware.com.mx
                  </a>
                </div>
              </div>
            </div>

            {/* Right — contact form */}
            <div className="p-10">
              <ContributeForm />
            </div>
          </div>
        </div>
      </section>

      {/* ── CTA Banner ──────────────────────────────────────────────────── */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 pb-16 sm:pb-20">
        <div className="relative bg-gradient-to-br from-blue-900/40 to-slate-900 border border-blue-500/20 rounded-3xl p-8 sm:p-10 text-center overflow-hidden">
          <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-blue-500/10 via-transparent to-transparent" />
          <div className="relative">
            <h2 className="text-3xl font-bold text-white mb-4">Ready to explore?</h2>
            <p className="text-slate-400 max-w-md mx-auto mb-8">
              Open the interactive map and discover news outlets from every country in the world,
              filtered by region, type, or language.
            </p>
            <Link
              to="/explore"
              className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-8 py-4 rounded-xl transition-all hover:-translate-y-0.5 shadow-lg shadow-blue-500/30"
            >
              <Globe className="w-5 h-5" />
              Open the Map
            </Link>
          </div>
        </div>
      </section>

      {/* ── Footer ──────────────────────────────────────────────────────── */}
      <footer className="border-t border-slate-800 py-8">
        <div className="max-w-6xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-3 text-slate-500 text-sm">
            <MindwareLogo className="w-5 h-5 rounded-sm object-contain opacity-50" />
            <span>VoxTerra.media &mdash; a <a href="https://mindware.com.mx" target="_blank" rel="noopener noreferrer" className="hover:text-slate-300 transition-colors">mindware</a> product</span>
          </div>
          <div className="flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-sm">
            <Link to="/explore"       className="text-slate-500 hover:text-slate-300 transition-colors">Explore</Link>
            <Link to="/countries/US"  className="text-slate-500 hover:text-slate-300 transition-colors">USA</Link>
            <Link to="/countries/GB"  className="text-slate-500 hover:text-slate-300 transition-colors">UK</Link>
            <Link to="/countries/MX"  className="text-slate-500 hover:text-slate-300 transition-colors">México</Link>
            <Link to="/countries/BR"  className="text-slate-500 hover:text-slate-300 transition-colors">Brazil</Link>
            <a href="#contribute" className="text-emerald-600 hover:text-emerald-400 transition-colors">Contribute</a>
          </div>
        </div>
      </footer>
    </div>
  )
}
