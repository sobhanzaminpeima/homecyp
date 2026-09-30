<script setup lang="ts">
import { onMounted, ref, reactive, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { Eye, Star, Heart, Plus, Trash2, Camera } from 'lucide-vue-next'
import api from '@/lib/api'
import { localizedName } from '@/i18n'
import { useToast } from '@/composables/useToast'
import BottomNav from '@/components/BottomNav.vue'
import DashboardProjects from '@/components/dashboard/DashboardProjects.vue'
import DashboardProducts from '@/components/dashboard/DashboardProducts.vue'
import DashboardServices from '@/components/dashboard/DashboardServices.vue'
import DashboardSubscription from '@/components/dashboard/DashboardSubscription.vue'
import type { Business, City, Category } from '@/types'

const { t, locale } = useI18n()
const toast = useToast()

const business = ref<Business | null>(null)
const stats = ref<{ view_count: number; rating_avg: number; rating_count: number; favorites_count: number; status: string; listing_limit: number | null } | null>(null)
const cities = ref<City[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)

const form = ref({ name: '', city_id: '', category_id: '', description: '' })
const savingBusiness = ref(false)

const editForm = reactive({
  name: '',
  city_id: '' as string | number,
  category_id: '' as string | number,
  description: '',
  address: '',
  phone: '',
  whatsapp: '',
  email: '',
  website: '',
  logo: '',
  cover_image: '',
})
const savingEdit = ref(false)
const uploadingLogo = ref(false)
const uploadingCover = ref(false)

watch(business, (b) => {
  if (!b) return
  editForm.name = b.name
  editForm.city_id = b.city?.id ?? ''
  editForm.category_id = b.category?.id ?? ''
  editForm.description = b.description ?? ''
  editForm.address = b.address ?? ''
  editForm.phone = b.phone ?? ''
  editForm.whatsapp = b.whatsapp ?? ''
  editForm.email = b.email ?? ''
  editForm.website = b.website ?? ''
  editForm.logo = b.logo ?? ''
  editForm.cover_image = b.cover_image ?? ''
}, { immediate: true })

const newMenuName = ref('')
const newItem = ref<Record<number, { name: string; price: string }>>({})

async function loadAll() {
  loading.value = true
  try {
    const [bizRes, catRes, cityRes] = await Promise.all([
      api.get('/dashboard/business'),
      api.get('/categories'),
      api.get('/cities'),
    ])
    business.value = bizRes.data.data ?? null
    categories.value = catRes.data.data ?? []
    cities.value = cityRes.data.data ?? []

    if (business.value) {
      await loadStats()
      for (const menu of business.value.menus ?? []) {
        newItem.value[menu.id] = { name: '', price: '' }
      }
    }
  } catch (error) {
    console.warn('Failed to load dashboard', error)
  } finally {
    loading.value = false
  }
}

async function loadStats() {
  try {
    const { data } = await api.get('/dashboard/stats')
    stats.value = data
  } catch (error) {
    console.warn('Failed to load stats', error)
  }
}

async function createBusiness() {
  savingBusiness.value = true
  try {
    const { data } = await api.post('/businesses', form.value)
    business.value = data.data
    await loadStats()
  } catch (error) {
    console.warn('Failed to create business', error)
  } finally {
    savingBusiness.value = false
  }
}

async function uploadImage(file: File): Promise<string | null> {
  const body = new FormData()
  body.append('file', file)
  try {
    const { data } = await api.post('/uploads', body, { headers: { 'Content-Type': 'multipart/form-data' } })
    return data.url as string
  } catch (error) {
    console.warn('Failed to upload image', error)
    toast.error(t('dashboard.uploadFailed'))
    return null
  }
}

async function onLogoSelected(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  uploadingLogo.value = true
  const url = await uploadImage(file)
  if (url) editForm.logo = url
  uploadingLogo.value = false
}

async function onCoverSelected(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return
  uploadingCover.value = true
  const url = await uploadImage(file)
  if (url) editForm.cover_image = url
  uploadingCover.value = false
}

async function saveEdit() {
  if (!business.value) return
  savingEdit.value = true
  try {
    const { data } = await api.put(`/businesses/${business.value.id}`, editForm)
    business.value = data.data
    toast.success(t('dashboard.saved'))
  } catch (error) {
    console.warn('Failed to save business', error)
    toast.error(t('dashboard.saveFailed'))
  } finally {
    savingEdit.value = false
  }
}

async function addMenu() {
  if (!newMenuName.value.trim()) return
  try {
    const { data } = await api.post('/dashboard/menus', { name: newMenuName.value })
    business.value?.menus?.push(data.data)
    newItem.value[data.data.id] = { name: '', price: '' }
    newMenuName.value = ''
  } catch (error) {
    console.warn('Failed to add menu', error)
  }
}

async function addItem(menuId: number) {
  const draft = newItem.value[menuId]
  if (!draft?.name?.trim()) return
  try {
    const { data } = await api.post(`/dashboard/menus/${menuId}/items`, {
      name: draft.name,
      price: draft.price || null,
    })
    const menu = business.value?.menus?.find((m) => m.id === menuId)
    menu?.items.push(data)
    newItem.value[menuId] = { name: '', price: '' }
  } catch (error) {
    console.warn('Failed to add menu item', error)
  }
}

async function deleteItem(menuId: number, itemId: number) {
  try {
    await api.delete(`/dashboard/menu-items/${itemId}`)
    const menu = business.value?.menus?.find((m) => m.id === menuId)
    if (menu) menu.items = menu.items.filter((i) => i.id !== itemId)
  } catch (error) {
    console.warn('Failed to delete menu item', error)
  }
}

onMounted(loadAll)
</script>

<template>
  <div class="p-5 pb-28 safe-x">
    <div class="ec-gradient rounded-[1.75rem] p-5 mb-5 text-white shadow-lg">
      <p class="text-[10px] uppercase tracking-[.18em] opacity-70 mb-1">Business workspace</p>
      <h1 class="font-extrabold text-xl">{{ t('dashboard.title') }}</h1>
      <p class="text-xs opacity-75 mt-1">Manage your listing, content and performance.</p>
    </div>

    <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

    <template v-else-if="!business">
      <p class="text-sm mb-4" style="color: var(--color-muted)">{{ t('dashboard.noBusiness') }}</p>
      <form class="ec-card p-4 flex flex-col gap-3" @submit.prevent="createBusiness">
        <input v-model="form.name" required placeholder="Business name" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
        <select v-model="form.city_id" required class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none">
          <option value="" disabled>City</option>
          <option v-for="city in cities" :key="city.id" :value="city.id">{{ localizedName(city, locale) }}</option>
        </select>
        <select v-model="form.category_id" required class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none">
          <option value="" disabled>Category</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">{{ localizedName(category, locale) }}</option>
        </select>
        <textarea v-model="form.description" rows="3" placeholder="Description" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
        <button type="submit" class="ec-btn-primary py-3" :disabled="savingBusiness">{{ t('dashboard.createBusiness') }}</button>
      </form>
    </template>

    <template v-else>
      <div class="ec-card p-4 mb-4">
        <p class="font-semibold" style="color: var(--color-navy)">{{ business.name }}</p>
        <p class="text-xs mt-1" style="color: var(--color-muted)">Status: {{ stats?.status ?? business.status }}</p>
      </div>

      <div class="ec-card p-4 mb-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('dashboard.editBusiness') }}</h2>

        <div class="flex gap-4 mb-4">
          <label class="relative w-20 h-20 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden cursor-pointer shrink-0">
            <img v-if="editForm.logo" :src="editForm.logo" class="w-full h-full object-cover" />
            <Camera v-else :size="20" style="color: var(--color-muted)" />
            <span v-if="uploadingLogo" class="absolute inset-0 bg-white/70 flex items-center justify-center text-[10px]">…</span>
            <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" @change="onLogoSelected" />
          </label>
          <label class="relative flex-1 h-20 rounded-xl bg-gray-100 flex items-center justify-center overflow-hidden cursor-pointer">
            <img v-if="editForm.cover_image" :src="editForm.cover_image" class="w-full h-full object-cover" />
            <span v-else class="text-xs flex items-center gap-1" style="color: var(--color-muted)"><Camera :size="16" /> {{ t('dashboard.coverPhoto') }}</span>
            <span v-if="uploadingCover" class="absolute inset-0 bg-white/70 flex items-center justify-center text-[10px]">…</span>
            <input type="file" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" @change="onCoverSelected" />
          </label>
        </div>

        <div class="flex flex-col gap-2">
          <input v-model="editForm.name" placeholder="Business name" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
          <select v-model="editForm.city_id" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none">
            <option v-for="city in cities" :key="city.id" :value="city.id">{{ localizedName(city, locale) }}</option>
          </select>
          <select v-model="editForm.category_id" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none">
            <option v-for="category in categories" :key="category.id" :value="category.id">{{ localizedName(category, locale) }}</option>
          </select>
          <textarea v-model="editForm.description" rows="3" placeholder="Description" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
          <input v-model="editForm.address" placeholder="Address" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
          <input v-model="editForm.phone" placeholder="Phone" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
          <input v-model="editForm.whatsapp" placeholder="WhatsApp" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
          <input v-model="editForm.email" type="email" placeholder="Email" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
          <input v-model="editForm.website" placeholder="Website" class="border border-gray-100 rounded-xl px-3 py-2 text-sm outline-none" />
          <p class="text-xs" style="color: var(--color-muted)">{{ t('dashboard.editNotice') }}</p>
          <button class="ec-btn-primary py-3 mt-1" :disabled="savingEdit" @click="saveEdit">{{ t('common.save') }}</button>
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="ec-card p-3 text-center">
          <Eye :size="16" class="mx-auto mb-1" style="color: var(--color-teal)" />
          <p class="font-bold text-sm">{{ stats?.view_count ?? business.view_count }}</p>
          <p class="text-[10px]" style="color: var(--color-muted)">{{ t('dashboard.views') }}</p>
        </div>
        <div class="ec-card p-3 text-center">
          <Star :size="16" class="mx-auto mb-1" style="color: #f5a623" />
          <p class="font-bold text-sm">{{ (stats?.rating_avg ?? business.rating_avg).toFixed(1) }}</p>
          <p class="text-[10px]" style="color: var(--color-muted)">{{ t('dashboard.rating') }}</p>
        </div>
        <div class="ec-card p-3 text-center">
          <Heart :size="16" class="mx-auto mb-1" style="color: var(--color-orange)" />
          <p class="font-bold text-sm">{{ stats?.favorites_count ?? 0 }}</p>
          <p class="text-[10px]" style="color: var(--color-muted)">{{ t('dashboard.favorites') }}</p>
        </div>
      </div>

      <DashboardProjects
        v-if="business.category?.content_type === 'projects'"
        :business="business"
        :limit="stats?.listing_limit ?? null"
        class="mb-4"
      />
      <DashboardProducts
        v-if="business.category?.content_type === 'products'"
        :business="business"
        :limit="stats?.listing_limit ?? null"
        class="mb-4"
      />
      <DashboardServices
        v-if="business.category?.content_type === 'services'"
        :business="business"
        :limit="stats?.listing_limit ?? null"
        class="mb-4"
      />

      <DashboardSubscription :business="business" class="mb-4" />

      <div v-if="business.category?.content_type === 'none' || !business.category" class="ec-card p-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('dashboard.menuManager') }}</h2>

        <div v-for="menu in business.menus" :key="menu.id" class="mb-4 last:mb-0">
          <p class="text-xs font-semibold mb-2" style="color: var(--color-teal)">{{ menu.name }}</p>
          <div v-for="item in menu.items" :key="item.id" class="flex items-center justify-between text-sm py-1">
            <span style="color: var(--color-navy)">{{ item.name }} — €{{ Number(item.price ?? 0).toFixed(2) }}</span>
            <button @click="deleteItem(menu.id, item.id)"><Trash2 :size="14" class="text-red-400" /></button>
          </div>
          <div v-if="newItem[menu.id]" class="flex gap-2 mt-2">
            <input
              v-model="newItem[menu.id].name"
              placeholder="Item name"
              class="flex-1 border border-gray-100 rounded-lg px-2 py-1 text-xs outline-none"
            />
            <input
              v-model="newItem[menu.id].price"
              placeholder="Price"
              class="w-20 border border-gray-100 rounded-lg px-2 py-1 text-xs outline-none"
            />
            <button class="ec-btn-primary px-3" @click="addItem(menu.id)"><Plus :size="14" /></button>
          </div>
        </div>

        <div class="flex gap-2 mt-2">
          <input v-model="newMenuName" :placeholder="t('dashboard.addMenu')" class="flex-1 border border-gray-100 rounded-lg px-2 py-1 text-xs outline-none" />
          <button class="ec-btn-primary px-3 text-xs" @click="addMenu">{{ t('dashboard.addMenu') }}</button>
        </div>
      </div>
    </template>

    <BottomNav />
  </div>
</template>
