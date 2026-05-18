import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { lazy, Suspense, Component, type ReactNode } from 'react'
import RequireAuth from '@/components/admin/RequireAuth'
import AdminLayout from '@/components/admin/AdminLayout'

// Public pages
const LandingPage  = lazy(() => import('@/pages/public/LandingPage'))
const ExplorePage  = lazy(() => import('@/pages/public/ExplorePage'))
const CountryPage  = lazy(() => import('@/pages/public/CountryPage'))
const OutletPage   = lazy(() => import('@/pages/public/OutletPage'))

// Admin pages
const LoginPage      = lazy(() => import('@/pages/admin/LoginPage'))
const DashboardPage  = lazy(() => import('@/pages/admin/DashboardPage'))
const OutletsPage    = lazy(() => import('@/pages/admin/OutletsPage'))
const OutletFormPage = lazy(() => import('@/pages/admin/OutletFormPage'))
const ImportPage     = lazy(() => import('@/pages/admin/ImportPage'))

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: 1, staleTime: 1000 * 60 * 5 },
  },
})

// ── Error boundary ─────────────────────────────────────────────────────────
class ErrorBoundary extends Component<{ children: ReactNode }, { error: Error | null }> {
  state = { error: null }
  static getDerivedStateFromError(error: Error) { return { error } }
  render() {
    if (this.state.error) {
      return (
        <div className="min-h-screen bg-slate-950 flex items-center justify-center text-slate-300 p-8">
          <div className="max-w-md text-center">
            <p className="text-4xl mb-4">⚠️</p>
            <h1 className="text-xl font-bold text-white mb-2">Something went wrong</h1>
            <p className="text-sm text-slate-400 mb-4 font-mono">{(this.state.error as Error).message}</p>
            <button
              onClick={() => window.location.reload()}
              className="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm transition-colors"
            >
              Reload page
            </button>
          </div>
        </div>
      )
    }
    return this.props.children
  }
}

// ── Loaders ────────────────────────────────────────────────────────────────
function PageLoader() {
  return (
    <div className="flex items-center justify-center h-screen bg-slate-950">
      <div className="w-8 h-8 border-2 border-blue-500 border-t-transparent rounded-full animate-spin" />
    </div>
  )
}

function AdminRoutes() {
  return (
    <RequireAuth>
      <AdminLayout>
        <Suspense fallback={<PageLoader />}>
          <Routes>
            <Route index                   element={<DashboardPage />} />
            <Route path="outlets"          element={<OutletsPage />} />
            <Route path="outlets/new"      element={<OutletFormPage />} />
            <Route path="outlets/:id/edit" element={<OutletFormPage />} />
            <Route path="import"           element={<ImportPage />} />
          </Routes>
        </Suspense>
      </AdminLayout>
    </RequireAuth>
  )
}

export default function App() {
  return (
    <ErrorBoundary>
      <QueryClientProvider client={queryClient}>
        <BrowserRouter
          future={{
            v7_startTransition: true,
            v7_relativeSplatPath: true,
          }}
        >
          <Suspense fallback={<PageLoader />}>
            <Routes>
              {/* Public */}
              <Route path="/"                element={<LandingPage />} />
              <Route path="/explore"         element={<ExplorePage />} />
              <Route path="/countries/:code" element={<CountryPage />} />
              <Route path="/outlets/:slug"   element={<OutletPage />} />

              {/* Admin auth */}
              <Route path="/admin/login"     element={<LoginPage />} />

              {/* Admin panel (protected) */}
              <Route path="/admin/*"         element={<AdminRoutes />} />
            </Routes>
          </Suspense>
        </BrowserRouter>
      </QueryClientProvider>
    </ErrorBoundary>
  )
}
