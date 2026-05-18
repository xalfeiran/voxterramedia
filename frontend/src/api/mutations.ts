import { useMutation, useQueryClient } from '@tanstack/react-query'
import client from './client'
import type { MediaOutlet } from '@/types'

// ── Media Outlets ──────────────────────────────────────────────────────────

export const useCreateOutlet = () => {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (data: Partial<MediaOutlet>) =>
      client.post('/admin/media-outlets', data).then(r => r.data.data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['media-outlets'] })
      qc.invalidateQueries({ queryKey: ['stats'] })
    },
  })
}

export const useUpdateOutlet = () => {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<MediaOutlet> }) =>
      client.put(`/admin/media-outlets/${id}`, data).then(r => r.data.data),
    onSuccess: (_data, { id }) => {
      qc.invalidateQueries({ queryKey: ['media-outlets'] })
      qc.invalidateQueries({ queryKey: ['media-outlet'] })
      qc.invalidateQueries({ queryKey: ['stats'] })
    },
  })
}

export const useDeleteOutlet = () => {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) =>
      client.delete(`/admin/media-outlets/${id}`).then(r => r.data),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ['media-outlets'] })
      qc.invalidateQueries({ queryKey: ['stats'] })
    },
  })
}

export const useToggleFeatured = () => {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) =>
      client.post(`/admin/media-outlets/${id}/toggle-featured`).then(r => r.data.data),
    onMutate: async (id) => {
      await qc.cancelQueries({ queryKey: ['media-outlets'] })
      const prev = qc.getQueryData(['media-outlets'])
      return { prev }
    },
    onError: (_err, _id, ctx) => {
      if (ctx?.prev) qc.setQueryData(['media-outlets'], ctx.prev)
    },
    onSettled: () => qc.invalidateQueries({ queryKey: ['media-outlets'] }),
  })
}
