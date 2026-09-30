<script setup lang="ts">
defineProps<{ message: string }>()
</script>

<template>
  <div class="flex flex-col items-center justify-center py-10 px-6 text-center gap-2">
    <p class="text-sm" style="color: var(--color-muted)">{{ message }}</p>
  </div>
</template>
