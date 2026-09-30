<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Search, ArrowLeft, MapPin, LocateFixed, List, Map as MapIcon } from 'lucide-vue-next'
import api from '@/lib/api'
import { useCityStore } from '@/stores/city'
import { localizedName } from '@/i18n'
import { useSmartBack } from '@/composables/useSmartBack'
import { useGeolocation } from '@/composables/useGeolocation'
import { useToast } from '@/composables/useToast'
import BusinessCard from '@/components/BusinessCard.vue'
import BusinessSlider from '@/components/BusinessSlider.vue'
import CategoryIcon from '@/components/CategoryIcon.vue'
import AdSlider from '@/components/AdSlider.vue'
import BottomNav from '@/components/BottomNav.vue'
import EmptyState from '@/components/EmptyState.vue'
import BusinessCardSkeleton from '@/components/skeletons/BusinessCardSkeleton.vue'
import ProjectCard from '@/components/ProjectCard.vue'
import BusinessMap from '@/components/BusinessMap.vue'
import type { Business, BusinessProject, Category, City, Advertisement, ListingType } from '@/types'

const { t, locale } = useI18n()
const route = useRoute()
const cityStore = useCityStore()
const goBack = useSmartBack('/home')
const geo = useGeolocation()
const toast = useToast()

const query = ref((route.query.q as string) ?? '')
const activeCategory = ref((route.query.category as string) ?? '')
const showCityPicker = ref(false)
const viewMode = ref<'list' | 'map'>('list')
const sortNearMe = ref(false)
const openNow = ref(false)

const categories = ref<Category[]>([])
const leafCategories = computed<Category[]>(() =>
  categories.value.flatMap((category) => (category.children.length ? category.children : [category])),
)
const cities = ref<City[]>([])
const results = ref<Business[]>([])
const loading = ref(true)

const activeCategoryObj = computed(() => leafCategories.value.find((c) => c.slug === activeCategory.value) ?? null)
const isProjectsCategory = computed(() => activeCategoryObj.value?.content_type === 'projects')

const projectResults = ref<BusinessProject[]>([])
const loadingProjects = ref(false)
const projectFilters = ref({ listing_type: '' as ListingType | '', min_price: '', max_price: '', min_bedrooms: '' })

async function searchProjects() {
  loadingProjects.value = true
  try {
    const { data } = await api.get('/projects', {
      params: {
        city: cityStore.city?.slug,
        listing_type: projectFilters.value.listing_type || undefined,
        min_price: projectFilters.value.min_price || undefined,
        max_price: projectFilters.value.max_price || undefined,
        min_bedrooms: projectFilters.value.min_bedrooms || undefined,
      },
    })
    projectResults.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to search projects', error)
    projectResults.value = []
  } finally {
    loadingProjects.value = false
  }
}

const featured = ref<Business[]>([])
const ads = ref<Advertisement[]>([])
const showDiscovery = computed(() => !query.value && !activeCategory.value)

async function loadFilters() {
  try {
    const [catRes, cityRes] = await Promise.all([api.get('/categories'), api.get('/cities')])
    categories.value = catRes.data.data ?? []
    cities.value = cityRes.data.data ?? []
    cityStore.syncFromList(cities.value)
  } catch (error) {
    console.warn('Failed to load filters', error)
  }
}

async function loadDiscovery() {
  try {
    const { data } = await api.get('/home', { params: { city: cityStore.city?.slug } })
    featured.value = data.featured ?? []
    ads.value = data.ads ?? []
  } catch (error) {
    console.warn('Failed to load discovery content', error)
  }
}

async function search() {
  loading.value = true
  try {
    const { data } = await api.get('/businesses', {
      params: {
        q: query.value || undefined,
        category: activeCategory.value || undefined,
        city: cityStore.city?.slug,
        sort: sortNearMe.value && geo.coords.value ? 'distance' : undefined,
        lat: sortNearMe.value ? geo.coords.value?.lat : undefined,
        lng: sortNearMe.value ? geo.coords.value?.lng : undefined,
        open_now: openNow.value || undefined,
      },
    })
    results.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to search businesses', error)
    results.value = []
  } finally {
    loading.value = false
  }
}

async function toggleNearMe() {
  if (sortNearMe.value) {
    sortNearMe.value = false
    search()
    return
  }

  const coords = geo.coords.value ?? (await geo.request())
  if (!coords) {
    toast.error(t('explore.locationDenied'))
    return
  }

  sortNearMe.value = true
  search()
}

function toggleCategory(slug: string) {
  activeCategory.value = activeCategory.value === slug ? '' : slug
  if (isProjectsCategory.value) {
    searchProjects()
  } else {
    search()
  }
}

function pickCity(city: City) {
  cityStore.setCity(city)
  showCityPicker.value = false
  if (isProjectsCategory.value) {
    searchProjects()
  } else {
    search()
  }
  loadDiscovery()
}

watch(query, () => {
  if (!isProjectsCategory.value) search()
})

onMounted(async () => {
  await loadFilters()
  if (isProjectsCategory.value) {
    searchProjects()
  } else {
    search()
  }
  loadDiscovery()
})
</script>

