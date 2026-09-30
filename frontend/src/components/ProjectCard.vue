<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { BedDouble, Ruler } from 'lucide-vue-next'
import type { BusinessProject } from '@/types'

defineProps<{ project: BusinessProject }>()

const { t } = useI18n()

const listingLabel = (type: BusinessProject['listing_type']) =>
  type === 'sale' ? 'forSale' : type === 'rent' ? 'forRent' : 'presale'
</script>

<template>
  <RouterLink :to="`/project/${project.id}`" class="ec-card overflow-hidden flex flex-col">
    <div class="h-28 bg-gray-100 relative">
      <img v-if="project.images[0]" :src="project.images[0]" :alt="project.title" class="h-full w-full object-cover" />
      <div v-else class="h-full w-full ec-gradient" />
      <span class="absolute top-2 left-2 text-[10px] px-2 py-0.5 rounded-full text-white" style="background: var(--color-teal)">
        {{ t(`project.${listingLabel(project.listing_type)}`) }}
      </span>
    </div>
    <div class="p-3 flex flex-col gap-1 min-w-0">
      <span class="font-semibold text-sm truncate" style="color: var(--color-navy)">{{ project.title }}</span>
      <span v-if="project.price" class="text-sm font-bold" style="color: var(--color-teal)">
        {{ project.currency }} {{ Number(project.price).toLocaleString() }}
      </span>
      <div class="flex items-center gap-3 text-xs mt-1" style="color: var(--color-muted)">
        <span v-if="project.area_m2" class="flex items-center gap-1"><Ruler :size="12" /> {{ project.area_m2 }} m²</span>
        <span v-if="project.bedrooms" class="flex items-center gap-1"><BedDouble :size="12" /> {{ project.bedrooms }}</span>
      </div>
    </div>
  </RouterLink>
</template>
