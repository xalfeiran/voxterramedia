import { create } from 'zustand'
import type { CountryCode, FilterState, Language, MediaType } from '@/types'

interface FilterStore extends FilterState {
  setCountry: (country: CountryCode | 'all') => void
  setType: (type: MediaType | '') => void
  setLanguage: (language: Language | '') => void
  setSearch: (search: string) => void
  reset: () => void
}

const DEFAULT: FilterState = {
  country: 'all',
  type: '',
  language: '',
  search: '',
}

export const useFilterStore = create<FilterStore>((set) => ({
  ...DEFAULT,
  setCountry:  (country)  => set({ country }),
  setType:     (type)     => set({ type }),
  setLanguage: (language) => set({ language }),
  setSearch:   (search)   => set({ search }),
  reset:       ()         => set(DEFAULT),
}))
