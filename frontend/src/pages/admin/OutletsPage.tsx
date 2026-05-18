import { useState, useMemo } from 'react'
import { Link } from 'react-router-dom'
import {
  Search, Plus, Pencil, Trash2, Star, StarOff,
  ChevronLeft, ChevronRight, ExternalLink,
} from 'lucide-react'
import { useMediaOutlets } from '@/api/queries'
import { useDeleteOutlet, useToggleFeatured } from '@/api/mutations'
import { TYPE_LABELS, COUNTRY_COLORS, cn } from '@/lib/utils'
import type { CountryCode, MediaType } from '@/types'

export default function OutletsPage() {
  const [search, setSearch]   = useState('')
  const [country, setCountry] = useState('')
  const [type, setType]       = useState('')
  const [page, setPage]       = useState(1)
  const [confirm, setConfirm] = useState<number | null>(null)

  const params = useMemo(() => ({
    ...(search  ? { search } : {}),
    ...(country ? { country } : {}),
    ...(type    ? { type }    : {}),
    page,
    per_page: 20,
  }), [search, country, type, page])

  const { data, isLoading } = useMediaOutlets(params)
  const deleteOutlet  = useDeleteOutlet()
  const toggleFeatured = useToggleFeatured()

  const outlets  = data?.data ?? []
  const meta     = data?.meta

  const handleDelete = async (id: number) => {
    await deleteOutlet.mutateAsync(id)
    setConfirm(null)
  }

  return (
    <div className="p-8 max-w-7xl">
      {/* Header */}
      <div className="flex items-center justify-between mb-6">
        <div>
          <h1 className="text-2xl font-bold text-white">Media Outlets</h1>
          <p className="text-sm text-slate-400 mt-0.5">{meta?.total ?? '…'} total outlets</p>
        </div>
        <Link
          to="/admin/outlets/new"
          className="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors"
        >
          <Plus className="w-4 h-4" />New outlet
        </Link>
      </div>

      {/* Filters */}
      <div className="flex flex-wrap gap-3 mb-6">
        <div className="relative">
          <Search className="absolute left-2.5 top-2.5 w-4 h-4 text-slate-500" />
          <input
            type="text"
            placeholder="Search…"
            value={search}
            onChange={e => { setSearch(e.target.value); setPage(1) }}
            className="bg-slate-900 border border-slate-700 rounded-lg pl-8 pr-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:border-blue-500 w-52"
          />
        </div>

        <select
          value={country}
          onChange={e => { setCountry(e.target.value); setPage(1) }}
          className="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-blue-500"
        >
          <option value="">All countries</option>
          <option value="CA">🇨🇦 Canada</option>
          <option value="US">🇺🇸 USA</option>
          <option value="MX">🇲🇽 México</option>
        </select>

        <select
          value={type}
          onChange={e => { setType(e.target.value); setPage(1) }}
          className="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:border-blue-500"
        >
          <option value="">All types</option>
          {(['national','newspaper','digital','tv','radio','magazine'] as MediaType[]).map(t => (
            <option key={t} value={t}>{TYPE_LABELS[t]}</option>
          ))}
        </select>
      </div>

      {/* Table */}
      <div className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <table className="w-full text-sm">
          <thead>
            <tr className="border-b border-slate-800 text-xs text-slate-500 uppercase tracking-wide">
              <th className="text-left px-4 py-3 font-medium">Outlet</th>
              <th className="text-left px-4 py-3 font-medium hidden md:table-cell">Location</th>
              <th className="text-left px-4 py-3 font-medium hidden lg:table-cell">Type</th>
              <th className="text-left px-4 py-3 font-medium hidden lg:table-cell">Language</th>
              <th className="text-center px-4 py-3 font-medium">Featured</th>
              <th className="text-right px-4 py-3 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody>
            {isLoading && Array.from({ length: 8 }).map((_, i) => (
              <tr key={i} className="border-b border-slate-800/50">
                <td className="px-4 py-3"><div className="h-4 bg-slate-800 rounded w-3/4 animate-pulse" /></td>
                <td className="px-4 py-3 hidden md:table-cell"><div className="h-4 bg-slate-800 rounded w-1/2 animate-pulse" /></td>
                <td className="px-4 py-3 hidden lg:table-cell"><div className="h-4 bg-slate-800 rounded w-20 animate-pulse" /></td>
                <td className="px-4 py-3 hidden lg:table-cell"><div className="h-4 bg-slate-800 rounded w-10 animate-pulse" /></td>
                <td className="px-4 py-3" />
                <td className="px-4 py-3" />
              </tr>
            ))}

            {!isLoading && outlets.map(outlet => {
              const color = COUNTRY_COLORS[outlet.country.code as CountryCode] ?? '#888'
              return (
                <tr key={outlet.id} className="border-b border-slate-800/50 hover:bg-slate-800/40 transition-colors group">
                  <td className="px-4 py-3">
                    <div className="flex items-center gap-2.5">
                      <div className="w-1.5 h-6 rounded-full flex-none" style={{ backgroundColor: color }} />
                      <div>
                        <p className="font-medium text-white">{outlet.name}</p>
                        <a
                          href={outlet.url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="text-xs text-slate-500 hover:text-blue-400 flex items-center gap-0.5 transition-colors"
                        >
                          {outlet.url.replace(/^https?:\/\//, '').slice(0, 30)}
                          <ExternalLink className="w-2.5 h-2.5" />
                        </a>
                      </div>
                    </div>
                  </td>
                  <td className="px-4 py-3 hidden md:table-cell text-slate-400">
                    {outlet.city.name}, {outlet.region.name}
                  </td>
                  <td className="px-4 py-3 hidden lg:table-cell">
                    <span className="bg-slate-800 text-slate-300 text-xs px-2 py-0.5 rounded">
                      {TYPE_LABELS[outlet.type as MediaType]}
                    </span>
                  </td>
                  <td className="px-4 py-3 hidden lg:table-cell text-slate-400 uppercase text-xs">
                    {outlet.language}
                  </td>
                  <td className="px-4 py-3 text-center">
                    <button
                      onClick={() => toggleFeatured.mutate(outlet.id)}
                      className="transition-colors"
                      title={outlet.is_featured ? 'Remove featured' : 'Mark featured'}
                    >
                      {outlet.is_featured
                        ? <Star className="w-4 h-4 text-amber-400 fill-amber-400 mx-auto" />
                        : <StarOff className="w-4 h-4 text-slate-600 mx-auto hover:text-amber-400" />
                      }
                    </button>
                  </td>
                  <td className="px-4 py-3">
                    <div className="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <Link
                        to={`/admin/outlets/${outlet.id}/edit`}
                        className="p-1.5 rounded hover:bg-slate-700 text-slate-400 hover:text-white transition-colors"
                      >
                        <Pencil className="w-3.5 h-3.5" />
                      </Link>
                      {confirm === outlet.id ? (
                        <div className="flex items-center gap-1">
                          <button
                            onClick={() => handleDelete(outlet.id)}
                            className="text-xs bg-red-600 hover:bg-red-500 text-white px-2 py-1 rounded transition-colors"
                          >
                            Confirm
                          </button>
                          <button
                            onClick={() => setConfirm(null)}
                            className="text-xs text-slate-400 hover:text-white px-1 py-1"
                          >
                            Cancel
                          </button>
                        </div>
                      ) : (
                        <button
                          onClick={() => setConfirm(outlet.id)}
                          className="p-1.5 rounded hover:bg-red-500/10 text-slate-400 hover:text-red-400 transition-colors"
                        >
                          <Trash2 className="w-3.5 h-3.5" />
                        </button>
                      )}
                    </div>
                  </td>
                </tr>
              )
            })}

            {!isLoading && outlets.length === 0 && (
              <tr>
                <td colSpan={6} className="px-4 py-12 text-center text-slate-500">
                  No outlets match your filters.
                </td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-between mt-4">
          <p className="text-sm text-slate-500">
            Showing {meta.from}–{meta.to} of {meta.total}
          </p>
          <div className="flex items-center gap-2">
            <button
              onClick={() => setPage(p => p - 1)}
              disabled={page === 1}
              className="p-1.5 rounded border border-slate-700 text-slate-400 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <ChevronLeft className="w-4 h-4" />
            </button>
            <span className="text-sm text-slate-400">Page {page} / {meta.last_page}</span>
            <button
              onClick={() => setPage(p => p + 1)}
              disabled={page === meta.last_page}
              className="p-1.5 rounded border border-slate-700 text-slate-400 hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed"
            >
              <ChevronRight className="w-4 h-4" />
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
