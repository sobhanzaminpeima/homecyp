<script setup lang="ts">
import { useToast } from '@/composables/useToast'

const { toasts, dismiss } = useToast()
</script>

<template>
  <div class="toast-stack" role="status" aria-live="polite">
    <TransitionGroup name="toast">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="toast-item"
        :class="`toast-item--${toast.type}`"
        @click="dismiss(toast.id)"
      >
        {{ toast.message }}
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-stack {
  position: fixed;
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  width: min(92vw, 380px);
  pointer-events: none;
}

.toast-item {
  pointer-events: auto;
  cursor: pointer;
  padding: 0.75rem 1rem;
  border-radius: 0.75rem;
  color: #fff;
  font-size: 0.9rem;
  box-shadow: var(--shadow-card-lg);
  text-align: center;
}

.toast-item--success {
  background: var(--color-teal-dark);
}

.toast-item--error {
  background: #c0392b;
}

.toast-item--info {
  background: var(--color-navy);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>
