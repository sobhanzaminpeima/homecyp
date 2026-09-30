<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import type { Package } from '@/types'

const { t } = useI18n()

const packages = ref<Package[]>([])
const loading = ref(true)
const form = ref({ name: '', description: '', price: 0, duration_days: 30, listing_limit: null as number | null, is_featured: false, is_premium: false })
const saving = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/packages')
    packages.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load packages', error)
  } finally {
    loading.value = false
  }
}

async function create() {
  if (!form.value.name.trim()) return
  saving.value = true
  try {
    const { data } = await api.post('/admin/packages', form.value)
    packages.value.push(data.data)
    form.value = { name: '', description: '', price: 0, duration_days: 30, listing_limit: null, is_featured: false, is_premium: false }
  } catch (error) {
    console.warn('Failed to create package', error)
  } finally {
    saving.value = false
  }
}

async function remove(pkg: Package) {
  if (!confirm(`Delete ${pkg.name}?`)) return
  try {
    await api.delete(`/admin/packages/${pkg.id}`)
    packages.value = packages.value.filter((p) => p.id !== pkg.id)
  } catch (error) {
    console.warn('Failed to delete package', error)
  }
}

onMounted(load)
</script>

<template>
  <h1 class="font-bold text-xl mb-6" style="color: var(--color-navy)">{{ t('admin.packages') }}</h1>

  <form class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex flex-wrap gap-2 items-center" @submit.prevent="create">
    <input v-model="form.name" placeholder="Name" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1 min-w-[140px]" />
    <input v-model="form.description" placeholder="Description" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1 min-w-[160px]" />
    <input v-model.number="form.price" type="number" placeholder="Price" class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-24" />
    <input v-model.number="form.duration_days" type="number" placeholder="Days" class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-20" />
    <input
      :value="form.listing_limit"
      type="number"
      placeholder="Listing limit (blank = ∞)"
      class="border border-gray-200 rounded-lg px-3 py-2 text-sm w-40"
      @input="form.listing_limit = ($event.target as HTMLInputElement).value ? Number(($event.target as HTMLInputElement).value) : null"
    />
    <label class="text-xs flex items-center gap-1"><input v-model="form.is_featured" type="checkbox" /> Featured</label>
    <label class="text-xs flex items-center gap-1"><input v-model="form.is_premium" type="checkbox" /> Premium</label>
    <button type="submit" class="ec-btn-primary px-4 text-sm" :disabled="saving">{{ t('common.save') }}</button>
  </form>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <div v-for="pkg in packages" :key="pkg.id" class="bg-white rounded-2xl shadow-sm p-4">
      <p class="font-semibold text-sm" style="color: var(--color-navy)">{{ pkg.name }}</p>
      <p class="text-lg font-bold mt-1" style="color: var(--color-teal)">€{{ pkg.price }}</p>
      <p class="text-xs" style="color: var(--color-muted)">{{ pkg.duration_days }} days · {{ pkg.listing_limit === null ? 'Unlimited listings' : `${pkg.listing_limit} listings` }}</p>
      <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700 mt-3" @click="remove(pkg)">{{ t('common.delete') }}</button>
    </div>
  </div>
</template>
