import { useEffect } from 'react'
import { useParams, Link } from 'react-router-dom'
import { ChevronRight, ExternalLink, MapPin, Calendar, Globe, Rss, Clock } from 'lucide-react'
import { useMediaOutlet, useRssFeed } from '@/api/queries'
import { TYPE_LABELS, COUNTRY_COLORS } from '@/lib/utils'
import type { CountryCode, MediaType, RssFeedItem } from '@/types'
import { useSeo } from '@/hooks/useSeo'

// ── RSS post card ─────────────────────────────────────────────────────────
function RssPostCard({ item }: { item: RssFeedItem }) {
  const date = item.pub_date
    ? (() => {
        try { return new Date(item.pub_date).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' }) }
        catch { return item.pub_date }
      })()
    : null

  return (
    <a
      href={item.link}
      target="_blank"
      rel="noopener noreferrer"
      className="group flex gap-4 bg-slate-900 border border-slate-800 hover:border-slate-600 rounded-xl p-4 transition-all"
    >
      {item.image && (
        <img
          src={item.image}
          alt=""
          className="w-20 h-20 rounded-lg object-cover flex-none bg-slate-800"
          onError={e => { (e.currentTarget as HTMLImageElement).style.display = 'none' }}
        />
      )}
      <div className="min-w-0 flex-1">
        <h4 className="font-semibold text-sm text-slate-100 group-hover:text-white leading-snug line-clamp-2 mb-1.5">
          {item.title}
        </h4>
        {item.description && (
          <p className="text-xs text-slate-500 line-clamp-2 mb-2">{item.description}</p>
        )}
        <div className="flex items-center gap-1.5 text-xs text-slate-600">
          {date && <><Clock className="w-3 h-3" />{date}</>}
          <ExternalLink className="w-3 h-3 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" />
        </div>
      </div>
    </a>
  )
}

// ── Main page ─────────────────────────────────────────────────────────────
export default function OutletPage() {
  const { slug = '' } = useParams<{ slug: string }>()
  const { data: outlet, isLoading } = useMediaOutlet(slug)

  // Only fetch the feed if the outlet has_rss
  const { data: feedItems = [], isLoading: feedLoading } = useRssFeed(
    slug,
    !isLoading && !!outlet?.has_rss
  )

  const canonUrl = `https://voxterra.media/outlets/${slug}`

  useSeo({
    title      : outlet ? `${outlet.name} — ${outlet.country?.name ?? ''} Media` : 'Media Outlet',
    description: outlet
      ? (outlet.description ?? `${outlet.name} is a ${TYPE_LABELS[outlet.type as MediaType] ?? outlet.type} media outlet based in ${outlet.country?.name ?? ''}. Explore coverage, language, and contact details on VoxTerra.media.`)
      : 'Explore this media outlet on VoxTerra.media.',
    canonical  : canonUrl,
    ogType     : 'article',
    lang       : outlet?.language ?? 'en',
    keywords   : outlet
      ? `${outlet.name}, ${outlet.country?.name ?? ''} news, ${outlet.city?.name ?? ''} media, ${TYPE_LABELS[outlet.type as MediaType] ?? outlet.type}`
      : undefined,
    hreflangs  : [
      { hreflang: outlet?.language ?? 'en', href: canonUrl },
      { hreflang: 'x-default',              href: canonUrl },
    ],
  })

  // JSON-LD structured data for NewsMediaOrganization
  useEffect(() => {
    if (!outlet) return

    const existingScript = document.getElementById('jsonld-outlet')
    if (existingScript) existingScript.remove()

    const script = document.createElement('script')
    script.id   = 'jsonld-outlet'
    script.type = 'application/ld+json'
    script.text = JSON.stringify({
      '@context'   : 'https://schema.org',
      '@type'      : 'NewsMediaOrganization',
      'name'       : outlet.name,
      'url'        : outlet.url ?? undefined,
      'logo'       : outlet.logo_url ?? undefined,
      'foundingDate': outlet.founded_year ? String(outlet.founded_year) : undefined,
      'inLanguage' : outlet.language,
      'description': outlet.description ?? undefined,
      'location'   : {
        '@type'  : 'Place',
        'address': {
          '@type'  : 'PostalAddress',
          'addressCountry': outlet.country?.code ?? undefined,
        },
      },
    })
    document.head.appendChild(script)

    return () => { script.remove() }
  }, [outlet])

  if (isLoading) return (
    <div className="min-h-screen bg-slate-950 flex items-center justify-center">
      <div className="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
    </div>
  )

  if (!outlet) return (
    <div className="min-h-screen bg-slate-950 flex items-center justify-center text-slate-400">
      Outlet not found.
    </div>
  )

  const color = COUNTRY_COLORS[outlet.country.code as CountryCode] ?? '#3b82f6'

  return (
    <div className="min-h-screen bg-slate-950 text-slate-200">
      <nav className="border-b border-slate-800 bg-slate-950/80 backdrop-blur-sm sticky top-0 z-50">
        <div className="max-w-4xl mx-auto px-6 h-14 flex items-center gap-2 text-sm text-slate-400">
          <Link to="/" className="hover:text-white transition-colors">Home</Link>
          <ChevronRight className="w-4 h-4" />
          <Link to={`/countries/${outlet.country.code}`} className="hover:text-white transition-colors">
            {outlet.country.flag_emoji} {outlet.country.name}
          </Link>
          <ChevronRight className="w-4 h-4" />
          <span className="text-white truncate">{outlet.name}</span>
        </div>
      </nav>

      <main className="max-w-4xl mx-auto px-6 py-12">
        {/* ── Header ──────────────────────────────────────────────────────── */}
        <div className="mb-8">
          <div className="flex items-start gap-4 mb-4">
            <div className="w-1 h-16 rounded-full flex-none" style={{ backgroundColor: color }} />
            <div>
              <h1 className="text-4xl font-bold text-white mb-2">{outlet.name}</h1>
              <div className="flex flex-wrap items-center gap-3">
                <span className="text-sm bg-slate-800 text-slate-300 px-3 py-1 rounded-full">
                  {TYPE_LABELS[outlet.type as MediaType]}
                </span>
                <span className="text-sm text-slate-400 flex items-center gap-1">
                  <Globe className="w-3.5 h-3.5" />{outlet.language.toUpperCase()}
                </span>
                {outlet.founded_year && (
                  <span className="text-sm text-slate-400 flex items-center gap-1">
                    <Calendar className="w-3.5 h-3.5" />Est. {outlet.founded_year}
                  </span>
                )}
                {outlet.has_rss && (
                  <span className="text-sm flex items-center gap-1 text-orange-400">
                    <Rss className="w-3.5 h-3.5" />RSS
                  </span>
                )}
              </div>
            </div>
          </div>

          {outlet.description && (
            <p className="text-slate-400 leading-relaxed mt-4">{outlet.description}</p>
          )}
        </div>

        {/* ── Info cards ──────────────────────────────────────────────────── */}
        <div className="grid sm:grid-cols-2 gap-6 mb-10">
          <div className="bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h3 className="text-sm font-medium text-slate-400 mb-3 flex items-center gap-2">
              <MapPin className="w-4 h-4" />Location
            </h3>
            <p className="font-semibold text-white">{outlet.city.name}</p>
            <p className="text-sm text-slate-400">{outlet.region.name}</p>
            <p className="text-sm text-slate-400">{outlet.country.flag_emoji} {outlet.country.name}</p>
          </div>

          <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 flex flex-col justify-between">
            <h3 className="text-sm font-medium text-slate-400 mb-3">Visit outlet</h3>
            <div className="flex flex-col gap-2">
              <a
                href={outlet.url}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-medium px-5 py-3 rounded-lg transition-colors"
              >
                <ExternalLink className="w-4 h-4" />
                Open website
              </a>
              {outlet.rss_url && (
                <a
                  href={outlet.rss_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-2 bg-orange-600/20 hover:bg-orange-600/30 border border-orange-600/40 text-orange-400 font-medium px-5 py-3 rounded-lg transition-colors text-sm"
                >
                  <Rss className="w-4 h-4" />
                  Subscribe via RSS
                </a>
              )}
            </div>
          </div>
        </div>

        {/* ── Recent posts from RSS ────────────────────────────────────────── */}
        {outlet.has_rss && (
          <section>
            <div className="flex items-center gap-2 mb-5">
              <Rss className="w-4 h-4 text-orange-400" />
              <h2 className="text-lg font-semibold text-white">Recent Posts</h2>
              <span className="text-xs text-slate-500 ml-1">from RSS feed</span>
            </div>

            {feedLoading ? (
              <div className="space-y-3">
                {Array.from({ length: 5 }).map((_, i) => (
                  <div key={i} className="flex gap-4 bg-slate-900 border border-slate-800 rounded-xl p-4 animate-pulse">
                    <div className="w-20 h-20 bg-slate-800 rounded-lg flex-none" />
                    <div className="flex-1 space-y-2 py-1">
                      <div className="h-3 bg-slate-800 rounded w-3/4" />
                      <div className="h-3 bg-slate-800 rounded w-1/2" />
                      <div className="h-2 bg-slate-800 rounded w-1/4 mt-3" />
                    </div>
                  </div>
                ))}
              </div>
            ) : feedItems.length > 0 ? (
              <div className="space-y-3">
                {feedItems.map((item, i) => (
                  <RssPostCard key={i} item={item} />
                ))}
              </div>
            ) : (
              <div className="bg-slate-900 border border-slate-800 rounded-xl p-8 text-center text-slate-500">
                <Rss className="w-8 h-8 mx-auto mb-2 opacity-30" />
                <p className="text-sm">Could not load posts from this feed.</p>
              </div>
            )}
          </section>
        )}
      </main>
    </div>
  )
}
