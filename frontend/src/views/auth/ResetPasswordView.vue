<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ArrowLeft } from 'lucide-vue-next'
import EcLogo from '@/components/EcLogo.vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const email = ref((route.query.email as string) || '')
const token = ref((route.query.token as string) || '')
const password = ref('')
const passwordConfirmation = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function submit() {
  errorMessage.value = ''

  if (password.value !== passwordConfirmation.value) {
    errorMessage.value = t('auth.confirmPassword')
    return
  }

  loading.value = true
  try {
    await authStore.resetPassword({
      token: token.value,
      email: email.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    })
    toast.success(t('auth.resetSuccess'))
    router.replace('/login')
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'This reset link is invalid or has expired.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col p-6 safe-x">
    <button class="mb-6" @click="router.push('/login')"><ArrowLeft :size="20" /></button>

    <div class="flex flex-col items-center gap-2 mb-8">
      <EcLogo :size="56" />
      <h1 class="font-bold text-lg" style="color: var(--color-navy)">{{ t('auth.resetPasswordCta') }}</h1>
    </div>

    <form class="flex flex-col gap-3" @submit.prevent="submit">
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
        minlength="8"
        :placeholder="t('auth.newPassword')"
        class="ec-card px-4 py-3 text-sm outline-none"
      />
      <input
        v-model="passwordConfirmation"
        type="password"
        required
        minlength="8"
        :placeholder="t('auth.confirmPassword')"
        class="ec-card px-4 py-3 text-sm outline-none"
      />

      <p v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</p>

      <button type="submit" class="ec-btn-primary py-3 mt-2" :disabled="loading">{{ t('auth.resetPassword') }}</button>
    </form>
  </div>
</template>
