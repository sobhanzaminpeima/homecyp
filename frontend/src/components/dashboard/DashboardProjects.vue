<script setup lang="ts">
import { computed, reactive, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Plus, Trash2, Camera, X } from 'lucide-vue-next'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'
import type { Business, BusinessProject, PropertyType, ListingType } from '@/types'

const props = defineProps<{ business: Business; limit: number | null }>()

const { t } = useI18n()
const toast = useToast()

const showForm = ref(false)
const editingId = ref<number | null>(null)
const saving = ref(false)
const uploading = ref(false)

const emptyForm = () => ({
  title: '',
  description: '',
  images: [] as string[],
  property_type: 'apartment' as PropertyType,
  listing_type: 'sale' as ListingType,
  rental_period: '',
  available_from: '',
  price: '',
  currency: 'GBP',
  area_m2: '',
  bedrooms: '',
  bathrooms: '',
  max_guests: '',
  minimum_stay: '',
  amenities: '',
  floor: '',
  address: '',
  booking_url: '',
})

const form = reactive(emptyForm())

const projects = computed(() => props.business.projects ?? [])
const atLimit = computed(() => props.limit !== null && projects.value.length >= props.limit && editingId.value === null)

function openCreate() {
  Object.assign(form, emptyForm())
  editingId.value = null
  showForm.value = true
}

function openEdit(project: BusinessProject) {
  Object.assign(form, {
    title: project.title,
    description: project.description ?? '',
    images: [...project.images],
    property_type: project.property_type,
    listing_type: project.listing_type,
    rental_period: project.rental_period ?? '',
    available_from: project.available_from ?? '',
    price: project.price?.toString() ?? '',
    currency: project.currency,
    area_m2: project.area_m2?.toString() ?? '',
    bedrooms: project.bedrooms?.toString() ?? '',
    bathrooms: project.bathrooms?.toString() ?? '',
    max_guests: project.max_guests?.toString() ?? '',
    minimum_stay: project.minimum_stay?.toString() ?? '',
    amenities: project.amenities.join(', '),
    floor: project.floor ?? '',
    address: project.address ?? '',
    booking_url: project.booking_url ?? '',
  })
  editingId.value = project.id
  showForm.value = true
}

function closeForm() {
  showForm.value = false
}

async function onImagesSelected(event: Event) {
  const files = Array.from((event.target as HTMLInputElement).files ?? [])
  const remaining = 5 - form.images.length
  if (remaining <= 0) return

  uploading.value = true
  for (const file of files.slice(0, remaining)) {
    const body = new FormData()
    body.append('file', file)
    try {
      const { data } = await api.post('/uploads', body, { headers: { 'Content-Type': 'multipart/form-data' } })
      form.images.push(data.url)
    } catch (error) {
      console.warn('Failed to upload project image', error)
      toast.error(t('dashboard.uploadFailed'))
    }
  }
  uploading.value = false
  ;(event.target as HTMLInputElement).value = ''
}

function removeImage(index: number) {
  form.images.splice(index, 1)
}

async function save() {
  if (!form.title.trim()) return

  saving.value = true
  try {
    const payload = {
      title: form.title,
      description: form.description || null,
      images: form.images,
      property_type: form.property_type,
      listing_type: form.listing_type,
      rental_period: form.listing_type === 'rent' ? form.rental_period || null : null,
      available_from: form.available_from || null,
      price: form.price || null,
      currency: form.currency,
      area_m2: form.area_m2 || null,
      bedrooms: form.bedrooms || null,
      bathrooms: form.bathrooms || null,
      max_guests: form.max_guests || null,
      minimum_stay: form.minimum_stay || null,
      amenities: form.amenities.split(',').map((x) => x.trim()).filter(Boolean),
      floor: form.floor || null,
      address: form.address || null,
      booking_url: form.booking_url || null,
    }

    if (editingId.value) {
      const { data } = await api.put(`/dashboard/projects/${editingId.value}`, payload)
      const index = projects.value.findIndex((p) => p.id === editingId.value)
      if (index !== -1) projects.value[index] = data.data
    } else {
      const { data } = await api.post('/dashboard/projects', payload)
      projects.value.unshift(data.data)
    }

    toast.success(t('dashboard.projectSaved'))
    closeForm()
  } catch (error: any) {
    console.warn('Failed to save project', error)
    toast.error(error?.response?.data?.errors?.limit?.[0] || t('dashboard.projectSaveFailed'))
  } finally {
    saving.value = false
  }
}

