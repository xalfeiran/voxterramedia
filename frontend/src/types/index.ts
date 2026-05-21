export type CountryCode = 'CA' | 'US' | 'MX'
export type MediaType = 'national' | 'newspaper' | 'digital' | 'tv' | 'radio' | 'magazine'
export type Language =
  | 'en' | 'es' | 'fr' | 'de' | 'pt' | 'ar' | 'zh' | 'ja' | 'ko' | 'ru'
  | 'hi' | 'it' | 'nl' | 'pl' | 'sv' | 'tr' | 'fa' | 'he' | 'id' | 'ms'
  | 'th' | 'vi' | 'uk' | 'ro' | 'hu'

export interface Country {
  code: string
  name: string
  name_es: string
  slug: string
  flag_emoji: string
  latitude: number
  longitude: number
  default_zoom: number
  regions_count?: number
  outlets_count?: number
  regions?: Region[]
}

export interface Region {
  id: number
  name: string
  name_es: string
  slug: string
  cities?: City[]
}

export interface City {
  id: number
  name: string
  slug: string
}

export interface MediaOutlet {
  id: number
  name: string
  slug: string
  url: string
  rss_url?: string
  has_rss: boolean
  type: MediaType
  language: Language
  description?: string
  logo_url?: string
  founded_year?: number
  is_active: boolean
  is_featured: boolean
  latitude: number
  longitude: number
  city: City
  region: Region
  country: Pick<Country, 'code' | 'name' | 'name_es' | 'flag_emoji'>
  created_at: string
  updated_at: string
}

export interface MapOutlet {
  id: number
  name: string
  slug: string
  url: string
  type: MediaType
  language: Language
  is_featured: boolean
  has_rss: boolean
  lat: number
  lon: number
  country: string   // worldwide — not limited to CA/US/MX
  city: string
  region: string
}

export interface PaginatedResponse<T> {
  data: T[]
  links: { first: string; last: string; prev: string | null; next: string | null }
  meta: {
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
    filters_applied?: Record<string, string>
  }
}

export interface Stats {
  total: number
  by_country: { code: string; name: string; name_es: string; emoji: string; outlets: number }[]
  by_type: Record<MediaType, number>
  by_language: Partial<Record<Language, number>>
}

export interface FilterState {
  country: CountryCode | 'all'
  type: MediaType | ''
  language: Language | ''
  search: string
  city: string
  hasRss: boolean
}
