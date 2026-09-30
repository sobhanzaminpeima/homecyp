<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ArrowLeft } from 'lucide-vue-next'
import EcLogo from '@/components/EcLogo.vue'
import { useAuthStore } from '@/stores/auth'
import { useSmartBack } from '@/composables/useSmartBack'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const goBack = useSmartBack('/home')

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function submit() {
  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.login(email.value, password.value)
    const redirect = (route.query.redirect as string) || '/home'
    router.replace(redirect)
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Login failed. Please check your credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col p-6 safe-x relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-72 ec-gradient rounded-b-[3rem]"></div>
    <button class="relative z-10 mb-8 ec-icon-button text-white" aria-label="Back" @click="goBack"><ArrowLeft :size="20" /></button>

    <div class="relative z-10 flex flex-col items-center gap-3 mb-7 text-white">
      <div class="h-20 w-20 rounded-3xl bg-white flex items-center justify-center shadow-xl shadow-black/10"><EcLogo :size="58" /></div>
      <h1 class="font-bold text-xl">{{ t('auth.loginCta') }}</h1>
    </div>

    <form class="relative z-10 ec-card p-5 flex flex-col gap-3" @submit.prevent="submit">
      <input
        v-model="email"
        type="email"
        required
        :placeholder="t('auth.email')"
        class="ec-card px-4 py-3 text-sm outline-none"
      />
      <input
        v-model="password"
        type="password"
        required
        :placeholder="t('auth.password')"
        class="ec-card px-4 py-3 text-sm outline-none"
      />

      <p v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</p>

      <RouterLink to="/forgot-password" class="text-xs text-right font-semibold" style="color: var(--color-teal)">
        {{ t('auth.forgotPassword') }}
      </RouterLink>

      <button type="submit" class="ec-btn-primary py-3.5 mt-2" :disabled="loading">{{ t('auth.login') }}</button>
    </form>

    <p class="relative z-10 text-center text-sm mt-6" style="color: var(--color-muted)">
      {{ t('auth.noAccount') }}
      <RouterLink to="/register" class="font-semibold" style="color: var(--color-teal)">{{ t('auth.register') }}</RouterLink>
    </p>
  </div>
</template>
