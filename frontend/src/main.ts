import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { registerSW } from 'virtual:pwa-register'

import App from './App.vue'
import router from './router'
import i18n from './i18n'
import './assets/style.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)

app.mount('#app')

// Auto-apply new versions as soon as they're available, so users never have
// to manually close and reopen the app to see updates.
if ('serviceWorker' in navigator) {
  const updateSW = registerSW({
    immediate: true,
    onNeedRefresh() {
      updateSW(true)
    },
  })

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      updateSW()
    }
  })
}
