<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import BusinessCard from '@/components/BusinessCard.vue'
import BottomNav from '@/components/BottomNav.vue'
import EmptyState from '@/components/EmptyState.vue'
import BusinessCardSkeleton from '@/components/skeletons/BusinessCardSkeleton.vue'
import type { Business } from '@/types'

const { t } = useI18n()
const favorites = ref<Business[]>([])
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/favorites')
    favorites.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load favorites', error)
  } finally {
    loading.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="p-5 pb-28 safe-x">
    <div class="ec-gradient rounded-[1.75rem] p-5 mb-5 text-white shadow-lg">
      <p class="text-[10px] uppercase tracking-[.18em] opacity-70 mb-1">Your collection</p>
      <h1 class="font-extrabold text-xl">{{ t('favorites.title') }}</h1>
      <p class="text-xs opacity-75 mt-1">Keep the places you love close at hand.</p>
    </div>

    <div v-if="loading" class="flex flex-col gap-3">
      <BusinessCardSkeleton v-for="n in 4" :key="n" />
    </div>
    <EmptyState v-else-if="!favorites.length" :message="t('favorites.empty')" />
    <div v-else class="flex flex-col gap-3">
      <BusinessCard v-for="business in favorites" :key="business.id" :business="business" />
    </div>

    <BottomNav />
  </div>
</template>
