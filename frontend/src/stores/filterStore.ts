import { create } from 'zustand'
import type { CountryCode, FilterState, Language, MediaType } from '@/types'

interface FilterStore extends FilterState {
  setCountry:  (country: CountryCode | 'all') => void
  setType:     (type: MediaType | '') => void
  setLanguage: (language: Language | '') => void
  setSearch:   (search: string) => void
  setCity:     (city: string) => void
  setHasRss:   (hasRss: boolean) => void
  reset:       () => void
}

const DEFAULT: FilterState = {
  country: 'all',
  type: '',
  language: '',
  search: '',
  city: '',
  hasRss: false,
}

export const useFilterStore = create<FilterStore>((set) => ({
  ...DEFAULT,
  setCountry:  (country)  => set({ country }),
  setType:     (type)     => set({ type }),
  setLanguage: (language) => set({ language }),
  setSearch:   (search)   => set({ search }),
  setCity:     (city)     => set({ city }),
  setHasRss:   (hasRss)   => set({ hasRss }),
  reset:       ()         => set(DEFAULT),
}))
