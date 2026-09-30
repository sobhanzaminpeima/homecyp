<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import type { City } from '@/types'

const { t } = useI18n()

const cities = ref<City[]>([])
const loading = ref(true)
const form = ref({ name: '', name_tr: '', image: '' })
const saving = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/cities')
    cities.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load cities', error)
  } finally {
    loading.value = false
  }
}

async function create() {
  if (!form.value.name.trim()) return
  saving.value = true
  try {
    const { data } = await api.post('/admin/cities', form.value)
    cities.value.push(data.data)
    form.value = { name: '', name_tr: '', image: '' }
  } catch (error) {
    console.warn('Failed to create city', error)
  } finally {
    saving.value = false
  }
}

async function toggleActive(city: City) {
  try {
    await api.put(`/admin/cities/${city.id}`, { name: city.name, is_active: !city.is_active })
    city.is_active = !city.is_active
  } catch (error) {
    console.warn('Failed to toggle city', error)
  }
}

async function remove(city: City) {
  if (!confirm(`Delete ${city.name}?`)) return
  try {
    await api.delete(`/admin/cities/${city.id}`)
    cities.value = cities.value.filter((c) => c.id !== city.id)
  } catch (error) {
    console.warn('Failed to delete city', error)
  }
}

onMounted(load)
</script>

<template>
  <h1 class="font-bold text-xl mb-6" style="color: var(--color-navy)">{{ t('admin.cities') }}</h1>

  <form class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex gap-2" @submit.prevent="create">
    <input v-model="form.name" placeholder="Name" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
    <input v-model="form.name_tr" placeholder="Name (TR)" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
    <input v-model="form.image" placeholder="Image URL" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
    <button type="submit" class="ec-btn-primary px-4 text-sm" :disabled="saving">{{ t('common.save') }}</button>
  </form>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else class="bg-white rounded-2xl shadow-sm divide-y divide-gray-50">
    <div v-for="city in cities" :key="city.id" class="p-4 flex items-center justify-between">
      <div>
        <p class="font-medium text-sm" style="color: var(--color-navy)">{{ city.name }}</p>
        <p class="text-xs" style="color: var(--color-muted)">{{ city.name_tr }}</p>
      </div>
      <div class="flex gap-2">
        <button class="text-xs px-2 py-1 rounded-full" :class="city.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100'" @click="toggleActive(city)">
          {{ city.is_active ? 'Active' : 'Inactive' }}
        </button>
        <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700" @click="remove(city)">{{ t('common.delete') }}</button>
      </div>
    </div>
  </div>
</template>
