<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Heart, Store, LogOut, Globe, User, Pencil, Bell } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { SUPPORTED_LOCALES, applyDirection, persistLocale, type SupportedLocale } from '@/i18n'
import BottomNav from '@/components/BottomNav.vue'

const { t, locale } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

const editing = ref(false)
const saving = ref(false)
const saved = ref(false)
const errorMessage = ref('')

const form = ref({
  name: authStore.user?.name ?? '',
  phone: authStore.user?.phone ?? '',
  password: '',
})

const accountTypeLabel: Record<string, string> = {
  user: t('auth.regularUser'),
  business: t('auth.businessOwner'),
  admin: 'Admin',
}

function startEditing() {
  form.value = {
    name: authStore.user?.name ?? '',
    phone: authStore.user?.phone ?? '',
    password: '',
  }
  saved.value = false
  errorMessage.value = ''
  editing.value = true
}

async function saveProfile() {
  saving.value = true
  errorMessage.value = ''
  try {
    const payload: Record<string, string> = { name: form.value.name, phone: form.value.phone ?? '' }
    if (form.value.password) {
      payload.password = form.value.password
    }
    await authStore.updateProfile(payload)
    saved.value = true
    editing.value = false
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Could not update profile.'
  } finally {
    saving.value = false
  }
}

async function logout() {
  await authStore.logout()
  router.replace('/home')
}

function changeLocale(l: SupportedLocale) {
  locale.value = l
  persistLocale(l)
  applyDirection(l)
}
</script>

<template>
  <div class="p-5 pb-28 safe-x">
    <div class="flex items-end justify-between mb-5">
      <div><p class="text-[10px] uppercase tracking-[.16em] mb-1" style="color: var(--color-teal)">Account</p><h1 class="font-extrabold text-2xl tracking-tight" style="color: var(--color-navy)">{{ t('profile.title') }}</h1></div>
      <div class="h-11 w-11 rounded-2xl ec-gradient flex items-center justify-center shadow-lg"><User :size="20" class="text-white" /></div>
    </div>

    <div class="ec-card p-5 mb-4">
      <template v-if="!editing">
        <div class="flex items-start justify-between">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-full ec-gradient flex items-center justify-center shrink-0">
              <User :size="22" class="text-white" />
            </div>
            <div>
              <p class="font-semibold" style="color: var(--color-navy)">{{ authStore.user?.name }}</p>
              <p class="text-xs" style="color: var(--color-muted)">{{ authStore.user?.email }}</p>
              <p v-if="authStore.user?.phone" class="text-xs" style="color: var(--color-muted)">{{ authStore.user.phone }}</p>
            </div>
          </div>
          <button class="p-2 rounded-full bg-gray-50" @click="startEditing">
            <Pencil :size="15" style="color: var(--color-teal)" />
          </button>
        </div>
        <div class="flex items-center gap-2 mt-3">
          <span class="text-[11px] px-2 py-1 rounded-full bg-gray-50" style="color: var(--color-muted)">
            {{ accountTypeLabel[authStore.user?.account_type ?? 'user'] }}
          </span>
          <span v-if="authStore.user?.created_at" class="text-[11px]" style="color: var(--color-muted)">
            {{ t('profile.memberSince') }} {{ new Date(authStore.user.created_at).toLocaleDateString() }}
          </span>
        </div>
        <p v-if="saved" class="text-xs text-green-600 mt-3">{{ t('profile.saved') }}</p>
      </template>

      <form v-else class="flex flex-col gap-3" @submit.prevent="saveProfile">
        <input v-model="form.name" :placeholder="t('auth.name')" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
        <input v-model="form.phone" :placeholder="t('auth.phone')" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
        <input v-model="form.password" type="password" :placeholder="t('auth.password')" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
        <p v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</p>
        <div class="flex gap-2">
          <button type="submit" class="ec-btn-primary flex-1 py-2 text-sm" :disabled="saving">{{ t('common.save') }}</button>
          <button type="button" class="flex-1 py-2 text-sm rounded-full bg-gray-100" @click="editing = false">{{ t('common.cancel') }}</button>
        </div>
      </form>
    </div>

    <div class="flex flex-col gap-2">
      <RouterLink to="/notifications" class="ec-card p-4 flex items-center gap-3">
        <Bell :size="18" style="color: var(--color-teal)" />
        <span class="text-sm" style="color: var(--color-navy)">Notifications</span>
      </RouterLink>
      <RouterLink to="/favorites" class="ec-card p-4 flex items-center gap-3">
        <Heart :size="18" style="color: var(--color-teal)" />
        <span class="text-sm" style="color: var(--color-navy)">{{ t('profile.myFavorites') }}</span>
      </RouterLink>
      <RouterLink v-if="authStore.isBusinessOwner" to="/dashboard" class="ec-card p-4 flex items-center gap-3">
        <Store :size="18" style="color: var(--color-teal)" />
        <span class="text-sm" style="color: var(--color-navy)">{{ t('profile.myBusiness') }}</span>
      </RouterLink>
    </div>

    <div class="ec-card p-4 mt-4">
      <p class="text-xs font-semibold mb-2 flex items-center gap-1" style="color: var(--color-muted)">
        <Globe :size="14" /> {{ t('profile.language') }}
      </p>
      <div class="flex flex-wrap gap-2">
        <button
          v-for="l in SUPPORTED_LOCALES"
          :key="l"
          class="px-3 py-1.5 rounded-full text-xs"
          :class="locale === l ? 'ec-btn-primary' : 'bg-gray-100'"
          @click="changeLocale(l)"
        >
          {{ l.toUpperCase() }}
        </button>
      </div>
    </div>

    <button class="ec-card p-4 mt-4 flex items-center gap-3 w-full text-red-500" @click="logout">
      <LogOut :size="18" />
      <span class="text-sm">{{ t('auth.logout') }}</span>
    </button>

    <BottomNav />
  </div>
</template>
