import { useEffect } from 'react'

const BASE_URL  = 'https://voxterra.media'
const SITE_NAME = 'VoxTerra.media'
const DEFAULT_IMAGE = `${BASE_URL}/og-image.png`

interface SeoOptions {
  title       : string
  description : string
  canonical  ?: string
  ogImage    ?: string
  ogType     ?: string
  noIndex    ?: boolean
}

/**
 * Lightweight SEO hook — sets <title>, meta description, OG and Twitter
 * tags, and the canonical URL directly via the DOM.
 *
 * react-helmet-async is used via HelmetProvider in main.tsx; this hook
 * keeps the call-sites minimal with sensible defaults.
 */
export function useSeo({
  title,
  description,
  canonical,
  ogImage  = DEFAULT_IMAGE,
  ogType   = 'website',
  noIndex  = false,
}: SeoOptions) {
  useEffect(() => {
    const fullTitle = title.includes(SITE_NAME) ? title : `${title} — ${SITE_NAME}`

    // Title
    document.title = fullTitle

    // Helper to upsert a <meta> tag
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

    // Helper to upsert a <link> tag
    const setLink = (rel: string, href: string) => {
      let el = document.querySelector<HTMLLinkElement>(`link[rel="${rel}"]`)
      if (!el) {
        el = document.createElement('link')
        el.setAttribute('rel', rel)
        document.head.appendChild(el)
      }
      el.setAttribute('href', href)
    }

    // Primary meta
    setMeta('meta[name="description"]',        'content', description)
    setMeta('meta[name="robots"]',             'content', noIndex ? 'noindex,nofollow' : 'index,follow')

    // Open Graph
    setMeta('meta[property="og:title"]',       'content', fullTitle)
    setMeta('meta[property="og:description"]', 'content', description)
    setMeta('meta[property="og:image"]',       'content', ogImage)
    setMeta('meta[property="og:type"]',        'content', ogType)
    setMeta('meta[property="og:url"]',         'content', canonical ?? window.location.href)

    // Twitter / X
    setMeta('meta[name="twitter:title"]',       'content', fullTitle)
    setMeta('meta[name="twitter:description"]', 'content', description)
    setMeta('meta[name="twitter:image"]',       'content', ogImage)

    // Canonical
    setLink('canonical', canonical ?? window.location.href)
  }, [title, description, canonical, ogImage, ogType, noIndex])
}
