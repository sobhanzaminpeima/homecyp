import { defineStore } from 'pinia'
import type { City } from '@/types'

const STORAGE_KEY = 'ec_city'

export const useCityStore = defineStore('city', {
  state: () => ({
    city: loadCity(),
  }),
  getters: {
    hasCity: (state) => !!state.city,
  },
  actions: {
    setCity(city: City) {
      this.city = city
      localStorage.setItem(STORAGE_KEY, JSON.stringify(city))
    },
    clearCity() {
      this.city = null
      localStorage.removeItem(STORAGE_KEY)
    },
    /**
     * The selected city is cached in localStorage so it survives reloads, but
     * that snapshot goes stale whenever the city record itself changes server-side
     * (e.g. a translated name added later) — refresh it from a freshly-fetched list.
     */
    syncFromList(cities: City[]) {
      if (!this.city) return
      const fresh = cities.find((c) => c.id === this.city!.id)
      if (fresh && JSON.stringify(fresh) !== JSON.stringify(this.city)) {
        this.setCity(fresh)
      }
    },
  },
})

function loadCity(): City | null {
  try {
    const raw = localStorage.getItem(STORAGE_KEY)
    return raw ? (JSON.parse(raw) as City) : null
  } catch {
    return null
  }
}
