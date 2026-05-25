import { useEffect } from 'react'

const BASE_URL  = 'https://voxterra.media'
const SITE_NAME = 'VoxTerra.media'
const DEFAULT_IMAGE = `${BASE_URL}/og-image.png`

/**
 * ISO 639-1 language code → BCP-47 locale (for og:locale and hreflang).
 * Extend as new outlet languages are added to MediaOutlet::LANGUAGES.
 */
const LANG_TO_LOCALE: Record<string, string> = {
  en: 'en_US', es: 'es_ES', fr: 'fr_FR', de: 'de_DE',
  pt: 'pt_BR', ar: 'ar_SA', zh: 'zh_CN', ja: 'ja_JP',
  ru: 'ru_RU', it: 'it_IT', nl: 'nl_NL', ko: 'ko_KR',
  hi: 'hi_IN', tr: 'tr_TR', pl: 'pl_PL', sv: 'sv_SE',
  fa: 'fa_IR', he: 'he_IL', id: 'id_ID', uk: 'uk_UA',
  sw: 'sw_KE', ms: 'ms_MY', th: 'th_TH', vi: 'vi_VN',
  ur: 'ur_PK',
}

interface HreflangEntry {
  /** BCP-47 language tag, e.g. "en", "es", "x-default" */
  hreflang: string
  href    : string
}

interface SeoOptions {
  title       : string
  description : string
  canonical  ?: string
  ogImage    ?: string
  ogType     ?: string
  /** ISO 639-1 language code of the page content (e.g. "es"). Defaults to "en". */
  lang       ?: string
  /** Alternate language versions for hreflang. Provide an entry per locale + x-default. */
  hreflangs  ?: HreflangEntry[]
  /** Additional comma-separated keywords for the <meta name="keywords"> tag */
  keywords   ?: string
  noIndex    ?: boolean
}

/**
 * Comprehensive SEO hook — manages <title>, meta description, keywords,
 * Open Graph, Twitter Cards, hreflang alternates, canonical URL, and
 * robots directives via direct DOM manipulation.
 *
 * Usage example (multilingual page):
 *   useSeo({
 *     title: 'México — Medios',
 *     description: 'Explora medios en México.',
 *     lang: 'es',
 *     canonical: 'https://voxterra.media/countries/mx',
 *     hreflangs: [
 *       { hreflang: 'en', href: 'https://voxterra.media/countries/mx' },
 *       { hreflang: 'es', href: 'https://voxterra.media/countries/mx' },
 *       { hreflang: 'x-default', href: 'https://voxterra.media/countries/mx' },
 *     ],
 *   })
 */
export function useSeo({
  title,
  description,
  canonical,
  ogImage   = DEFAULT_IMAGE,
  ogType    = 'website',
  lang      = 'en',
  hreflangs,
  keywords,
  noIndex   = false,
}: SeoOptions) {
  useEffect(() => {
    const fullTitle  = title.includes(SITE_NAME) ? title : `${title} — ${SITE_NAME}`
    const ogLocale   = LANG_TO_LOCALE[lang] ?? 'en_US'
    const canonUrl   = canonical ?? window.location.href

    // ── Title ──────────────────────────────────────────────────────────────
    document.title = fullTitle

    // ── <html lang="…"> ────────────────────────────────────────────────────
    document.documentElement.lang = lang

    // ── Helper: upsert <meta> ──────────────────────────────────────────────
    const setMeta = (selector: string, attr: string, value: string) => {
      let el = document.querySelector<HTMLMetaElement>(selector)
      if (!el) {
        el = document.createElement('meta')
        const [attrName, attrValue] = selector
          .replace('meta[', '')
          .replace(']', '')
          .split('=')
        el.setAttribute(attrName.trim(), attrValue.replace(/"/g, '').trim())
        document.head.appendChild(el)
      }
      el.setAttribute(attr, value)
    }

    // ── Helper: upsert single-value <link> ────────────────────────────────
    const setLink = (rel: string, href: string) => {
      let el = document.querySelector<HTMLLinkElement>(`link[rel="${rel}"]`)
      if (!el) {
        el = document.createElement('link')
        el.setAttribute('rel', rel)
        document.head.appendChild(el)
      }
      el.setAttribute('href', href)
    }

    // ── Primary meta ───────────────────────────────────────────────────────
    setMeta('meta[name="description"]', 'content', description)
    setMeta('meta[name="robots"]',      'content', noIndex ? 'noindex,nofollow' : 'index,follow,max-snippet:-1,max-image-preview:large,max-video-preview:-1')
    if (keywords) setMeta('meta[name="keywords"]', 'content', keywords)

    // ── Open Graph ─────────────────────────────────────────────────────────
    setMeta('meta[property="og:site_name"]',  'content', SITE_NAME)
    setMeta('meta[property="og:locale"]',     'content', ogLocale)
    setMeta('meta[property="og:title"]',      'content', fullTitle)
    setMeta('meta[property="og:description"]','content', description)
    setMeta('meta[property="og:image"]',      'content', ogImage)
    setMeta('meta[property="og:type"]',       'content', ogType)
    setMeta('meta[property="og:url"]',        'content', canonUrl)

    // ── Twitter / X ────────────────────────────────────────────────────────
    setMeta('meta[name="twitter:card"]',        'content', 'summary_large_image')
    setMeta('meta[name="twitter:title"]',       'content', fullTitle)
    setMeta('meta[name="twitter:description"]', 'content', description)
    setMeta('meta[name="twitter:image"]',       'content', ogImage)

    // ── Canonical ──────────────────────────────────────────────────────────
    setLink('canonical', canonUrl)

    // ── Hreflang alternates ────────────────────────────────────────────────
    // Remove stale hreflang links injected by a previous render
    document.querySelectorAll<HTMLLinkElement>('link[rel="alternate"][hreflang]')
      .forEach(el => el.remove())

    if (hreflangs?.length) {
      hreflangs.forEach(({ hreflang, href }) => {
        const el = document.createElement('link')
        el.setAttribute('rel', 'alternate')
        el.setAttribute('hreflang', hreflang)
        el.setAttribute('href', href)
        document.head.appendChild(el)
      })
    }
  }, [title, description, canonical, ogImage, ogType, lang, hreflangs, keywords, noIndex])
}