<template>
  <div class="pb-24 safe-x">
    <header class="pt-6 pb-3 flex items-center gap-3">
      <button @click="goBack"><ArrowLeft :size="20" /></button>
      <h1 class="font-bold text-base" style="color: var(--color-navy)">{{ t('explore.title') }}</h1>
    </header>

    <div class="ec-card flex items-center gap-2 px-4 py-3 mb-3">
      <Search :size="18" style="color: var(--color-muted)" />
      <input v-model="query" type="text" class="flex-1 text-sm outline-none" :placeholder="t('home.searchPlaceholder')" />
    </div>

    <button
      class="flex items-center gap-1 text-xs mb-3"
      style="color: var(--color-teal)"
      @click="showCityPicker = !showCityPicker"
    >
      <MapPin :size="14" />
      {{ cityStore.city ? localizedName(cityStore.city, locale) : t('explore.chooseCity') }}
    </button>

    <button class="inline-flex items-center gap-1 text-xs mb-3 ml-2 px-3 py-2 rounded-xl border" :class="openNow ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-white text-slate-500 border-slate-100'" @click="openNow=!openNow; search()">
      <span class="h-2 w-2 rounded-full" :class="openNow?'bg-emerald-500':'bg-slate-300'"></span> Open now
    </button>

    <div v-if="showCityPicker" class="ec-card p-3 mb-3 grid grid-cols-2 gap-2">
      <button
        v-for="city in cities"
        :key="city.id"
        class="text-sm py-2 rounded-lg"
        :class="cityStore.city?.id === city.id ? 'ec-btn-primary' : 'bg-gray-50'"
        @click="pickCity(city)"
      >
        {{ localizedName(city, locale) }}
      </button>
    </div>

    <div v-if="!isProjectsCategory" class="flex items-center gap-2 mb-3">
      <button
        class="flex items-center gap-1 px-3 py-1.5 rounded-full text-xs"
        :class="sortNearMe ? 'ec-btn-primary' : 'bg-white border border-gray-100'"
        :disabled="geo.loading.value"
        @click="toggleNearMe"
      >
        <LocateFixed :size="13" /> {{ geo.loading.value ? t('common.loading') : t('explore.nearMe') }}
      </button>
      <div class="flex items-center rounded-full border border-gray-100 overflow-hidden ml-auto">
        <button
          class="px-3 py-1.5 text-xs flex items-center gap-1"
          :class="viewMode === 'list' ? 'ec-btn-primary' : 'bg-white'"
          @click="viewMode = 'list'"
        >
          <List :size="13" /> {{ t('explore.listView') }}
        </button>
        <button
          class="px-3 py-1.5 text-xs flex items-center gap-1"
          :class="viewMode === 'map' ? 'ec-btn-primary' : 'bg-white'"
          @click="viewMode = 'map'"
        >
          <MapIcon :size="13" /> {{ t('explore.mapView') }}
        </button>
      </div>
    </div>

    <div class="flex gap-2 overflow-x-auto pb-1 mb-4">
      <button
        v-for="category in leafCategories"
        :key="category.id"
        class="shrink-0 flex items-center gap-1 px-3 py-2 rounded-full text-xs"
        :class="activeCategory === category.slug ? 'ec-btn-primary' : 'bg-white border border-gray-100'"
        @click="toggleCategory(category.slug)"
      >
        <CategoryIcon :icon="category.icon" :size="14" />
        {{ localizedName(category, locale) }}
      </button>
    </div>

    <template v-if="showDiscovery">
      <AdSlider v-if="ads.length" :ads="ads" />

      <section v-if="featured.length" class="mt-4 mb-2">
        <h2 class="font-bold text-sm mb-3" style="color: var(--color-navy)">{{ t('home.featured') }}</h2>
        <BusinessSlider :businesses="featured" />
      </section>
    </template>

    <template v-if="isProjectsCategory">
      <div class="ec-card p-3 mb-4 flex flex-col gap-2">
        <p class="text-xs font-semibold" style="color: var(--color-navy)">{{ t('project.filters') }}</p>
        <div class="grid grid-cols-2 gap-2">
          <select v-model="projectFilters.listing_type" class="border border-gray-100 rounded-lg px-2 py-2 text-xs" @change="searchProjects">
            <option value="">{{ t('project.anyListingType') }}</option>
            <option value="sale">{{ t('project.forSale') }}</option>
            <option value="rent">{{ t('project.forRent') }}</option>
            <option value="presale">{{ t('project.presale') }}</option>
          </select>
          <input
            v-model="projectFilters.min_bedrooms"
            type="number"
            min="0"
            :placeholder="t('project.minBedrooms')"
            class="border border-gray-100 rounded-lg px-2 py-2 text-xs"
            @change="searchProjects"
          />
          <input
            v-model="projectFilters.min_price"
            type="number"
            min="0"
            :placeholder="t('project.minPrice')"
            class="border border-gray-100 rounded-lg px-2 py-2 text-xs"
            @change="searchProjects"
          />
          <input
            v-model="projectFilters.max_price"
            type="number"
            min="0"
            :placeholder="t('project.maxPrice')"
            class="border border-gray-100 rounded-lg px-2 py-2 text-xs"
            @change="searchProjects"
          />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <template v-if="loadingProjects">
          <BusinessCardSkeleton v-for="n in 4" :key="n" />
        </template>
        <EmptyState v-else-if="!projectResults.length" :message="t('project.noProjects')" />
        <ProjectCard v-for="p in projectResults" :key="p.id" :project="p" />
      </div>
    </template>

    <div v-else-if="viewMode === 'map'" class="mt-4" style="height: 60vh">
      <BusinessMap :businesses="results" :center="geo.coords.value" />
    </div>

    <div v-else class="flex flex-col gap-3 mt-4">
      <template v-if="loading">
        <BusinessCardSkeleton v-for="n in 6" :key="n" />
      </template>
      <EmptyState v-else-if="!results.length" :message="t('explore.noResults')" />
      <BusinessCard v-for="business in results" :key="business.id" :business="business" />
    </div>

    <BottomNav />
  </div>
</template>
