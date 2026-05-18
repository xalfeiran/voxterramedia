import { create } from 'zustand'
import type { BotLocation } from '@/lib/botData'
import { BOTS, LOCATIONS, STATUSES } from '@/lib/botData'

export interface BotState {
  id: number
  name: string
  emoji: string
  color: string
  location: BotLocation
  status: string
  jumping: boolean
}

interface BotStore {
  bots: BotState[]
  activeBotId: number | null
  setJumping: (id: number, jumping: boolean) => void
  land: (id: number, location: BotLocation, status: string) => void
  setActiveBot: (id: number | null) => void
}

/** Build initial bot states — each starts at a well-spread location */
function initBots(): BotState[] {
  return BOTS.map((b, i) => ({
    ...b,
    location: LOCATIONS[(i * 5) % LOCATIONS.length],
    status:   STATUSES[Math.floor(Math.random() * STATUSES.length)],
    jumping:  false,
  }))
}

export const useBotStore = create<BotStore>(set => ({
  bots: initBots(),
  activeBotId: null,

  setJumping: (id, jumping) =>
    set(state => ({
      bots: state.bots.map(b => b.id === id ? { ...b, jumping } : b),
    })),

  land: (id, location, status) =>
    set(state => ({
      bots: state.bots.map(b =>
        b.id === id ? { ...b, location, status, jumping: false } : b
      ),
    })),

  setActiveBot: (id) => set({ activeBotId: id }),
}))
