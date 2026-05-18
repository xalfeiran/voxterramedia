import axios from 'axios'

const client = axios.create({
  baseURL: '/api/v1',
  headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
  withCredentials: true,
})

// Attach Sanctum token if present
client.interceptors.request.use((config) => {
  const token = localStorage.getItem('nc_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// Redirect to login on 401
client.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('nc_token')
      window.location.href = '/admin/login'
    }
    return Promise.reject(err)
  }
)

export default client
