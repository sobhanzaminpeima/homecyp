<script setup lang="ts">
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Star, BadgeCheck } from 'lucide-vue-next'
import { localizedName } from '@/i18n'
import type { Business } from '@/types'

defineProps<{ business: Business; horizontal?: boolean }>()

const { locale } = useI18n()

// A small square logo used as a stand-in cover photo looks cropped/stretched
// when object-cover forces it into the wide horizontal-card aspect ratio —
// switch that one case to object-contain once we know the image is square.
const isSquareLogo = ref(false)
const imageFailed = ref(false)
function onImgLoad(event: Event) {
  const img = event.target as HTMLImageElement
  const ratio = img.naturalWidth / img.naturalHeight
  isSquareLogo.value = ratio > 0.85 && ratio < 1.15 && img.naturalWidth <= 256
}
function onImgError() {
  imageFailed.value = true
}
</script>

<template>
  <RouterLink
    :to="`/business/${business.slug}`"
    class="ec-card overflow-hidden flex group"
    :class="horizontal ? 'flex-col w-44 shrink-0' : 'flex-row w-full items-stretch'"
  >
    <div
      class="flex items-center justify-center shrink-0"
      :class="[horizontal ? 'h-32 w-full' : 'h-28 w-28', horizontal && isSquareLogo && business.cover_image ? 'ec-gradient' : 'bg-gray-100']"
    >
      <img
        v-if="business.cover_image && !imageFailed"
        :src="business.cover_image"
        :alt="business.name"
        :class="horizontal && isSquareLogo ? 'h-full w-full object-contain p-4' : 'h-full w-full object-cover'"
        @load="onImgLoad"
        @error="onImgError"
      />
      <img
        v-else
        src="/default-business-photo.png"
        :alt="business.name"
        class="h-full w-full object-contain p-3 opacity-60"
      />
    </div>

    <div class="p-3.5 flex flex-col gap-1.5 min-w-0 flex-1">
      <div class="flex items-center gap-1 min-w-0">
        <span class="font-bold text-sm truncate tracking-[-.01em]" style="color: var(--color-navy)">{{ business.name }}</span>
        <BadgeCheck v-if="business.is_verified" :size="14" style="color: var(--color-teal)" />
      </div>
      <span class="text-[11px] truncate" style="color: var(--color-muted)">
        {{ business.category ? localizedName(business.category, locale) : '' }} · {{ business.city ? localizedName(business.city, locale) : '' }}
      </span>
      <div class="flex items-center gap-1 text-xs mt-auto pt-1">
        <Star :size="13" fill="#f5a623" style="color: #f5a623" />
        <span class="font-medium">{{ business.rating_avg.toFixed(1) }}</span>
        <span style="color: var(--color-muted)">({{ business.rating_count }})</span>
        <span v-if="business.distance_km !== undefined" style="color: var(--color-teal)" class="ml-auto font-medium">
          {{ business.distance_km < 1 ? `${Math.round(business.distance_km * 1000)}m` : `${business.distance_km.toFixed(1)}km` }}
        </span>
      </div>
    </div>
  </RouterLink>
</template>
