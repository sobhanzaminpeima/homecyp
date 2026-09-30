import { useRouter } from 'vue-router'

/**
 * `router.back()` alone breaks for deep links: if the user opened this page
 * directly (shared URL, browser refresh, PWA launch straight into it), there is
 * no in-app history to go back to, and back() either does nothing or leaves the
 * app entirely. Vue Router's web history stores `history.state.back` for the
 * entry it navigated *from* — if that's null, we didn't get here by clicking
 * around inside the app, so fall back to a sensible screen instead.
 */
export function useSmartBack(fallback: string = '/home') {
  const router = useRouter()

  return function goBack() {
    if (window.history.state && window.history.state.back) {
      router.back()
    } else {
      router.replace(fallback)
    }
  }
}
