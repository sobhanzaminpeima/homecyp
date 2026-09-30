<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import type { Business, City, Category } from '@/types'

const { t } = useI18n()

const businesses = ref<Business[]>([])
const cities = ref<City[]>([])
const categories = ref<Category[]>([])

const statusFilter = ref('')
const cityFilter = ref('')
const categoryFilter = ref('')
const search = ref('')
const loading = ref(true)

const editing = ref<Business | null>(null)
const editForm = ref<Record<string, any>>({})
const saving = ref(false)
const editError = ref('')

const creating = ref(false)
const createForm = ref<Record<string, any>>({ name: '', city_id: '', category_id: '', status: 'pending' })
const createError = ref('')

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/businesses', {
      params: {
        status: statusFilter.value || undefined,
        city_id: cityFilter.value || undefined,
        category_id: categoryFilter.value || undefined,
        q: search.value || undefined,
        per_page: 50,
      },
    })
    businesses.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load businesses', error)
  } finally {
    loading.value = false
  }
}

async function loadLookups() {
  try {
    const [citiesRes, categoriesRes] = await Promise.all([api.get('/admin/cities'), api.get('/admin/categories')])
    cities.value = citiesRes.data.data ?? []
    categories.value = categoriesRes.data.data ?? []
  } catch (error) {
    console.warn('Failed to load cities/categories', error)
  }
}

async function updateStatus(business: Business, status: Business['status']) {
  try {
    await api.put(`/admin/businesses/${business.id}`, { status })
    business.status = status
  } catch (error) {
    console.warn('Failed to update business status', error)
  }
}

async function toggleFeatured(business: Business) {
  try {
    await api.put(`/admin/businesses/${business.id}`, { is_featured: !business.is_featured })
    business.is_featured = !business.is_featured
  } catch (error) {
    console.warn('Failed to toggle featured', error)
  }
}

async function remove(business: Business) {
  if (!confirm(`Delete ${business.name}?`)) return
  try {
    await api.delete(`/admin/businesses/${business.id}`)
    businesses.value = businesses.value.filter((b) => b.id !== business.id)
  } catch (error) {
    console.warn('Failed to delete business', error)
  }
}

function openEdit(business: Business) {
  editing.value = business
  editError.value = ''
  editForm.value = {
    name: business.name,
    city_id: business.city?.id ?? '',
    category_id: business.category?.id ?? '',
    phone: business.phone ?? '',
    whatsapp: business.whatsapp ?? '',
    email: business.email ?? '',
    website: business.website ?? '',
    website_secondary: business.website_secondary ?? '',
    address: business.address ?? '',
    notes: business.notes ?? '',
    description: business.description ?? '',
    external_rating_avg: business.external_rating_avg ?? 0,
    external_rating_count: business.external_rating_count ?? 0,
    is_verified: business.is_verified,
  }
}

function closeEdit() {
  editing.value = null
}

async function saveEdit() {
  if (!editing.value) return
  saving.value = true
  editError.value = ''
  try {
    const payload = { ...editForm.value }
    payload.website ||= null
    payload.website_secondary ||= null
    payload.notes ||= null
    payload.description ||= null
    payload.phone ||= null
    payload.whatsapp ||= null
    payload.email ||= null
    payload.address ||= null

    const { data } = await api.put(`/admin/businesses/${editing.value.id}`, payload)
    const idx = businesses.value.findIndex((b) => b.id === editing.value!.id)
    if (idx !== -1) businesses.value[idx] = data.data
    closeEdit()
  } catch (error: any) {
    editError.value = error?.response?.data?.message ?? 'Failed to save changes.'
    console.warn('Failed to update business', error)
  } finally {
    saving.value = false
  }
}

function openCreate() {
  creating.value = true
  createError.value = ''
  createForm.value = { name: '', city_id: '', category_id: '', status: 'pending' }
}

function closeCreate() {
  creating.value = false
}

async function saveCreate() {
  if (!createForm.value.name?.trim() || !createForm.value.city_id || !createForm.value.category_id) {
    createError.value = 'Name, city and category are required.'
    return
  }
  saving.value = true
  createError.value = ''
  try {
    const { data } = await api.post('/admin/businesses', createForm.value)
    businesses.value.unshift(data.data)
    closeCreate()
  } catch (error: any) {
    createError.value = error?.response?.data?.message ?? 'Failed to create business.'
    console.warn('Failed to create business', error)
  } finally {
    saving.value = false
  }
}

