<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'
import BusinessCard from '@/components/BusinessCard.vue'
import type { Business } from '@/types'

const props = defineProps<{ businesses: Business[] }>()

const current = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

const visibleCount = computed(() => Math.min(2, props.businesses.length))
const pageCount = computed(() => Math.max(1, props.businesses.length - visibleCount.value + 1))

function startTimer() {
  stopTimer()
  if (pageCount.value > 1) {
    timer = setInterval(() => {
      current.value = (current.value + 1) % pageCount.value
    }, 5000)
  }
}

function stopTimer() {
  if (timer) {
    clearInterval(timer)
    timer = null
  }
}

function goTo(index: number) {
  current.value = index
  startTimer()
}

function prev() {
  goTo((current.value - 1 + pageCount.value) % pageCount.value)
}

function next() {
  goTo((current.value + 1) % pageCount.value)
}

watch(
  () => props.businesses.length,
  () => {
    current.value = 0
    startTimer()
  },
)

onMounted(startTimer)
onUnmounted(stopTimer)
</script>

<template>
  <div v-if="businesses.length" class="relative">
    <div class="overflow-hidden">
      <div
        class="flex gap-3 transition-transform duration-500 ease-out"
        :style="{ transform: `translateX(-${current * (100 / visibleCount)}%)` }"
      >
        <div v-for="business in businesses" :key="business.id" class="shrink-0" :style="{ width: `calc(${100 / visibleCount}% - 0.5rem)` }">
          <BusinessCard :business="business" horizontal class="w-full" />
        </div>
      </div>
    </div>

    <template v-if="pageCount > 1">
      <button
        class="absolute -left-3 top-1/2 -translate-y-1/2 bg-white rounded-full shadow-md shadow-black/10 h-8 w-8 flex items-center justify-center z-10 transition-transform active:scale-90"
        style="color: var(--color-teal)"
        aria-label="Previous"
        @click="prev"
      >
        <ChevronLeft :size="16" />
      </button>
      <button
        class="absolute -right-3 top-1/2 -translate-y-1/2 bg-white rounded-full shadow-md shadow-black/10 h-8 w-8 flex items-center justify-center z-10 transition-transform active:scale-90"
        style="color: var(--color-teal)"
        aria-label="Next"
        @click="next"
      >
        <ChevronRight :size="16" />
      </button>

      <div class="flex justify-center gap-1.5 mt-3">
        <button
          v-for="index in pageCount"
          :key="index"
          class="h-1.5 rounded-full transition-all"
          :class="index - 1 === current ? 'w-5' : 'w-1.5 bg-gray-300'"
          :style="index - 1 === current ? 'background: linear-gradient(135deg, var(--color-teal-light), var(--color-teal-dark))' : ''"
          :aria-label="`Slide ${index}`"
          @click="goTo(index - 1)"
        />
      </div>
    </template>
  </div>
</template>
