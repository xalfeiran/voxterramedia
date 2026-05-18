import {
  BarChart, Bar, XAxis, YAxis, Tooltip, ResponsiveContainer,
  PieChart, Pie, Cell, Legend,
} from 'recharts'
import { Newspaper, Globe, Tv, Radio, Star, TrendingUp } from 'lucide-react'
import { useStats } from '@/api/queries'
import { COUNTRY_COLORS, TYPE_LABELS } from '@/lib/utils'
import type { MediaType } from '@/types'

const TYPE_PIE_COLORS: Record<string, string> = {
  national:  '#a855f7',
  newspaper: '#3b82f6',
  digital:   '#06b6d4',
  tv:        '#f97316',
  radio:     '#eab308',
  magazine:  '#ec4899',
}

function StatCard({ label, value, icon: Icon, color }: {
  label: string; value: number | string; icon: React.ElementType; color: string
}) {
  return (
    <div className="bg-slate-900 border border-slate-800 rounded-xl p-5 flex items-center gap-4">
      <div className={`w-10 h-10 rounded-lg flex items-center justify-center flex-none ${color}`}>
        <Icon className="w-5 h-5" />
      </div>
      <div>
        <p className="text-2xl font-bold text-white">{value}</p>
        <p className="text-xs text-slate-400 mt-0.5">{label}</p>
      </div>
    </div>
  )
}

export default function DashboardPage() {
  const { data: stats, isLoading } = useStats()

  if (isLoading) return (
    <div className="p-8 space-y-4">
      {Array.from({ length: 4 }).map((_, i) => (
        <div key={i} className="h-24 bg-slate-900 rounded-xl animate-pulse" />
      ))}
    </div>
  )

  const countryBar = (stats?.by_country ?? []).map(c => ({
    name: c.name,
    outlets: c.outlets,
    fill: COUNTRY_COLORS[c.code] ?? '#888',
  }))

  const typePie = Object.entries(stats?.by_type ?? {}).map(([type, count]) => ({
    name: TYPE_LABELS[type as MediaType] ?? type,
    value: count,
    fill: TYPE_PIE_COLORS[type] ?? '#888',
  }))

  const langData = Object.entries(stats?.by_language ?? {}).map(([lang, count]) => ({
    lang: lang.toUpperCase(),
    count,
  }))

  return (
    <div className="p-8 max-w-6xl space-y-8">
      <div>
        <h1 className="text-2xl font-bold text-white">Dashboard</h1>
        <p className="text-slate-400 text-sm mt-1">Overview of the VoxTerra.media catalog</p>
      </div>

      {/* Stat cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <StatCard label="Total Outlets"  value={stats?.total ?? 0}                    icon={Newspaper}   color="bg-blue-500/10 text-blue-400" />
        <StatCard label="National Media" value={stats?.by_type?.national ?? 0}        icon={Globe}       color="bg-purple-500/10 text-purple-400" />
        <StatCard label="TV Channels"    value={stats?.by_type?.tv ?? 0}              icon={Tv}          color="bg-orange-500/10 text-orange-400" />
        <StatCard label="Digital Native" value={stats?.by_type?.digital ?? 0}         icon={Radio}       color="bg-cyan-500/10 text-cyan-400" />
      </div>

      {/* Charts row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Outlets by country */}
        <div className="bg-slate-900 border border-slate-800 rounded-xl p-5">
          <h2 className="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
            <TrendingUp className="w-4 h-4 text-blue-400" />
            Outlets by Country
          </h2>
          <ResponsiveContainer width="100%" height={200}>
            <BarChart data={countryBar} margin={{ top: 0, right: 0, left: -20, bottom: 0 }}>
              <XAxis dataKey="name" tick={{ fill: '#94a3b8', fontSize: 12 }} axisLine={false} tickLine={false} />
              <YAxis tick={{ fill: '#94a3b8', fontSize: 11 }} axisLine={false} tickLine={false} />
              <Tooltip
                contentStyle={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 8, color: '#e2e8f0' }}
                cursor={{ fill: 'rgba(255,255,255,0.04)' }}
              />
              <Bar dataKey="outlets" radius={[4, 4, 0, 0]}>
                {countryBar.map((entry, i) => (
                  <Cell key={i} fill={entry.fill} />
                ))}
              </Bar>
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Outlets by type */}
        <div className="bg-slate-900 border border-slate-800 rounded-xl p-5">
          <h2 className="text-sm font-semibold text-slate-300 mb-4 flex items-center gap-2">
            <Star className="w-4 h-4 text-amber-400" />
            Outlets by Type
          </h2>
          <ResponsiveContainer width="100%" height={200}>
            <PieChart>
              <Pie
                data={typePie}
                dataKey="value"
                nameKey="name"
                cx="40%"
                cy="50%"
                outerRadius={80}
                stroke="none"
              >
                {typePie.map((entry, i) => (
                  <Cell key={i} fill={entry.fill} />
                ))}
              </Pie>
              <Legend
                layout="vertical"
                align="right"
                verticalAlign="middle"
                iconType="circle"
                iconSize={8}
                formatter={(value) => <span style={{ color: '#94a3b8', fontSize: 12 }}>{value}</span>}
              />
              <Tooltip
                contentStyle={{ background: '#1e293b', border: '1px solid #334155', borderRadius: 8, color: '#e2e8f0' }}
              />
            </PieChart>
          </ResponsiveContainer>
        </div>
      </div>

      {/* Language breakdown */}
      <div className="bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 className="text-sm font-semibold text-slate-300 mb-4">Language Breakdown</h2>
        <div className="flex gap-6">
          {langData.map(({ lang, count }) => {
            const pct = Math.round((count / (stats?.total ?? 1)) * 100)
            const labels: Record<string, string> = { EN: 'English', ES: 'Spanish', FR: 'French' }
            const colors: Record<string, string> = { EN: '#3b82f6', ES: '#10b981', FR: '#f59e0b' }
            return (
              <div key={lang} className="flex-1">
                <div className="flex items-center justify-between mb-1.5">
                  <span className="text-sm text-slate-400">{labels[lang] ?? lang}</span>
                  <span className="text-sm font-semibold text-white">{count}</span>
                </div>
                <div className="w-full bg-slate-800 rounded-full h-2">
                  <div
                    className="h-2 rounded-full transition-all"
                    style={{ width: `${pct}%`, backgroundColor: colors[lang] ?? '#888' }}
                  />
                </div>
                <p className="text-xs text-slate-600 mt-1">{pct}%</p>
              </div>
            )
          })}
        </div>
      </div>
    </div>
  )
}
