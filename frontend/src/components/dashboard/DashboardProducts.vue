<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Plus, Trash2, Camera, X } from 'lucide-vue-next'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'
import type { Business, BusinessProduct } from '@/types'

const props = defineProps<{ business: Business; limit: number | null }>()

const { t } = useI18n()
const toast = useToast()

const showForm = ref(false)
const editingId = ref<number | null>(null)
const saving = ref(false)
const uploading = ref(false)

const form = reactive({ name: '', description: '', price: '', image: '' })

const products = computed(() => props.business.products ?? [])
const atLimit = computed(() => props.limit !== null && products.value.length >= props.limit && editingId.value === null)

function openCreate() {
  Object.assign(form, { name: '', description: '', price: '', image: '' })
  editingId.value = null
  showForm.value = true
}

function openEdit(product: BusinessProduct) {
  Object.assign(form, {
    name: product.name,
    description: product.description ?? '',
    price: product.price?.toString() ?? '',
    image: product.image ?? '',
  })
  editingId.value = product.id
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
    console.warn('Failed to upload product image', error)
    toast.error(t('dashboard.uploadFailed'))
  } finally {
    uploading.value = false
  }
}

async function save() {
  if (!form.name.trim()) return
  saving.value = true
  try {
    const payload = { name: form.name, description: form.description || null, price: form.price || null, image: form.image || null }

    if (editingId.value) {
      const { data } = await api.put(`/dashboard/products/${editingId.value}`, payload)
      const index = products.value.findIndex((p) => p.id === editingId.value)
      if (index !== -1) products.value[index] = data.data
    } else {
      const { data } = await api.post('/dashboard/products', payload)
      products.value.unshift(data.data)
    }

    toast.success(t('dashboard.productSaved'))
    closeForm()
  } catch (error: any) {
    console.warn('Failed to save product', error)
    toast.error(error?.response?.data?.errors?.limit?.[0] || t('dashboard.productSaveFailed'))
  } finally {
    saving.value = false
  }
}

async function remove(product: BusinessProduct) {
  if (!confirm(t('dashboard.confirmDeleteProduct'))) return
  try {
    await api.delete(`/dashboard/products/${product.id}`)
    const index = products.value.findIndex((p) => p.id === product.id)
    if (index !== -1) products.value.splice(index, 1)
    toast.success(t('dashboard.productDeleted'))
  } catch (error) {
    console.warn('Failed to delete product', error)
    toast.error(t('dashboard.productSaveFailed'))
  }
}
</script>

<template>
  <div class="ec-card p-4">
    <div class="flex items-center justify-between mb-1">
      <h2 class="font-semibold text-sm" style="color: var(--color-navy)">{{ t('dashboard.products') }}</h2>
      <span class="text-xs" style="color: var(--color-muted)">{{ products.length }}/{{ limit === null ? '∞' : limit }}</span>
    </div>
    <p class="text-xs mb-3" style="color: var(--color-muted)">{{ t('dashboard.productsHint') }}</p>

    <div v-for="product in products" :key="product.id" class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
      <img v-if="product.image" :src="product.image" class="w-12 h-12 rounded-lg object-cover shrink-0" />
      <div v-else class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
        <Camera :size="16" style="color: var(--color-muted)" />
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-sm font-medium truncate" style="color: var(--color-navy)">{{ product.name }}</p>
        <p v-if="product.price" class="text-xs" style="color: var(--color-muted)">€{{ Number(product.price).toFixed(2) }}</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button class="text-xs px-2 py-1 rounded-full bg-gray-100" @click="openEdit(product)">{{ t('common.edit') }}</button>
        <button @click="remove(product)"><Trash2 :size="14" class="text-red-400" /></button>
      </div>
    </div>

    <p v-if="!products.length" class="text-xs py-2" style="color: var(--color-muted)">{{ t('dashboard.noProductsYet') }}</p>

    <button class="ec-btn-primary text-xs px-4 py-2 mt-3 flex items-center gap-1" :disabled="atLimit" @click="openCreate">
      <Plus :size="14" /> {{ t('dashboard.addProduct') }}
    </button>
    <p v-if="atLimit" class="text-xs mt-2" style="color: var(--color-orange-dark)">{{ t('dashboard.limitReached') }}</p>

    <div v-if="showForm" class="fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="closeForm">
      <div class="bg-white w-full sm:max-w-sm sm:rounded-2xl rounded-t-2xl p-5">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-bold text-sm" style="color: var(--color-navy)">{{ editingId ? t('dashboard.editProduct') : t('dashboard.addProduct') }}</h3>
          <button @click="closeForm"><X :size="18" /></button>
        </div>

        <div class="flex flex-col gap-3 text-sm">
          <label class="relative w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden cursor-pointer self-start">
            <img v-if="form.image" :src="form.image" class="w-full h-full object-cover" />
            <Camera v-else :size="18" style="color: var(--color-muted)" />
            <span v-if="uploading" class="absolute inset-0 bg-white/70 flex items-center justify-center text-[10px]">…</span>
            <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" @change="onImageSelected" />
          </label>
          <input v-model="form.name" :placeholder="t('dashboard.productName')" class="border border-gray-200 rounded-lg px-3 py-2" />
          <textarea v-model="form.description" rows="2" :placeholder="t('common.description')" class="border border-gray-200 rounded-lg px-3 py-2" />
          <input v-model="form.price" type="number" min="0" step="0.01" :placeholder="t('project.price')" class="border border-gray-200 rounded-lg px-3 py-2" />
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <button class="text-sm px-4 py-2 rounded-lg bg-gray-100" @click="closeForm">{{ t('common.cancel') }}</button>
          <button class="ec-btn-primary px-4 py-2 text-sm" :disabled="saving || uploading" @click="save">{{ t('common.save') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
