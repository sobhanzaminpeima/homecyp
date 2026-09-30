<script setup lang="ts">
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import EcLogo from '@/components/EcLogo.vue'
import { useCityStore } from '@/stores/city'

const router = useRouter()
const { t } = useI18n()
const cityStore = useCityStore()

onMounted(() => {
  setTimeout(() => {
    router.replace(cityStore.hasCity ? '/home' : '/city')
  }, 1200)
})
</script>

<template>
  <div class="ec-gradient min-h-screen flex flex-col items-center justify-center gap-4 text-white">
    <EcLogo :size="96" />
    <h1 class="text-2xl font-bold">Easy Cyprus</h1>
    <p class="text-sm opacity-90">{{ t('splash.tagline') }}</p>
  </div>
</template>
