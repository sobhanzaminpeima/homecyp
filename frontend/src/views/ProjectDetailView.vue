<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { ArrowLeft, Phone, MessageCircle, MapPin, BedDouble, Bath, Ruler, Layers } from 'lucide-vue-next'
import api from '@/lib/api'
import { localizedName } from '@/i18n'
import { useSmartBack } from '@/composables/useSmartBack'
import EmptyState from '@/components/EmptyState.vue'
import type { BusinessProject } from '@/types'

const { t, locale } = useI18n()
const route = useRoute()
const goBack = useSmartBack('/explore')

const project = ref<BusinessProject | null>(null)
const loading = ref(true)
const failed = ref(false)
const activeImage = ref(0)

const id = computed(() => route.params.id as string)

async function load() {
  loading.value = true
  failed.value = false
  activeImage.value = 0
  try {
    const { data } = await api.get(`/projects/${id.value}`)
    project.value = data.data
  } catch (error) {
    console.warn('Failed to load project', error)
    failed.value = true
  } finally {
    loading.value = false
  }
}

onMounted(load)
watch(id, () => {
  window.scrollTo({ top: 0 })
  load()
})
</script>

<template>
  <div v-if="loading" class="p-6 text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else-if="failed || !project" class="p-6">
    <EmptyState :message="t('project.loadFailed')" />
  </div>

  <div v-else class="pb-24">
    <div class="relative h-64 bg-gray-100">
      <img
        v-if="project.images.length"
        :src="project.images[activeImage]"
        :alt="project.title"
        class="w-full h-full object-cover"
      />
      <div v-else class="w-full h-full ec-gradient" />
      <button class="absolute top-4 left-4 bg-white/90 rounded-full p-2" @click="goBack">
        <ArrowLeft :size="18" />
      </button>

      <div v-if="project.images.length > 1" class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5">
        <button
          v-for="(img, i) in project.images"
          :key="img"
          class="w-2 h-2 rounded-full"
          :class="i === activeImage ? 'bg-white' : 'bg-white/50'"
          @click="activeImage = i"
        />
      </div>
    </div>

    <div v-if="project.images.length > 1" class="flex gap-2 px-5 py-3 overflow-x-auto">
      <img
        v-for="(img, i) in project.images"
        :key="img"
        :src="img"
        class="w-16 h-16 rounded-lg object-cover shrink-0 cursor-pointer"
        :class="i === activeImage ? 'ring-2' : ''"
        :style="i === activeImage ? { '--tw-ring-color': 'var(--color-teal)' } : {}"
        @click="activeImage = i"
      />
    </div>

    <div class="px-5">
      <div class="ec-card p-4">
        <div class="flex items-center justify-between gap-2">
          <h1 class="font-bold text-lg" style="color: var(--color-navy)">{{ project.title }}</h1>
          <span class="text-xs px-2 py-1 rounded-full shrink-0" style="background: var(--color-teal); color: white">
            {{ t(`project.${project.listing_type === 'sale' ? 'forSale' : project.listing_type === 'rent' ? 'forRent' : 'presale'}`) }}
          </span>
        </div>

        <p v-if="project.price" class="text-xl font-bold mt-2" style="color: var(--color-teal)">
          {{ project.currency }} {{ Number(project.price).toLocaleString() }}
        </p>

        <div class="flex flex-wrap gap-4 mt-3 text-sm" style="color: var(--color-navy)">
          <div v-if="project.area_m2" class="flex items-center gap-1">
            <Ruler :size="15" /> {{ project.area_m2 }} m²
          </div>
          <div v-if="project.bedrooms" class="flex items-center gap-1">
            <BedDouble :size="15" /> {{ project.bedrooms }} {{ t('project.bedrooms') }}
          </div>
          <div v-if="project.bathrooms" class="flex items-center gap-1">
            <Bath :size="15" /> {{ project.bathrooms }} {{ t('project.bathrooms') }}
          </div>
          <div v-if="project.floor" class="flex items-center gap-1">
            <Layers :size="15" /> {{ project.floor }}
          </div>
        </div>

        <p v-if="project.description" class="text-sm mt-4" style="color: var(--color-navy)">{{ project.description }}</p>

        <div v-if="project.address" class="flex items-start gap-2 mt-4 text-xs" style="color: var(--color-muted)">
          <MapPin :size="14" class="mt-0.5 shrink-0" />
          {{ project.address }}
        </div>
      </div>

      <div v-if="project.business" class="ec-card p-4 mt-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('project.contactOwner') }}</h2>
        <RouterLink :to="`/business/${project.business.slug}`" class="flex items-center gap-3">
          <img
            v-if="project.business.logo"
            :src="project.business.logo"
            class="w-12 h-12 rounded-xl object-cover shrink-0"
          />
          <div class="min-w-0">
            <p class="text-sm font-medium truncate" style="color: var(--color-navy)">{{ project.business.name }}</p>
            <p class="text-xs truncate" style="color: var(--color-muted)">
              {{ project.business.category ? localizedName(project.business.category, locale) : '' }}
            </p>
          </div>
        </RouterLink>
        <div class="flex gap-2 mt-3">
          <a v-if="project.business.phone" :href="`tel:${project.business.phone}`" class="ec-btn-primary text-xs px-3 py-2 flex items-center gap-1">
            <Phone :size="14" /> {{ t('common.call') }}
          </a>
          <a
            v-if="project.business.whatsapp"
            :href="`https://wa.me/${project.business.whatsapp.replace(/[^0-9]/g, '')}`"
            target="_blank"
            rel="noopener"
            class="text-xs px-3 py-2 rounded-full bg-[#25D366] text-white flex items-center gap-1"
          >
            <MessageCircle :size="14" /> {{ t('common.whatsapp') }}
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
