import { useParams, Link } from 'react-router-dom'
import { useEffect } from 'react'
import { ChevronRight } from 'lucide-react'
import { useCountry, useMediaOutlets } from '@/api/queries'
import { TYPE_LABELS, COUNTRY_COLORS, formatUrl } from '@/lib/utils'
import type { CountryCode, MediaType } from '@/types'
import { useSeo } from '@/hooks/useSeo'

export default function CountryPage() {
  const { code = '' } = useParams<{ code: string }>()
  const { data: country, isLoading } = useCountry(code.toUpperCase())
  const { data: outletsData } = useMediaOutlets({ country: code.toUpperCase(), per_page: 100 })

  const outlets     = outletsData?.data ?? []
  const countryCode = code.toLowerCase()
  const canonUrl    = `https://voxterra.media/countries/${countryCode}`

  useSeo({
    title      : country ? `${country.flag_emoji} ${country.name} — News Media Outlets` : 'Country Media Outlets',
    description: country
      ? `Discover ${outlets.length || ''} news media outlets in ${country.name}. Browse newspapers, TV channels, radio stations and digital media from ${country.name}.`
      : 'Explore news media outlets by country on VoxTerra.media.',
    canonical  : canonUrl,
    keywords   : country ? `${country.name} news media, ${country.name} newspapers, ${country.name} TV channels, ${country.name} radio, ${country.name} journalism` : undefined,
    hreflangs  : [
      { hreflang: 'en',        href: canonUrl },
      { hreflang: 'es',        href: canonUrl },
      { hreflang: 'x-default', href: canonUrl },
    ],
  })

  // ── JSON-LD: CollectionPage + ItemList ────────────────────────────────────
  useEffect(() => {
    if (!country || outlets.length === 0) return

    const existing = document.getElementById('jsonld-country')
    if (existing) existing.remove()

    const script   = document.createElement('script')
    script.id      = 'jsonld-country'
    script.type    = 'application/ld+json'
    script.text    = JSON.stringify({
      '@context'        : 'https://schema.org',
      '@type'           : 'CollectionPage',
      'name'            : `${country.name} — News Media Outlets`,
      'url'             : canonUrl,
      'description'     : `Directory of ${outlets.length} news media outlets in ${country.name}.`,
      'inLanguage'      : 'en',
      'about'           : { '@type': 'Country', 'name': country.name },
      'mainEntity'      : {
        '@type'          : 'ItemList',
        'name'           : `News media in ${country.name}`,
        'numberOfItems'  : outlets.length,
        'itemListElement': outlets.slice(0, 50).map((outlet, idx) => ({
          '@type'   : 'ListItem',
          'position': idx + 1,
          'name'    : outlet.name,
          'url'     : `https://voxterra.media/outlets/${outlet.slug}`,
        })),
      },
    })
    document.head.appendChild(script)

    return () => { script.remove() }
  }, [country, outlets, canonUrl])

  if (isLoading) return (
    <div className="min-h-screen bg-slate-950 flex items-center justify-center">
      <div className="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
    </div>
  )

  if (!country) return (
    <div className="min-h-screen bg-slate-950 flex items-center justify-center text-slate-400">
      Country not found.
    </div>
  )

  const countryColor = COUNTRY_COLORS[code.toUpperCase() as CountryCode] ?? '#3b82f6'

  return (
    <div className="min-h-screen bg-slate-950 text-slate-200">
      <nav className="border-b border-slate-800 bg-slate-950/80 backdrop-blur-sm sticky top-0 z-50">
        <div className="max-w-6xl mx-auto px-6 h-14 flex items-center gap-2 text-sm text-slate-400">
          <Link to="/" className="hover:text-white transition-colors">Home</Link>
          <ChevronRight className="w-4 h-4" />
          <span className="text-white">{country.name}</span>
        </div>
      </nav>

      <header className="max-w-6xl mx-auto px-6 py-12">
        <div className="flex items-center gap-4 mb-4">
          <span className="text-5xl">{country.flag_emoji}</span>
          <div>
            <h1 className="text-4xl font-bold text-white">{country.name}</h1>
            <p className="text-slate-400 mt-1">{outlets.length} media outlets across {country.regions?.length ?? 0} regions</p>
          </div>
        </div>
        <div className="w-32 h-1 rounded-full" style={{ backgroundColor: countryColor }} />
      </header>

      <main className="max-w-6xl mx-auto px-6 pb-20">
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          {outlets.map(outlet => (
            <Link
              key={outlet.id}
              to={`/outlets/${outlet.slug}`}
              className="bg-slate-900 border border-slate-800 rounded-xl p-4 hover:border-blue-500/40 transition-all group"
            >
              <h3 className="font-semibold text-white mb-1 group-hover:text-blue-300 transition-colors">{outlet.name}</h3>
              <p className="text-xs text-slate-500 mb-3">{outlet.city.name} · {outlet.region.name}</p>
              <div className="flex items-center justify-between">
                <span className="text-xs bg-slate-800 text-slate-400 px-2 py-0.5 rounded">
                  {TYPE_LABELS[outlet.type as MediaType]}
                </span>
                <span className="text-xs text-slate-600">{formatUrl(outlet.url)}</span>
              </div>
            </Link>
          ))}
        </div>
      </main>
    </div>
  )
}
