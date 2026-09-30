<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import type { Advertisement } from '@/types'

const props = defineProps<{ ads: Advertisement[] }>()

const router = useRouter()
const current = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

function go(url: string | null) {
  if (!url) return
  if (url.startsWith('/')) {
    router.push(url)
  } else {
    window.open(url, '_blank', 'noopener')
  }
}

function goToSlide(index: number) {
  current.value = index
  restartTimer()
}

function prevSlide() {
  goToSlide((current.value - 1 + props.ads.length) % props.ads.length)
}

function nextSlide() {
  goToSlide((current.value + 1) % props.ads.length)
}

function startTimer() {
  stopTimer()
  if (props.ads.length > 1) {
    timer = setInterval(() => {
      current.value = (current.value + 1) % props.ads.length
    }, 4500)
  }
}

function stopTimer() {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

function restartTimer() {
  startTimer()
}

watch(
  () => props.ads.length,
  () => {
    current.value = 0
    startTimer()
  },
)

onMounted(startTimer)
onUnmounted(stopTimer)
</script>

<template>
  <div v-if="ads.length" class="safe-x">
    <div class="relative h-40 rounded-[1.35rem] overflow-hidden shadow-xl shadow-black/15 ring-1 ring-white/15">
      <button
        v-for="(ad, index) in ads"
        :key="ad.id"
        class="absolute inset-0 h-full w-full transition-opacity duration-500"
        :class="index === current ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none'"
        @click="go(ad.target_url)"
      >
        <img :src="ad.image" :alt="ad.title" class="h-full w-full object-cover" />
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/5 to-black/10" />
        <span class="absolute bottom-4 left-4 right-16 text-white text-sm font-bold text-left drop-shadow">{{ ad.title }}</span>
      </button>

      <template v-if="ads.length > 1">
        <button
          class="absolute left-2 top-1/2 -translate-y-1/2 z-20 h-8 w-8 rounded-full bg-white/25 backdrop-blur-md text-white flex items-center justify-center transition-colors hover:bg-white/40"
          aria-label="Previous"
          @click.stop="prevSlide"
        >
          <ChevronLeft :size="16" />
        </button>
        <button
          class="absolute right-2 top-1/2 -translate-y-1/2 z-20 h-8 w-8 rounded-full bg-white/25 backdrop-blur-md text-white flex items-center justify-center transition-colors hover:bg-white/40"
          aria-label="Next"
          @click.stop="nextSlide"
        >
          <ChevronRight :size="16" />
        </button>

        <div class="absolute bottom-3 right-4 z-20 flex gap-1.5">
          <button
            v-for="(ad, index) in ads"
            :key="ad.id"
            class="h-1.5 rounded-full transition-all"
            :class="index === current ? 'w-5 bg-white' : 'w-1.5 bg-white/50'"
            :aria-label="`Slide ${index + 1}`"
            @click.stop="goToSlide(index)"
          />
        </div>
      </template>
    </div>
  </div>
</template>
