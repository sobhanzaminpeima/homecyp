<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import EcLogo from '@/components/EcLogo.vue'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function submit() {
  loading.value = true
  errorMessage.value = ''
  try {
    const user = await authStore.login(email.value, password.value)
    if (user.account_type !== 'admin') {
      errorMessage.value = 'This account does not have admin access.'
      authStore.clearSession()
      return
    }
    router.replace('/admin/dashboard')
  } catch {
    errorMessage.value = 'Invalid credentials.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-gray-50 max-w-none">
    <div class="w-full max-w-sm">
      <div class="flex flex-col items-center gap-2 mb-8">
        <EcLogo :size="56" />
        <h1 class="font-bold text-lg" style="color: var(--color-navy)">Admin login</h1>
      </div>

      <form class="flex flex-col gap-3 bg-white p-6 rounded-2xl shadow-sm" @submit.prevent="submit">
        <input v-model="email" type="email" required placeholder="Email" class="border border-gray-100 rounded-xl px-4 py-3 text-sm outline-none" />
        <input v-model="password" type="password" required placeholder="Password" class="border border-gray-100 rounded-xl px-4 py-3 text-sm outline-none" />
        <p v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</p>
        <button type="submit" class="ec-btn-primary py-3" :disabled="loading">Log in</button>
      </form>
    </div>
  </div>
</template>
