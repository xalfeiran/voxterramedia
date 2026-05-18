import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'
import type { CountryCode, MediaType } from '@/types'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export const COUNTRY_COLORS: Record<CountryCode, string> = {
  CA: '#ef4444',
  US: '#3b82f6',
  MX: '#10b981',
}

export const COUNTRY_NAMES: Record<CountryCode, string> = {
  CA: 'Canada',
  US: 'United States',
  MX: 'México',
}

export const TYPE_LABELS: Record<MediaType, string> = {
  national:  'National',
  newspaper: 'Newspaper',
  digital:   'Digital',
  tv:        'TV',
  radio:     'Radio',
  magazine:  'Magazine',
}

export const TYPE_COLORS: Record<MediaType, string> = {
  national:  'bg-purple-500/20 text-purple-300',
  newspaper: 'bg-blue-500/20 text-blue-300',
  digital:   'bg-cyan-500/20 text-cyan-300',
  tv:        'bg-orange-500/20 text-orange-300',
  radio:     'bg-yellow-500/20 text-yellow-300',
  magazine:  'bg-pink-500/20 text-pink-300',
}

export function formatUrl(url: string) {
  return url.replace(/^https?:\/\//, '').replace(/\/$/, '')
}
