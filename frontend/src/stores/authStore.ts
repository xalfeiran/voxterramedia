import { create } from 'zustand'
import client from '@/api/client'

interface User { id: number; name: string; email: string; role: string }

interface AuthStore {
  user: User | null
  token: string | null
  isLoading: boolean
  login: (email: string, password: string) => Promise<void>
  logout: () => Promise<void>
  fetchMe: () => Promise<void>
}

export const useAuthStore = create<AuthStore>((set) => ({
  user:      null,
  token:     localStorage.getItem('nc_token'),
  isLoading: false,

  login: async (email, password) => {
    set({ isLoading: true })
    const { data } = await client.post('/auth/login', { email, password })
    const token = data.data.token
    localStorage.setItem('nc_token', token)
    set({ user: data.data.user, token, isLoading: false })
  },

  logout: async () => {
    await client.post('/auth/logout').catch(() => {})
    localStorage.removeItem('nc_token')
    set({ user: null, token: null })
  },

  fetchMe: async () => {
    const token = localStorage.getItem('nc_token')
    if (!token) return
    try {
      const { data } = await client.get('/auth/me')
      set({ user: data.data })
    } catch {
      localStorage.removeItem('nc_token')
      set({ user: null, token: null })
    }
  },
}))
