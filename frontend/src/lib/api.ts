import axios from 'axios'
import { useToast } from '@/composables/useToast'

export const TOKEN_KEY = 'cg_token'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? '/api/v1',
  headers: {
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401 && localStorage.getItem(TOKEN_KEY)) {
      // Import lazily to avoid a circular dependency (auth store / router both import this module).
      const [{ useAuthStore }, { default: router }] = await Promise.all([
        import('@/stores/auth'),
        import('@/router'),
      ])
      const authStore = useAuthStore()
      authStore.clearSession()
      useToast().error('Your session has expired. Please sign in again.')

      if (router.currentRoute.value.meta.requiresAuth || router.currentRoute.value.meta.requiresAdmin) {
        router.push({ name: router.currentRoute.value.meta.requiresAdmin ? 'admin-login' : 'login' })
      }
    } else if (error.response?.status >= 500) {
      useToast().error('Something went wrong on our end. Please try again shortly.')
    } else if (!error.response) {
      useToast().error('Network error — check your connection and try again.')
    }

    return Promise.reject(error)
  },
)

export default api
