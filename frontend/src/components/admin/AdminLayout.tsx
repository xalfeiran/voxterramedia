import { Link, useLocation, useNavigate } from 'react-router-dom'
import {
  Globe, Newspaper, BarChart3, LogOut,
  Users, Upload, Map,
} from 'lucide-react'
import { useAuthStore } from '@/stores/authStore'
import { cn } from '@/lib/utils'

const NAV = [
  { to: '/admin',         icon: BarChart3,  label: 'Dashboard',  exact: true },
  { to: '/admin/outlets', icon: Newspaper,  label: 'Outlets' },
  { to: '/admin/import',  icon: Upload,     label: 'Import CSV' },
  { to: '/admin/users',   icon: Users,      label: 'Users' },
]

interface Props { children: React.ReactNode }

export default function AdminLayout({ children }: Props) {
  const { user, logout } = useAuthStore()
  const { pathname } = useLocation()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/admin/login')
  }

  return (
    <div className="flex h-screen bg-slate-950 text-slate-200 overflow-hidden">
      {/* Sidebar */}
      <aside className="w-56 flex-none bg-slate-900 border-r border-slate-800 flex flex-col">
        <div className="p-5 border-b border-slate-800">
          <Link to="/" className="flex items-center gap-2">
            <Globe className="w-5 h-5 text-blue-400" />
            <div>
              <p className="text-sm font-bold text-white">VoxTerra.media</p>
              <p className="text-xs text-slate-500">Admin panel</p>
            </div>
          </Link>
        </div>

        <nav className="flex-1 p-3 space-y-1">
          {NAV.map(({ to, icon: Icon, label, exact }) => {
            const active = exact ? pathname === to : pathname.startsWith(to)
            return (
              <Link
                key={to}
                to={to}
                className={cn(
                  'flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors',
                  active
                    ? 'bg-blue-600 text-white'
                    : 'text-slate-400 hover:bg-slate-800 hover:text-white'
                )}
              >
                <Icon className="w-4 h-4 flex-none" />
                {label}
              </Link>
            )
          })}

          <div className="pt-2 border-t border-slate-800 mt-2">
            <Link
              to="/explore"
              target="_blank"
              className="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-800 hover:text-white transition-colors"
            >
              <Map className="w-4 h-4 flex-none" />
              View Public Map
            </Link>
          </div>
        </nav>

        <div className="p-3 border-t border-slate-800">
          <div className="flex items-center gap-2.5 px-3 py-2 mb-1">
            <div className="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold text-white flex-none">
              {user?.name?.[0]?.toUpperCase() ?? 'A'}
            </div>
            <div className="min-w-0">
              <p className="text-xs font-medium text-white truncate">{user?.name ?? 'Admin'}</p>
              <p className="text-xs text-slate-500 capitalize">{user?.role ?? 'admin'}</p>
            </div>
          </div>
          <button
            onClick={handleLogout}
            className="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-400 hover:bg-slate-800 hover:text-red-400 transition-colors"
          >
            <LogOut className="w-4 h-4 flex-none" />
            Sign out
          </button>
        </div>
      </aside>

      {/* Main content */}
      <main className="flex-1 overflow-y-auto">
        {children}
      </main>
    </div>
  )
}
