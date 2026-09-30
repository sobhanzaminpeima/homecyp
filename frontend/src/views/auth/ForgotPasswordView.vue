<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { ArrowLeft } from 'lucide-vue-next'
import EcLogo from '@/components/EcLogo.vue'
import { useAuthStore } from '@/stores/auth'
import { useSmartBack } from '@/composables/useSmartBack'

const { t } = useI18n()
const authStore = useAuthStore()
const goBack = useSmartBack('/login')

const email = ref('')
const loading = ref(false)
const errorMessage = ref('')
const sent = ref(false)

async function submit() {
  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.forgotPassword(email.value)
    sent.value = true
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Something went wrong. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col p-6 safe-x">
    <button class="mb-6" @click="goBack"><ArrowLeft :size="20" /></button>

    <div class="flex flex-col items-center gap-2 mb-8">
      <EcLogo :size="56" />
      <h1 class="font-bold text-lg" style="color: var(--color-navy)">{{ t('auth.forgotPasswordCta') }}</h1>
    </div>

    <div v-if="sent" class="ec-card p-4 text-sm text-center" style="color: var(--color-teal-dark)">
      {{ t('auth.resetLinkSent') }}
    </div>

    <form v-else class="flex flex-col gap-3" @submit.prevent="submit">
      <p class="text-sm mb-1" style="color: var(--color-muted)">{{ t('auth.forgotPasswordHint') }}</p>
      <input
        v-model="email"
        type="email"
        required
        :placeholder="t('auth.email')"
        class="ec-card px-4 py-3 text-sm outline-none"
      />

      <p v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</p>

      <button type="submit" class="ec-btn-primary py-3 mt-2" :disabled="loading">{{ t('auth.sendResetLink') }}</button>
    </form>

    <p class="text-center text-sm mt-6" style="color: var(--color-muted)">
      <RouterLink to="/login" class="font-semibold" style="color: var(--color-teal)">{{ t('auth.backToLogin') }}</RouterLink>
    </p>
  </div>
</template>
