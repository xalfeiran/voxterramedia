import { useEffect, useRef, useState, FormEvent } from 'react'
import { Link } from 'react-router-dom'
import { Globe, Newspaper, Radio, Tv, MapPin, ChevronRight, ExternalLink, Star, GitBranch, Send, CheckCircle2 } from 'lucide-react'
import { useStats } from '@/api/queries'
import type { Stats } from '@/types'
import WorldMapBackground from '@/components/WorldMapBackground'

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
      <p className="text-sm text-slate-400 mb-4">{outlets} media outlets</p>
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
  const { data: stats } = useStats()

  const COUNTRY_META: Record<string, { emoji: string; color: string; fallback: number }> = {
    US: { emoji: '🇺🇸', color: '#3b82f6', fallback: 28 },
    MX: { emoji: '🇲🇽', color: '#10b981', fallback: 24 },
    CA: { emoji: '🇨🇦', color: '#ef4444', fallback: 14 },
    GB: { emoji: '🇬🇧', color: '#6366f1', fallback: 10 },
    FR: { emoji: '🇫🇷', color: '#f59e0b', fallback: 8  },
    DE: { emoji: '🇩🇪', color: '#8b5cf6', fallback: 7  },
    BR: { emoji: '🇧🇷', color: '#22c55e', fallback: 6  },
    ES: { emoji: '🇪🇸', color: '#f97316', fallback: 6  },
    AR: { emoji: '🇦🇷', color: '#06b6d4', fallback: 5  },
    IN: { emoji: '🇮🇳', color: '#ec4899', fallback: 5  },
    AU: { emoji: '🇦🇺', color: '#a3e635', fallback: 4  },
    JP: { emoji: '🇯🇵', color: '#e11d48', fallback: 4  },
  }

  // Build country cards: prefer live stats, fall back to meta defaults
  const countryData = stats?.by_country?.length
    ? stats.by_country
        .filter(c => COUNTRY_META[c.code])
        .sort((a, b) => b.outlets - a.outlets)
        .slice(0, 6)
        .map(c => ({
          code: c.code,
          name: c.name ?? c.code,
          emoji: COUNTRY_META[c.code].emoji,
          color: COUNTRY_META[c.code].color,
          outlets: c.outlets,
        }))
    : Object.entries(COUNTRY_META).slice(0, 6).map(([code, meta]) => ({
        code,
        name: { US:'United States', MX:'México', CA:'Canada', GB:'United Kingdom',
                FR:'France', DE:'Germany', BR:'Brazil', ES:'Spain',
                AR:'Argentina', IN:'India', AU:'Australia', JP:'Japan' }[code] ?? code,
        emoji: meta.emoji,
        color: meta.color,
        outlets: meta.fallback,
      }))

  return (
    <div className="min-h-screen bg-slate-950 text-slate-200">

      {/* ── Mindware top banner ──────────────────────────────────────────── */}
      <MindwareBanner />

      {/* ── Nav ─────────────────────────────────────────────────────────── */}
      <nav className="border-b border-slate-800 bg-slate-950/80 backdrop-blur-sm sticky top-0 z-50">
        <div className="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
          <div className="flex items-center">
            <img
              src="/voxterra-logo.svg"
              alt="VoxTerra.media"
              className="h-9 w-auto"
            />
          </div>
          <div className="flex items-center gap-6">
            <Link to="/explore"      className="text-sm text-slate-400 hover:text-white transition-colors">Explore</Link>
            <Link to="/countries/US" className="text-sm text-slate-400 hover:text-white transition-colors hidden sm:block">Countries</Link>
            <a
              href="#contribute"
              className="text-sm text-emerald-400 hover:text-emerald-300 transition-colors hidden sm:flex items-center gap-1"
            >
              <GitBranch className="w-3.5 h-3.5" />
              Contribute
            </a>
            <Link to="/explore" className="bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
              Open Map
            </Link>
          </div>
        </div>
      </nav>

      {/* ── Hero ────────────────────────────────────────────────────────── */}
      <section className="relative overflow-hidden">
        {/* World map background — outlet dots are live catalog data */}
        <WorldMapBackground />

        <div className="relative z-10 max-w-6xl mx-auto px-6 py-24 text-center">
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

          <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
            <Link
              to="/explore"
              className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-8 py-4 rounded-xl transition-all hover:-translate-y-0.5 shadow-lg shadow-blue-500/25 text-base"
            >
              <MapPin className="w-5 h-5" />
              Explore the Map
            </Link>
            <Link
              to="/countries/US"
              className="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium px-8 py-4 rounded-xl transition-all border border-slate-700 text-base"
            >
              Browse by Country
              <ChevronRight className="w-4 h-4" />
            </Link>
          </div>

          {/* Country flags preview */}
          <div className="flex items-center justify-center flex-wrap gap-3 mt-10 text-2xl">
            {['🇺🇸', '🇬🇧', '🇫🇷', '🇩🇪', '🇲🇽', '🇧🇷', '🇯🇵', '🇮🇳', '🇦🇺', '🇪🇸', '🇨🇦', '🇶🇦'].map(flag => (
              <span key={flag} className="bg-slate-900 border border-slate-800 rounded-xl px-4 py-2">{flag}</span>
            ))}
          </div>
        </div>
      </section>

      {/* ── Stats ───────────────────────────────────────────────────────── */}
      <section className="max-w-6xl mx-auto px-6 py-16">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <StatCard label="Total Outlets"    value={stats?.total ?? 66}                            icon={Newspaper} color="bg-blue-500/10 text-blue-400" />
          <StatCard label="National Media"   value={stats?.by_type?.national ?? 12}               icon={Globe}     color="bg-purple-500/10 text-purple-400" />
          <StatCard label="TV & Broadcast"   value={(stats?.by_type?.tv ?? 3)}                    icon={Tv}        color="bg-orange-500/10 text-orange-400" />
          <StatCard label="Digital Outlets"  value={stats?.by_type?.digital ?? 6}                 icon={Radio}     color="bg-cyan-500/10 text-cyan-400" />
        </div>
      </section>

      {/* ── Countries ───────────────────────────────────────────────────── */}
      <section className="max-w-6xl mx-auto px-6 pb-16">
        <div className="mb-8">
          <h2 className="text-2xl font-bold text-white">Browse by Country</h2>
          <p className="text-slate-400 mt-1">Click a country to see all its outlets grouped by region and city. Covering every continent.</p>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          {countryData.map(c => (
            <CountryCard key={c.code} {...c} maxOutlets={Math.max(...countryData.map(d => d.outlets))} />
          ))}
        </div>
      </section>

      {/* ── Featured ────────────────────────────────────────────────────── */}
      <section className="max-w-6xl mx-auto px-6 pb-16">
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
      <section id="contribute" className="max-w-6xl mx-auto px-6 pb-16">
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
      <section className="max-w-6xl mx-auto px-6 pb-20">
        <div className="relative bg-gradient-to-br from-blue-900/40 to-slate-900 border border-blue-500/20 rounded-3xl p-10 text-center overflow-hidden">
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
        <div className="max-w-6xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
          <div className="flex items-center gap-3 text-slate-500 text-sm">
            <MindwareLogo className="w-5 h-5 rounded-sm object-contain opacity-50" />
            <span>VoxTerra.media &mdash; a <a href="https://mindware.com.mx" target="_blank" rel="noopener noreferrer" className="hover:text-slate-300 transition-colors">mindware</a> product</span>
          </div>
          <div className="flex items-center gap-6 text-sm">
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
