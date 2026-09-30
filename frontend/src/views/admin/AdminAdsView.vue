<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Pencil, Upload } from 'lucide-vue-next'
import api from '@/lib/api'
import type { Advertisement, City } from '@/types'

const { t } = useI18n()

const ads = ref<Advertisement[]>([])
const cities = ref<City[]>([])
const loading = ref(true)
const saving = ref(false)
const uploading = ref(false)
const editingId = ref<number | null>(null)
const errorMessage = ref('')

const emptyForm = () => ({
  title: '',
  image: '',
  target_url: '',
  city_id: '' as number | '',
  starts_at: '',
  ends_at: '',
  is_active: true,
})

const form = ref(emptyForm())

async function load() {
  loading.value = true
  try {
    const [adsRes, citiesRes] = await Promise.all([api.get('/admin/advertisements'), api.get('/cities')])
    ads.value = adsRes.data.data ?? []
    cities.value = citiesRes.data.data ?? []
  } catch (error) {
    console.warn('Failed to load advertisements', error)
  } finally {
    loading.value = false
  }
}

async function handleFileSelect(event: Event) {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  if (!file) return

  uploading.value = true
  errorMessage.value = ''
  try {
    const body = new FormData()
    body.append('file', file)
    const { data } = await api.post('/admin/uploads', body, { headers: { 'Content-Type': 'multipart/form-data' } })
    form.value.image = data.url
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Upload failed.'
  } finally {
    uploading.value = false
    input.value = ''
  }
}

function startEdit(ad: Advertisement) {
  editingId.value = ad.id
  errorMessage.value = ''
  form.value = {
    title: ad.title,
    image: ad.image,
    target_url: ad.target_url ?? '',
    city_id: ad.city_id ?? '',
    starts_at: ad.starts_at ? ad.starts_at.slice(0, 10) : '',
    ends_at: ad.ends_at ? ad.ends_at.slice(0, 10) : '',
    is_active: ad.is_active,
  }
}

function cancelEdit() {
  editingId.value = null
  form.value = emptyForm()
  errorMessage.value = ''
}

async function submit() {
  if (!form.value.title.trim() || !form.value.image.trim()) {
    errorMessage.value = 'Title and image are required.'
    return
  }

  saving.value = true
  errorMessage.value = ''
  const payload = {
    title: form.value.title,
    image: form.value.image,
    target_url: form.value.target_url || null,
    city_id: form.value.city_id || null,
    starts_at: form.value.starts_at || null,
    ends_at: form.value.ends_at || null,
    is_active: form.value.is_active,
  }

  try {
    if (editingId.value) {
      const { data } = await api.put(`/admin/advertisements/${editingId.value}`, payload)
      const index = ads.value.findIndex((a) => a.id === editingId.value)
      if (index !== -1) ads.value[index] = data.data
    } else {
      const { data } = await api.post('/admin/advertisements', payload)
      ads.value.push(data.data)
    }
    cancelEdit()
  } catch (error: any) {
    errorMessage.value = error?.response?.data?.message || 'Could not save advertisement.'
  } finally {
    saving.value = false
  }
}

async function toggleActive(ad: Advertisement) {
  try {
    await api.put(`/admin/advertisements/${ad.id}`, {
      title: ad.title,
      image: ad.image,
      target_url: ad.target_url,
      city_id: ad.city_id,
      starts_at: ad.starts_at,
      ends_at: ad.ends_at,
      is_active: !ad.is_active,
    })
    ad.is_active = !ad.is_active
  } catch (error) {
    console.warn('Failed to toggle advertisement', error)
  }
}

async function remove(ad: Advertisement) {
  if (!confirm(`Delete ${ad.title}?`)) return
  try {
    await api.delete(`/admin/advertisements/${ad.id}`)
    ads.value = ads.value.filter((a) => a.id !== ad.id)
    if (editingId.value === ad.id) cancelEdit()
  } catch (error) {
    console.warn('Failed to delete advertisement', error)
  }
}

onMounted(load)
</script>

<template>
  <h1 class="font-bold text-xl mb-6" style="color: var(--color-navy)">{{ t('admin.advertisements') }}</h1>

  <form class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex flex-col gap-3 max-w-xl" @submit.prevent="submit">
    <p class="text-sm font-semibold" style="color: var(--color-navy)">
      {{ editingId ? 'Edit advertisement' : 'New advertisement' }}
    </p>

    <input v-model="form.title" placeholder="Title" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />

    <div class="flex gap-2 items-center">
      <input v-model="form.image" placeholder="Image URL" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
      <label class="shrink-0 flex items-center gap-1 px-3 py-2 rounded-lg bg-gray-100 text-xs cursor-pointer">
        <Upload :size="14" />
        {{ uploading ? '...' : 'Upload' }}
        <input type="file" accept="image/*" class="hidden" @change="handleFileSelect" />
      </label>
    </div>
    <img v-if="form.image" :src="form.image" class="h-24 w-full object-cover rounded-lg" alt="Preview" />

    <input v-model="form.target_url" placeholder="Link (target URL)" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />

    <select v-model="form.city_id" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All cities</option>
      <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
    </select>

    <div class="flex gap-2">
      <input v-model="form.starts_at" type="date" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
      <input v-model="form.ends_at" type="date" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
    </div>

    <label class="flex items-center gap-2 text-sm"><input v-model="form.is_active" type="checkbox" /> Active</label>

    <p v-if="errorMessage" class="text-xs text-red-500">{{ errorMessage }}</p>

    <div class="flex gap-2">
      <button type="submit" class="ec-btn-primary px-4 py-2 text-sm flex-1" :disabled="saving">
        {{ editingId ? t('common.save') : 'Add' }}
      </button>
      <button v-if="editingId" type="button" class="px-4 py-2 text-sm rounded-full bg-gray-100" @click="cancelEdit">
        {{ t('common.cancel') }}
      </button>
    </div>
  </form>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <div v-for="ad in ads" :key="ad.id" class="bg-white rounded-2xl shadow-sm overflow-hidden">
      <img :src="ad.image" :alt="ad.title" class="h-32 w-full object-cover" />
      <div class="p-3">
        <p class="font-medium text-sm truncate" style="color: var(--color-navy)">{{ ad.title }}</p>
        <a v-if="ad.target_url" :href="ad.target_url" target="_blank" rel="noopener" class="text-xs underline truncate block" style="color: var(--color-teal)">
          {{ ad.target_url }}
        </a>
        <p class="text-xs mt-1" style="color: var(--color-muted)">{{ ad.city?.name ?? 'All cities' }}</p>
        <button
          class="text-xs px-2 py-1 rounded-full mt-2"
          :class="ad.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100'"
          @click="toggleActive(ad)"
        >
          {{ ad.is_active ? 'Active' : 'Inactive' }}
        </button>
        <div class="flex gap-2 mt-2">
          <button class="text-xs px-2 py-1 rounded-full bg-gray-100 flex items-center gap-1" @click="startEdit(ad)">
            <Pencil :size="12" /> {{ t('common.edit') }}
          </button>
          <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700" @click="remove(ad)">{{ t('common.delete') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
