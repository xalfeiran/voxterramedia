import { useEffect } from 'react'
import { Navigate, useLocation } from 'react-router-dom'
import { useAuthStore } from '@/stores/authStore'

interface Props { children: React.ReactNode }

export default function RequireAuth({ children }: Props) {
  const { token, user, fetchMe } = useAuthStore()
  const location = useLocation()

  useEffect(() => {
    if (token && !user) fetchMe()
  }, [token, user, fetchMe])

  if (!token) {
    return <Navigate to="/admin/login" state={{ from: location }} replace />
  }

  return <>{children}</>
}
