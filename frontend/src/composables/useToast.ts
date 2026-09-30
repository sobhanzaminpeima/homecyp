import { reactive } from 'vue'

export type ToastType = 'success' | 'error' | 'info'

export interface Toast {
  id: number
  type: ToastType
  message: string
}

const toasts = reactive<Toast[]>([])
let nextId = 1

function push(type: ToastType, message: string, duration = 4000) {
  const id = nextId++
  toasts.push({ id, type, message })
  setTimeout(() => dismiss(id), duration)
  return id
}

function dismiss(id: number) {
  const index = toasts.findIndex((t) => t.id === id)
  if (index !== -1) toasts.splice(index, 1)
}

export function useToast() {
  return {
    toasts,
    success: (message: string, duration?: number) => push('success', message, duration),
    error: (message: string, duration?: number) => push('error', message, duration),
    info: (message: string, duration?: number) => push('info', message, duration),
    dismiss,
  }
}
