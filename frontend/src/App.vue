<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import ToastContainer from '@/components/ToastContainer.vue'

const route = useRoute()
const isAdmin = computed(() => route.path.startsWith('/admin'))
const isAi = computed(() => route.path.startsWith('/ai'))
</script>

<template>
  <div :class="isAdmin ? '' : isAi ? 'ai-shell' : 'app-shell'">
    <RouterView />
  </div>
  <ToastContainer />
</template>
