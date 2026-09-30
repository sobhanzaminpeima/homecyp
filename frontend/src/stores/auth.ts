import { defineStore } from 'pinia'
import api, { TOKEN_KEY } from '@/lib/api'
import type { AccountType, User } from '@/types'

const USER_KEY = 'ec_user'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: loadUser(),
    token: localStorage.getItem(TOKEN_KEY),
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.account_type === 'admin',
    isBusinessOwner: (state) => state.user?.account_type === 'business',
  },
  actions: {
    async login(email: string, password: string) {
      const { data } = await api.post('/auth/login', { email, password })
      this.setSession(data.user, data.token)
      return data.user as User
    },
    async register(payload: { name: string; email: string; password: string; phone?: string; account_type: AccountType }) {
      const { data } = await api.post('/auth/register', payload)
      this.setSession(data.user, data.token)
      return data.user as User
    },
    async logout() {
      try {
        await api.post('/auth/logout')
      } catch {
        // ignore network errors on logout, clear local session regardless
      }
      this.clearSession()
    },
    async forgotPassword(email: string) {
      const { data } = await api.post('/auth/forgot-password', { email })
      return data.message as string
    },
    async resetPassword(payload: { token: string; email: string; password: string; password_confirmation: string }) {
      const { data } = await api.post('/auth/reset-password', payload)
      return data.message as string
    },
    async updateProfile(payload: { name?: string; phone?: string | null; password?: string }) {
      const { data } = await api.put('/auth/profile', payload)
      this.user = data.data
      localStorage.setItem(USER_KEY, JSON.stringify(data.data))
      return data.data as User
    },
    setSession(user: User, token: string) {
      this.user = user
      this.token = token
      localStorage.setItem(TOKEN_KEY, token)
      localStorage.setItem(USER_KEY, JSON.stringify(user))
    },
    clearSession() {
      this.user = null
      this.token = null
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(USER_KEY)
    },
  },
})

function loadUser(): User | null {
  try {
    const raw = localStorage.getItem(USER_KEY)
    return raw ? (JSON.parse(raw) as User) : null
  } catch {
    return null
  }
}
