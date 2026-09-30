<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import type { Category } from '@/types'

const { t } = useI18n()

const categories = ref<Category[]>([])
const loading = ref(true)
const form = ref({ name: '', name_tr: '', icon: '', content_type: 'none' })
const saving = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/categories')
    categories.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load categories', error)
  } finally {
    loading.value = false
  }
}

async function create() {
  if (!form.value.name.trim()) return
  saving.value = true
  try {
    const { data } = await api.post('/admin/categories', form.value)
    categories.value.push(data.data)
    form.value = { name: '', name_tr: '', icon: '', content_type: 'none' }
  } catch (error) {
    console.warn('Failed to create category', error)
  } finally {
    saving.value = false
  }
}

async function toggleActive(category: Category) {
  try {
    await api.put(`/admin/categories/${category.id}`, { name: category.name, is_active: !category.is_active })
    category.is_active = !category.is_active
  } catch (error) {
    console.warn('Failed to toggle category', error)
  }
}

async function changeContentType(category: Category, content_type: string) {
  try {
    await api.put(`/admin/categories/${category.id}`, { name: category.name, content_type })
    category.content_type = content_type as Category['content_type']
  } catch (error) {
    console.warn('Failed to change category content type', error)
  }
}

async function remove(category: Category) {
  if (!confirm(`Delete ${category.name}?`)) return
  try {
    await api.delete(`/admin/categories/${category.id}`)
    categories.value = categories.value.filter((c) => c.id !== category.id)
  } catch (error) {
    console.warn('Failed to delete category', error)
  }
}

onMounted(load)
</script>

<template>
  <h1 class="font-bold text-xl mb-6" style="color: var(--color-navy)">{{ t('admin.categories') }}</h1>

  <form class="bg-white rounded-2xl shadow-sm p-4 mb-4 flex gap-2" @submit.prevent="create">
    <input v-model="form.name" placeholder="Name" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
    <input v-model="form.name_tr" placeholder="Name (TR)" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
    <input v-model="form.icon" placeholder="Icon key (e.g. utensils)" class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1" />
    <select v-model="form.content_type" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
      <option value="none">No showcase</option>
      <option value="projects">Projects (real estate)</option>
      <option value="products">Products</option>
      <option value="services">Services</option>
    </select>
    <button type="submit" class="ec-btn-primary px-4 text-sm" :disabled="saving">{{ t('common.save') }}</button>
  </form>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else class="bg-white rounded-2xl shadow-sm divide-y divide-gray-50">
    <div v-for="category in categories" :key="category.id" class="p-4 flex items-center justify-between">
      <div>
        <p class="font-medium text-sm" style="color: var(--color-navy)">{{ category.name }}</p>
        <p class="text-xs" style="color: var(--color-muted)">{{ category.name_tr }}</p>
      </div>
      <div class="flex items-center gap-2">
        <select
          :value="category.content_type"
          class="border border-gray-200 rounded-lg px-2 py-1 text-xs"
          @change="changeContentType(category, ($event.target as HTMLSelectElement).value)"
        >
          <option value="none">No showcase</option>
          <option value="projects">Projects</option>
          <option value="products">Products</option>
          <option value="services">Services</option>
        </select>
        <button class="text-xs px-2 py-1 rounded-full bg-gray-100" @click="toggleActive(category)">Toggle active</button>
        <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700" @click="remove(category)">{{ t('common.delete') }}</button>
      </div>
    </div>
  </div>
</template>
