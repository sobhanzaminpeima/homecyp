<script setup lang="ts">
withDefaults(defineProps<{ horizontal?: boolean }>(), { horizontal: false })
</script>

<template>
  <div class="ec-card overflow-hidden flex animate-pulse" :class="horizontal ? 'flex-col w-44 shrink-0' : 'flex-row w-full items-stretch'">
    <div class="bg-gray-200 shrink-0" :class="horizontal ? 'h-28 w-full' : 'h-24 w-24'" />
    <div class="p-3 flex flex-col gap-2 min-w-0 flex-1 justify-center">
      <div class="h-3.5 rounded-full bg-gray-200" :style="{ width: horizontal ? '85%' : '60%' }" />
      <div class="h-3 rounded-full bg-gray-100" style="width: 45%" />
      <div class="h-3 rounded-full bg-gray-100" style="width: 30%" />
    </div>
  </div>
</template>
