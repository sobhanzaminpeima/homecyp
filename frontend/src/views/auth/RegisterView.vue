<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ArrowLeft } from 'lucide-vue-next'
import EcLogo from '@/components/EcLogo.vue'
import { useAuthStore } from '@/stores/auth'
import { useSmartBack } from '@/composables/useSmartBack'
import type { AccountType } from '@/types'

const { t } = useI18n()
const router = useRouter()
const authStore = useAuthStore()
const goBack = useSmartBack('/home')

const name = ref('')
const email = ref('')
const password = ref('')
const phone = ref('')
const accountType = ref<AccountType>('user')
const loading = ref(false)
const errorMessage = ref('')

async function submit() {
  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.register({
      name: name.value,
      email: email.value,
      password: password.value,
      phone: phone.value || undefined,
      account_type: accountType.value,
    })
    router.replace(accountType.value === 'business' ? '/dashboard' : '/home')
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Registration failed. Please check your details.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col p-6 safe-x relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-64 ec-gradient rounded-b-[3rem]"></div>
    <button class="relative z-10 mb-5 ec-icon-button text-white" aria-label="Back" @click="goBack"><ArrowLeft :size="20" /></button>

    <div class="relative z-10 flex flex-col items-center gap-2 mb-5 text-white">
      <div class="h-16 w-16 rounded-2xl bg-white flex items-center justify-center shadow-xl shadow-black/10"><EcLogo :size="48" /></div>
      <h1 class="font-bold text-xl">{{ t('auth.register') }}</h1>
    </div>

    <form class="relative z-10 ec-card p-5 flex flex-col gap-3" @submit.prevent="submit">
      <div class="flex gap-2">
        <button
          type="button"
          class="flex-1 py-2 rounded-full text-xs"
          :class="accountType === 'user' ? 'ec-btn-primary' : 'bg-gray-100'"
          @click="accountType = 'user'"
        >
          {{ t('auth.regularUser') }}
        </button>
        <button
          type="button"
          class="flex-1 py-2 rounded-full text-xs"
          :class="accountType === 'business' ? 'ec-btn-primary' : 'bg-gray-100'"
          @click="accountType = 'business'"
        >
          {{ t('auth.businessOwner') }}
        </button>
      </div>

      <input v-model="name" required :placeholder="t('auth.name')" class="ec-card px-4 py-3 text-sm outline-none" />
      <input v-model="email" type="email" required :placeholder="t('auth.email')" class="ec-card px-4 py-3 text-sm outline-none" />
      <input v-model="phone" :placeholder="t('auth.phone')" class="ec-card px-4 py-3 text-sm outline-none" />
      <input v-model="password" type="password" required minlength="8" :placeholder="t('auth.password')" class="ec-card px-4 py-3 text-sm outline-none" />

      <p v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</p>

      <button type="submit" class="ec-btn-primary py-3.5 mt-2" :disabled="loading">{{ t('auth.register') }}</button>
    </form>

    <p class="relative z-10 text-center text-sm mt-6" style="color: var(--color-muted)">
      {{ t('auth.haveAccount') }}
      <RouterLink to="/login" class="font-semibold" style="color: var(--color-teal)">{{ t('auth.login') }}</RouterLink>
    </p>
  </div>
</template>
