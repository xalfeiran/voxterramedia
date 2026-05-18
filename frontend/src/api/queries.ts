import { useQuery } from '@tanstack/react-query'
import client from './client'
import type { Country, MapOutlet, MediaOutlet, PaginatedResponse, Stats } from '@/types'

// ── Countries ──────────────────────────────────────────────────────────────
export const useCountries = () =>
  useQuery({
    queryKey: ['countries'],
    queryFn: async () => {
      const { data } = await client.get<{ data: Country[] }>('/countries')
      return data.data
    },
    staleTime: 1000 * 60 * 10,
  })

export const useCountry = (code: string) =>
  useQuery({
    queryKey: ['country', code],
    queryFn: async () => {
      const { data } = await client.get<{ data: Country }>(`/countries/${code}`)
      return data.data
    },
    enabled: !!code,
  })

// ── Map ────────────────────────────────────────────────────────────────────
export const useMapOutlets = () =>
  useQuery({
    queryKey: ['media-outlets', 'map'],
    queryFn: async () => {
      const { data } = await client.get<{ data: MapOutlet[] }>('/media-outlets/map')
      return data.data
    },
    staleTime: 1000 * 60 * 5,
  })

// ── Outlets ────────────────────────────────────────────────────────────────
export const useMediaOutlets = (params: Record<string, string | number>) =>
  useQuery({
    queryKey: ['media-outlets', params],
    queryFn: async () => {
      const { data } = await client.get<PaginatedResponse<MediaOutlet>>('/media-outlets', { params })
      return data
    },
  })

export const useMediaOutlet = (slug: string) =>
  useQuery({
    queryKey: ['media-outlet', slug],
    queryFn: async () => {
      const { data } = await client.get<{ data: MediaOutlet }>(`/media-outlets/${slug}`)
      return data.data
    },
    enabled: !!slug,
  })

// ── Stats ──────────────────────────────────────────────────────────────────
export const useStats = () =>
  useQuery({
    queryKey: ['stats'],
    queryFn: async () => {
      const { data } = await client.get<{ data: Stats }>('/stats')
      return data.data
    },
    staleTime: 1000 * 60 * 15,
  })