const cityOptions = computed(() => cities.value)
const categoryOptions = computed(() => categories.value)

watch([statusFilter, cityFilter, categoryFilter], load)
onMounted(() => {
  loadLookups()
  load()
})
</script>

<template>
  <div class="flex items-center justify-between mb-6">
    <h1 class="font-bold text-xl" style="color: var(--color-navy)">{{ t('admin.businesses') }}</h1>
    <button class="ec-btn-primary px-4 text-sm" @click="openCreate">+ Add business</button>
  </div>

  <div class="flex flex-wrap gap-2 mb-4">
    <input v-model="search" placeholder="Search name / phone / email / address..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1 min-w-[220px]" @keyup.enter="load" />
    <select v-model="statusFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All statuses</option>
      <option value="pending">Pending</option>
      <option value="approved">Approved</option>
      <option value="rejected">Rejected</option>
      <option value="expired">Expired</option>
    </select>
    <select v-model="cityFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All cities</option>
      <option v-for="city in cityOptions" :key="city.id" :value="city.id">{{ city.name }}</option>
    </select>
    <select v-model="categoryFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
      <option value="">All categories</option>
      <option v-for="category in categoryOptions" :key="category.id" :value="category.id">{{ category.name }}</option>
    </select>
    <button class="ec-btn-primary px-4 text-sm" @click="load">{{ t('common.search') }}</button>
  </div>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="text-left border-b border-gray-100" style="color: var(--color-muted)">
          <th class="p-3">Name</th>
          <th class="p-3">City</th>
          <th class="p-3">Category</th>
          <th class="p-3">Phone</th>
          <th class="p-3">Rating</th>
          <th class="p-3">Status</th>
          <th class="p-3">Featured</th>
          <th class="p-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="business in businesses" :key="business.id" class="border-b border-gray-50 last:border-0">
          <td class="p-3 font-medium" style="color: var(--color-navy)">
            <div class="flex items-center gap-2">
              <img v-if="business.logo" :src="business.logo" class="w-6 h-6 rounded object-cover" alt="" />
              {{ business.name }}
            </div>
          </td>
          <td class="p-3">{{ business.city?.name }}</td>
          <td class="p-3">{{ business.category?.name }}</td>
          <td class="p-3">{{ business.phone || '—' }}</td>
          <td class="p-3">{{ business.rating_avg ? `${business.rating_avg} (${business.rating_count})` : '—' }}</td>
          <td class="p-3 capitalize">{{ business.status }}</td>
          <td class="p-3">
            <button
              class="px-2 py-1 rounded-full text-xs"
              :class="business.is_featured ? 'ec-btn-accent' : 'bg-gray-100'"
              @click="toggleFeatured(business)"
            >
              {{ t('admin.feature') }}
            </button>
          </td>
          <td class="p-3">
            <div class="flex gap-2 flex-wrap">
              <button class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700" @click="openEdit(business)">Edit</button>
              <button
                v-if="business.status !== 'approved'"
                class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700"
                @click="updateStatus(business, 'approved')"
              >
                {{ t('admin.approve') }}
              </button>
              <button
                v-if="business.status !== 'rejected'"
                class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700"
                @click="updateStatus(business, 'rejected')"
              >
                {{ t('admin.reject') }}
              </button>
              <button class="text-xs px-2 py-1 rounded-full bg-gray-100" @click="remove(business)">
                {{ t('common.delete') }}
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Edit modal -->
  <div v-if="editing" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="closeEdit">
    <div class="bg-white w-full max-w-lg rounded-2xl p-5 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-sm" style="color: var(--color-navy)">Edit business</h3>
        <button class="text-xs" style="color: var(--color-muted)" @click="closeEdit">{{ t('common.close') }}</button>
      </div>

      <div v-if="editError" class="text-xs text-red-600 mb-3">{{ editError }}</div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <label class="col-span-2">
          <span class="text-xs" style="color: var(--color-muted)">Name</span>
          <input v-model="editForm.name" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">City</span>
          <select v-model="editForm.city_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1">
            <option v-for="city in cityOptions" :key="city.id" :value="city.id">{{ city.name }}</option>
          </select>
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Category</span>
          <select v-model="editForm.category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1">
            <option v-for="category in categoryOptions" :key="category.id" :value="category.id">{{ category.name }}</option>
          </select>
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Phone</span>
          <input v-model="editForm.phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">WhatsApp</span>
          <input v-model="editForm.whatsapp" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Email</span>
          <input v-model="editForm.email" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Website</span>
          <input v-model="editForm.website" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Website (secondary)</span>
          <input v-model="editForm.website_secondary" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label class="col-span-2">
          <span class="text-xs" style="color: var(--color-muted)">Address</span>
          <input v-model="editForm.address" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Base rating (e.g. imported)</span>
          <input v-model.number="editForm.external_rating_avg" type="number" step="0.1" min="0" max="5" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Base review count</span>
          <input v-model.number="editForm.external_rating_count" type="number" min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <div class="col-span-2 text-xs -mt-1" style="color: var(--color-muted)">
          Shown rating ({{ editing?.rating_avg ?? 0 }} · {{ editing?.rating_count ?? 0 }} reviews) blends this base rating with real in-app reviews — it updates automatically and isn't edited directly.
        </div>
        <label class="col-span-2">
          <span class="text-xs" style="color: var(--color-muted)">Description (public)</span>
          <textarea v-model="editForm.description" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1"></textarea>
        </label>
        <label class="col-span-2">
          <span class="text-xs" style="color: var(--color-muted)">Notes (admin only)</span>
          <textarea v-model="editForm.notes" rows="2" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1"></textarea>
        </label>
        <label class="col-span-2 flex items-center gap-2">
          <input v-model="editForm.is_verified" type="checkbox" />
          <span class="text-xs" style="color: var(--color-muted)">Verified</span>
        </label>
      </div>

      <div class="flex justify-end gap-2 mt-4">
        <button class="text-sm px-4 py-2 rounded-lg bg-gray-100" @click="closeEdit">Cancel</button>
        <button class="ec-btn-primary px-4 py-2 text-sm" :disabled="saving" @click="saveEdit">{{ t('common.save') }}</button>
      </div>
    </div>
  </div>

  <!-- Create modal -->
  <div v-if="creating" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="closeCreate">
    <div class="bg-white w-full max-w-lg rounded-2xl p-5 max-h-[90vh] overflow-y-auto">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-sm" style="color: var(--color-navy)">Add business</h3>
        <button class="text-xs" style="color: var(--color-muted)" @click="closeCreate">{{ t('common.close') }}</button>
      </div>

      <div v-if="createError" class="text-xs text-red-600 mb-3">{{ createError }}</div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <label class="col-span-2">
          <span class="text-xs" style="color: var(--color-muted)">Name *</span>
          <input v-model="createForm.name" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">City *</span>
          <select v-model="createForm.city_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1">
            <option value="" disabled>Select city</option>
            <option v-for="city in cityOptions" :key="city.id" :value="city.id">{{ city.name }}</option>
          </select>
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Category *</span>
          <select v-model="createForm.category_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1">
            <option value="" disabled>Select category</option>
            <option v-for="category in categoryOptions" :key="category.id" :value="category.id">{{ category.name }}</option>
          </select>
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Phone</span>
          <input v-model="createForm.phone" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Email</span>
          <input v-model="createForm.email" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label class="col-span-2">
          <span class="text-xs" style="color: var(--color-muted)">Website</span>
          <input v-model="createForm.website" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label class="col-span-2">
          <span class="text-xs" style="color: var(--color-muted)">Address</span>
          <input v-model="createForm.address" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
      </div>

      <div class="flex justify-end gap-2 mt-4">
        <button class="text-sm px-4 py-2 rounded-lg bg-gray-100" @click="closeCreate">Cancel</button>
        <button class="ec-btn-primary px-4 py-2 text-sm" :disabled="saving" @click="saveCreate">{{ t('common.save') }}</button>
      </div>
    </div>
  </div>
</template>
