<script setup lang="ts">
withDefaults(defineProps<{ size?: number; rounded?: boolean }>(), {
  size: 40,
  rounded: true,
})
</script>

<template>
  <img
    src="/pwa-512.png"
    alt="Easy Cyprus"
    :width="size"
    :height="size"
    :style="{ width: `${size}px`, height: `${size}px`, borderRadius: rounded ? '22%' : 0 }"
    class="object-cover shrink-0"
  />
</template>