async function remove(project: BusinessProject) {
  if (!confirm(t('dashboard.confirmDeleteProject'))) return
  try {
    await api.delete(`/dashboard/projects/${project.id}`)
    const index = projects.value.findIndex((p) => p.id === project.id)
    if (index !== -1) projects.value.splice(index, 1)
    toast.success(t('dashboard.projectDeleted'))
  } catch (error) {
    console.warn('Failed to delete project', error)
    toast.error(t('dashboard.projectSaveFailed'))
  }
}

const statusStyles: Record<string, string> = {
  approved: 'bg-green-100 text-green-700',
  pending: 'bg-yellow-100 text-yellow-700',
  rejected: 'bg-red-100 text-red-700',
}
</script>

<template>
  <div class="ec-card p-4">
    <div class="flex items-center justify-between mb-1">
      <h2 class="font-semibold text-sm" style="color: var(--color-navy)">{{ t('dashboard.projects') }}</h2>
      <span class="text-xs" style="color: var(--color-muted)">
        {{ projects.length }}/{{ limit === null ? '∞' : limit }}
      </span>
    </div>
    <p class="text-xs mb-3" style="color: var(--color-muted)">{{ t('dashboard.projectsHint') }}</p>

    <div v-for="project in projects" :key="project.id" class="flex items-center gap-3 py-2 border-b border-gray-50 last:border-0">
      <img
        v-if="project.images[0]"
        :src="project.images[0]"
        class="w-14 h-14 rounded-lg object-cover shrink-0"
      />
      <div v-else class="w-14 h-14 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
        <Camera :size="18" style="color: var(--color-muted)" />
      </div>
      <div class="min-w-0 flex-1">
        <p class="text-sm font-medium truncate" style="color: var(--color-navy)">{{ project.title }}</p>
        <div class="flex items-center gap-2 mt-0.5">
          <span class="text-xs px-2 py-0.5 rounded-full" :class="statusStyles[project.status]">{{ project.status }}</span>
          <span v-if="project.price" class="text-xs" style="color: var(--color-muted)">{{ project.currency }} {{ Number(project.price).toLocaleString() }}</span>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button class="text-xs px-2 py-1 rounded-full bg-gray-100" @click="openEdit(project)">{{ t('common.edit') }}</button>
        <button @click="remove(project)"><Trash2 :size="14" class="text-red-400" /></button>
      </div>
    </div>

    <p v-if="!projects.length" class="text-xs py-2" style="color: var(--color-muted)">{{ t('dashboard.noProjectsYet') }}</p>

    <button
      class="ec-btn-primary text-xs px-4 py-2 mt-3 flex items-center gap-1"
      :disabled="atLimit"
      @click="openCreate"
    >
      <Plus :size="14" /> {{ t('dashboard.addProject') }}
    </button>
    <p v-if="atLimit" class="text-xs mt-2" style="color: var(--color-orange-dark)">{{ t('dashboard.limitReached') }}</p>

    <div v-if="showForm" class="fixed inset-0 bg-black/40 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4" @click.self="closeForm">
      <div class="bg-white w-full sm:max-w-md sm:rounded-2xl rounded-t-2xl p-5 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-bold text-sm" style="color: var(--color-navy)">{{ editingId ? t('dashboard.editProject') : t('dashboard.addProject') }}</h3>
          <button @click="closeForm"><X :size="18" /></button>
        </div>

        <div class="flex flex-col gap-3 text-sm">
          <div class="flex gap-2 flex-wrap">
            <div v-for="(img, i) in form.images" :key="img" class="relative w-16 h-16">
              <img :src="img" class="w-16 h-16 rounded-lg object-cover" />
              <button class="absolute -top-1 -right-1 bg-white rounded-full shadow p-0.5" @click="removeImage(i)">
                <X :size="12" />
              </button>
            </div>
            <label v-if="form.images.length < 5" class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center cursor-pointer">
              <Camera :size="18" style="color: var(--color-muted)" />
              <input type="file" multiple accept="image/png,image/jpeg,image/webp,image/gif" class="hidden" @change="onImagesSelected" />
            </label>
          </div>

          <p class="text-[11px]" style="color: var(--color-muted)">{{ t('dashboard.upTo5Images') }}</p>

          <input v-model="form.title" :placeholder="t('dashboard.projectTitle')" class="border border-gray-200 rounded-lg px-3 py-2" />
          <textarea v-model="form.description" rows="3" :placeholder="t('common.description')" class="border border-gray-200 rounded-lg px-3 py-2" />

          <div class="grid grid-cols-2 gap-2">
            <select v-model="form.property_type" class="border border-gray-200 rounded-lg px-2 py-2 text-xs">
              <option value="apartment">{{ t('project.apartment') }}</option>
              <option value="villa">{{ t('project.villa') }}</option>
              <option value="land">{{ t('project.land') }}</option>
              <option value="commercial">{{ t('project.commercial') }}</option>
              <option value="other">{{ t('project.other') }}</option>
            </select>
            <select v-model="form.listing_type" class="border border-gray-200 rounded-lg px-2 py-2 text-xs">
              <option value="sale">{{ t('project.forSale') }}</option>
              <option value="rent">{{ t('project.forRent') }}</option>
              <option value="presale">{{ t('project.presale') }}</option>
            </select>
          </div>

          <div v-if="form.listing_type === 'rent'" class="grid grid-cols-2 gap-2">
            <select v-model="form.rental_period" class="border border-gray-200 rounded-lg px-2 py-2 text-xs">
              <option value="">Rental period</option><option value="daily">Daily / holiday stay</option><option value="monthly">Monthly</option><option value="long_term">Long term</option>
            </select>
            <input v-model="form.available_from" type="date" class="border border-gray-200 rounded-lg px-2 py-2 text-xs" />
          </div>

          <div class="grid grid-cols-2 gap-2">
            <input v-model="form.price" type="number" min="0" :placeholder="t('project.price')" class="border border-gray-200 rounded-lg px-3 py-2" />
            <select v-model="form.currency" class="border border-gray-200 rounded-lg px-2 py-2 text-xs">
              <option value="GBP">GBP</option>
              <option value="EUR">EUR</option>
              <option value="USD">USD</option>
              <option value="TRY">TRY</option>
            </select>
          </div>

          <div class="grid grid-cols-3 gap-2">
            <input v-model="form.area_m2" type="number" min="0" :placeholder="t('project.areaM2')" class="border border-gray-200 rounded-lg px-2 py-2 text-xs" />
            <input v-model="form.bedrooms" type="number" min="0" :placeholder="t('project.bedrooms')" class="border border-gray-200 rounded-lg px-2 py-2 text-xs" />
            <input v-model="form.bathrooms" type="number" min="0" :placeholder="t('project.bathrooms')" class="border border-gray-200 rounded-lg px-2 py-2 text-xs" />
          </div>

          <div v-if="form.listing_type === 'rent'" class="grid grid-cols-2 gap-2">
            <input v-model="form.max_guests" type="number" min="1" placeholder="Max guests" class="border border-gray-200 rounded-lg px-2 py-2 text-xs" />
            <input v-model="form.minimum_stay" type="number" min="1" placeholder="Minimum stay (days)" class="border border-gray-200 rounded-lg px-2 py-2 text-xs" />
          </div>
          <input v-model="form.amenities" placeholder="Amenities, comma separated" class="border border-gray-200 rounded-lg px-3 py-2" />

          <input v-model="form.floor" :placeholder="t('project.floor')" class="border border-gray-200 rounded-lg px-3 py-2" />
          <input v-model="form.address" :placeholder="t('common.address')" class="border border-gray-200 rounded-lg px-3 py-2" />
          <input v-model="form.booking_url" type="url" placeholder="Booking URL (optional)" class="border border-gray-200 rounded-lg px-3 py-2" />

          <p class="text-xs" style="color: var(--color-muted)">{{ t('dashboard.editNotice') }}</p>
        </div>

        <div class="flex justify-end gap-2 mt-4">
          <button class="text-sm px-4 py-2 rounded-lg bg-gray-100" @click="closeForm">{{ t('common.cancel') }}</button>
          <button class="ec-btn-primary px-4 py-2 text-sm" :disabled="saving || uploading" @click="save">{{ t('common.save') }}</button>
        </div>
      </div>
    </div>
  </div>
</template>
