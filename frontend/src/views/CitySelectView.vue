<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { MapPin, Globe } from 'lucide-vue-next'
import api from '@/lib/api'
import { useCityStore } from '@/stores/city'
import { SUPPORTED_LOCALES, applyDirection, persistLocale, localizedName, type SupportedLocale } from '@/i18n'
import EmptyState from '@/components/EmptyState.vue'
import type { City } from '@/types'

const { t, locale } = useI18n()
const router = useRouter()
const cityStore = useCityStore()

const cities = ref<City[]>([])
const loading = ref(true)
const failed = ref(false)
const showLangMenu = ref(false)

function changeLocale(l: SupportedLocale) {
  locale.value = l
  persistLocale(l)
  applyDirection(l)
  showLangMenu.value = false
}

async function loadCities() {
  loading.value = true
  failed.value = false
  try {
    const { data } = await api.get('/cities')
    cities.value = data.data
  } catch (error) {
    console.warn('Failed to load cities', error)
    failed.value = true
  } finally {
    loading.value = false
  }
}

function choose(city: City) {
  cityStore.setCity(city)
  router.replace('/home')
}

onMounted(loadCities)
</script>

<template>
  <div class="min-h-screen p-5 safe-x flex flex-col gap-6 relative overflow-hidden">
    <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-teal-100/50 blur-3xl"></div>
    <div class="relative flex justify-end">
      <div class="relative">
        <button class="p-2 rounded-full bg-black/5" @click="showLangMenu = !showLangMenu">
          <Globe :size="18" style="color: var(--color-navy)" />
        </button>
        <div v-if="showLangMenu" class="absolute right-0 mt-2 bg-white rounded-xl shadow-lg overflow-hidden z-40 w-28">
          <button
            v-for="l in SUPPORTED_LOCALES"
            :key="l"
            class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
            @click="changeLocale(l)"
          >
            {{ l.toUpperCase() }}
          </button>
        </div>
      </div>
    </div>

    <div class="relative text-center px-6">
      <div class="h-14 w-14 rounded-2xl ec-gradient text-white flex items-center justify-center mx-auto mb-4 shadow-lg"><MapPin :size="25" /></div>
      <h1 class="text-2xl font-extrabold tracking-tight" style="color: var(--color-navy)">{{ t('city.title') }}</h1>
      <p class="text-sm mt-2 leading-6" style="color: var(--color-muted)">{{ t('city.subtitle') }}</p>
    </div>

    <div v-if="loading" class="text-center text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

    <EmptyState v-else-if="failed" :message="t('common.retry')" />

    <div v-else class="relative grid grid-cols-2 gap-3">
      <button
        v-for="city in cities"
        :key="city.id"
        class="ec-card overflow-hidden relative flex flex-col items-center justify-end gap-2 h-36 p-3 group"
        @click="choose(city)"
      >
        <template v-if="city.image">
          <img :src="city.image" :alt="city.name" class="absolute inset-0 h-full w-full object-cover" />
          <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/5 to-transparent transition-colors group-hover:from-black/65" />
          <span class="relative font-bold text-sm text-white drop-shadow mb-1">{{ localizedName(city, locale) }}</span>
        </template>
        <template v-else>
          <MapPin :size="28" style="color: var(--color-teal)" />
          <span class="font-semibold text-sm" style="color: var(--color-navy)">{{ localizedName(city, locale) }}</span>
        </template>
      </button>
    </div>
  </div>
</template>
