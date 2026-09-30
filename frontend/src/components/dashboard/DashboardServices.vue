<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Plus, Trash2, Camera, X } from 'lucide-vue-next'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'
import type { Business, BusinessServiceItem } from '@/types'

const props = defineProps<{ business: Business; limit: number | null }>()

const { t } = useI18n()
const toast = useToast()

const showForm = ref(false)
const editingId = ref<number | null>(null)
const saving = ref(false)
const uploading = ref(false)

const form = reactive({ name: '', description: '', price: '', duration_minutes: '', image: '' })

const services = computed(() => props.business.services ?? [])
const atLimit = computed(() => props.limit !== null && services.value.length >= props.limit && editingId.value === null)

function openCreate() {
  Object.assign(form, { name: '', description: '', price: '', duration_minutes: '', image: '' })
  editingId.value = null
  showForm.value = true
}

function openEdit(service: BusinessServiceItem) {
  Object.assign(form, {
    name: service.name,
    description: service.description ?? '',
    price: service.price?.toString() ?? '',
    duration_minutes: service.duration_minutes?.toString() ?? '',
    image: service.image ?? '',
  })
  editingId.value = service.id
  showForm.value = true
}

function closeForm() {
  showForm.value = false
}

async function onImageSelected(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  uploading.value = true
  const body = new FormData()
  body.append('file', file)
  try {
    const { data } = await api.post('/uploads', body, { headers: { 'Content-Type': 'multipart/form-data' } })
    form.image = data.url
  } catch (error) {
    console.warn('Failed to upload service image', error)
    toast.error(t('dashboard.uploadFailed'))
  } finally {
    uploading.value = false
  }
}

async function save() {
  if (!form.name.trim()) return
  saving.value = true
  try {
    const payload = {
      name: form.name,
      description: form.description || null,
      price: form.price || null,
      duration_minutes: form.duration_minutes || null,
      image: form.image || null,
    }

    if (editingId.value) {
      const { data } = await api.put(`/dashboard/services/${editingId.value}`, payload)
      const index = services.value.findIndex((s) => s.id === editingId.value)
      if (index !== -1) services.value[index] = data.data
    } else {
      const { data } = await api.post('/dashboard/services', payload)
      services.value.unshift(data.data)
    }

    toast.success(t('dashboard.serviceSaved'))
    closeForm()
  } catch (error: any) {
    console.warn('Failed to save service', error)
    toast.error(error?.response?.data?.errors?.limit?.[0] || t('dashboard.serviceSaveFailed'))
  } finally {
    saving.value = false
  }
}

async function remove(service: BusinessServiceItem) {
  if (!confirm(t('dashboard.confirmDeleteService'))) return
  try {
    await api.delete(`/dashboard/services/${service.id}`)
    const index = services.value.findIndex((s) => s.id === service.id)
    if (index !== -1) services.value.splice(index, 1)
    toast.success(t('dashboard.serviceDeleted'))
  } catch (error) {
    console.warn('Failed to delete service', error)
    toast.error(t('dashboard.serviceSaveFailed'))
  }
}
</script>

<template>
  <div class="ec-card p-4">
    <div class="flex items-center justify-between mb-1">
      <h2 class="font-semibold text-sm" style="color: var(--color-navy)">{{ t('dashboard.services') }}</h2>
      <span class="text-xs" style="color: var(--color-muted)">{{ services.length }}/{{ limit === null ? '∞' : limit }}</span>
    </div>
    <p class="text-xs mb-3" style="color: var(--color-muted)">{{ t('dashboard.servicesHint') }}</p>

    <div v-for="service in services" :key="service.id" class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
      <img v-if="service.image" :src="service.image" class="w-12 h-12 rounded-lg object-cover shrink-0" />
      <div v-else class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
        <Camera :size="16" style="color: var(--color-muted)" />
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-sm font-medium truncate" style="color: var(--color-navy)">{{ service.name }}</p>
        <p class="text-xs" style="color: var(--color-muted)">
          <span v-if="service.price">€{{ Number(service.price).toFixed(2) }}</span>
          <span v-if="service.duration_minutes"> · {{ service.duration_minutes }} {{ t('project.minutes') }}</span>
        </p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button class="text-xs px-2 py-1 rounded-full bg-gray-100" @click="openEdit(service)">{{ t('common.edit') }}</button>
        <button @click="remove(service)"><Trash2 :size="14" class="text-red-400" /></button>
      </div>
    </div>

    <p v-if="!services.length" class="text-xs py-2" style="color: var(--color-muted)">{{ t('dashboard.noServicesYet') }}</p>

    <button class="ec-btn-primary text-xs px-4 py-2 mt-3 flex items-center gap-1" :disabled="atLimit" @click="openCreate">
      <Plus :size="14" /> {{ t('dashboard.addService') }}
    </button>
    <p v-if="atLimit" class="text-xs mt-2" style="color: var(--color-orange-dark)">{{ t('dashboard.limitReached') }}</p>

    <div v-if="showForm" class="fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="closeForm">
      <div class="bg-white w-full sm:max-w-sm sm:rounded-2xl rounded-t-2xl p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-bold text-sm" style="color: var(--color-navy)">{{ editingId ? t('dashboard.editService') : t('dashboard.addService') }}</h3>
          <button @click="closeForm"><X :size="18" /></button>
        </div>

        <div class="flex flex-col gap-3 text-sm">
          <label class="relative w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden cursor-pointer self-start">
            <img v-if="form.image" :src="form.image" class="w-full h-full object-cover" />
            <Camera v-else :size="18" style="color: var(--color-muted)" />
            <span v-if="uploading" class="absolute inset-0 bg-white/70 flex items-center justify-center text-[10px]">…</span>
            <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" @change="onImageSelected" />
          </label>
          <input v-model="form.name" :placeholder="t('dashboard.serviceName')" class="border border-gray-200 rounded-lg px-3 py-2" />
          <textarea v-model="form.description" rows="2" :placeholder="t('common.description')" class="border border-gray-200 rounded-lg px-3 py-2" />
          <div class="grid grid-cols-2 gap-2">
            <input v-model="form.price" type="number" min="0" step="0.01" :placeholder="t('project.price')" class="border border-gray-200 rounded-lg px-3 py-2" />
            <input v-model="form.duration_minutes" type="number" min="0" :placeholder="t('project.durationMinutes')" class="border border-gray-200 rounded-lg px-3 py-2" />
          </div>
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <button class="text-sm px-4 py-2 rounded-lg bg-gray-100" @click="closeForm">{{ t('common.cancel') }}</button>
          <button class="ec-btn-primary px-4 py-2 text-sm" :disabled="saving || uploading" @click="save">{{ t('common.save') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
