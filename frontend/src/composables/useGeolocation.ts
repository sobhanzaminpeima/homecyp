import { ref } from 'vue'

export interface Coords {
  lat: number
  lng: number
}

const coords = ref<Coords | null>(null)
const error = ref<string | null>(null)
const loading = ref(false)

export function useGeolocation() {
  function request(): Promise<Coords | null> {
    if (!navigator.geolocation) {
      error.value = 'unsupported'
      return Promise.resolve(null)
    }

    loading.value = true
    error.value = null

    return new Promise((resolve) => {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          coords.value = { lat: position.coords.latitude, lng: position.coords.longitude }
          loading.value = false
          resolve(coords.value)
        },
        (err) => {
          error.value = err.code === err.PERMISSION_DENIED ? 'denied' : 'unavailable'
          loading.value = false
          resolve(null)
        },
        { enableHighAccuracy: false, timeout: 8000, maximumAge: 5 * 60 * 1000 },
      )
    })
  }

  return { coords, error, loading, request }
}
