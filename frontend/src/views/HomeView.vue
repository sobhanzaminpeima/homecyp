<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Search, ChevronRight, Globe, Eye } from 'lucide-vue-next'
import api from '@/lib/api'
import { useCityStore } from '@/stores/city'
import { SUPPORTED_LOCALES, applyDirection, persistLocale, localizedName, type SupportedLocale } from '@/i18n'
import EcLogo from '@/components/EcLogo.vue'
import InstallMenu from '@/components/InstallMenu.vue'
import CategoryIcon from '@/components/CategoryIcon.vue'
import BusinessCard from '@/components/BusinessCard.vue'
import BusinessSlider from '@/components/BusinessSlider.vue'
import AdSlider from '@/components/AdSlider.vue'
import BottomNav from '@/components/BottomNav.vue'
import EmptyState from '@/components/EmptyState.vue'
import BusinessCardSkeleton from '@/components/skeletons/BusinessCardSkeleton.vue'
import CategorySkeleton from '@/components/skeletons/CategorySkeleton.vue'
import type { Business, Category, Advertisement, City } from '@/types'

const { t, locale } = useI18n()
const router = useRouter()
const cityStore = useCityStore()

const searchQuery = ref('')
const showLangMenu = ref(false)

const categories = ref<Category[]>([])
const leafCategories = computed<Category[]>(() =>
  categories.value.flatMap((category) => (category.children.length ? category.children : [category])),
)
const featured = ref<Business[]>([])
const popular = ref<Business[]>([])
const ads = ref<Advertisement[]>([])
const viewCounter = ref<number | null>(null)
const counterLabel = ref('')

const loadingHome = ref(true)
const loadingCategories = ref(true)

const cities = ref<City[]>([])
const pendingCategory = ref<Category | null>(null)

const cityName = computed(() => (cityStore.city ? localizedName(cityStore.city, locale.value) : ''))

async function loadHome() {
  loadingHome.value = true
  try {
    const { data } = await api.get('/home', { params: { city: cityStore.city?.slug } })
    featured.value = data.featured ?? []
    popular.value = data.popular ?? []
    ads.value = data.ads ?? []
    viewCounter.value = data.view_counter ?? 0
    counterLabel.value = data.counter_label ?? ''
  } catch (error) {
    console.warn('Failed to load home data', error)
  } finally {
    loadingHome.value = false
  }
}

async function loadCategories() {
  loadingCategories.value = true
  try {
    const { data } = await api.get('/categories')
    categories.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load categories', error)
  } finally {
    loadingCategories.value = false
  }
}

async function loadCities() {
  try {
    const { data } = await api.get('/cities')
    cities.value = data.data ?? []
    cityStore.syncFromList(cities.value)
  } catch (error) {
    console.warn('Failed to load cities', error)
  }
}

function submitSearch() {
  router.push({ path: '/explore', query: searchQuery.value ? { q: searchQuery.value } : {} })
}

function selectCategory(category: Category) {
  pendingCategory.value = category
}

function closeCityPicker() {
  pendingCategory.value = null
}

function chooseCityForCategory(city: City) {
  cityStore.setCity(city)
  const category = pendingCategory.value
  pendingCategory.value = null
  if (category) {
    router.push({ path: '/explore', query: { category: category.slug } })
  }
}

function changeLocale(l: SupportedLocale) {
  locale.value = l
  persistLocale(l)
  applyDirection(l)
  showLangMenu.value = false
}

const CATEGORY_TINTS = [
  { bg: '#e4f2f1', fg: 'var(--color-teal)' },
  { bg: '#fdecd8', fg: 'var(--color-orange-dark)' },
  { bg: '#e7edf7', fg: '#3a5a9c' },
  { bg: '#fbe8ea', fg: '#c2455a' },
  { bg: '#eaf3e2', fg: '#4c8a3e' },
  { bg: '#f3e9fb', fg: '#7c4fc9' },
]

function categoryTint(index: number) {
  return CATEGORY_TINTS[index % CATEGORY_TINTS.length]
}

onMounted(() => {
  loadHome()
  loadCategories()
  loadCities()
})
</script>

<template>
  <div class="pb-28">
    <header class="home-hero text-white pt-6 pb-9 safe-x rounded-b-[2.4rem] relative overflow-hidden">
      <div class="pointer-events-none absolute -top-20 -right-16 w-60 h-60 rounded-full border border-white/10"></div>
      <div class="pointer-events-none absolute -bottom-24 -left-16 w-56 h-56 rounded-full bg-white/[.06]"></div>

      <div class="relative flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="h-11 w-11 rounded-2xl bg-white shadow-lg shadow-black/10 flex items-center justify-center">
            <EcLogo :size="34" />
          </div>
          <div class="leading-tight">
            <div class="text-[10px] uppercase tracking-[.2em] opacity-70 mb-1">Explore · Connect · Enjoy</div>
            <div class="text-sm font-bold">{{ cityName }}</div>
            <RouterLink to="/city" class="text-[10px] opacity-75">{{ t('common.changeCity') }} →</RouterLink>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <InstallMenu />
          <div class="relative">
            <button class="ec-icon-button" aria-label="Change language" @click="showLangMenu = !showLangMenu">
              <Globe :size="17" />
            </button>
            <div v-if="showLangMenu" class="absolute right-0 mt-2 bg-white rounded-xl shadow-lg overflow-hidden z-40 w-28">
              <button
                v-for="l in SUPPORTED_LOCALES"
                :key="l"
                class="block w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50"
                @click="changeLocale(l)"
              >
                {{ l.toUpperCase() }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="ads.length" class="relative mt-5 -mx-4">
        <AdSlider :ads="ads" />
      </div>

      <div class="relative mt-4 flex items-stretch gap-2.5">
        <div class="flex-1 flex items-center gap-2.5 bg-white/10 border border-white/10 backdrop-blur-sm rounded-2xl px-3.5 py-2.5 min-w-0">
          <div class="h-8 w-8 rounded-xl bg-white/12 flex items-center justify-center shrink-0">
            <Eye :size="15" />
          </div>
          <div class="leading-tight min-w-0">
            <span class="text-[15px] font-extrabold block tracking-tight">{{ viewCounter !== null ? viewCounter.toLocaleString() : '—' }}</span>
            <span class="text-[10px] opacity-80 truncate block">{{ counterLabel || t('home.exploredCounter') }}</span>
          </div>
        </div>
        <RouterLink to="/dashboard" class="ec-btn-accent px-4 py-2.5 text-xs whitespace-nowrap shrink-0 flex items-center">
          {{ t('home.listBusiness') }}
        </RouterLink>
      </div>

      <form class="relative mt-4" @submit.prevent="submitSearch">
        <Search :size="19" class="absolute left-4 top-1/2 -translate-y-1/2" style="color: var(--color-teal)" />
        <input
          v-model="searchQuery"
          type="search"
          class="w-full h-13 rounded-2xl pl-11 pr-4 text-sm text-slate-800 outline-none shadow-xl shadow-black/10"
          :placeholder="t('home.searchPlaceholder')"
        />
      </form>
    </header>

    <section class="mt-7 px-5">
      <div class="flex items-end justify-between mb-4">
        <h2 class="ec-section-title">{{ t('home.categories') }}</h2>
        <span class="text-[10px] font-semibold uppercase tracking-[.12em]" style="color: var(--color-muted)">{{ leafCategories.length }} services</span>
      </div>
      <div v-if="loadingCategories" class="grid grid-cols-4 gap-3">
        <CategorySkeleton v-for="n in 8" :key="n" />
      </div>
      <EmptyState v-else-if="!leafCategories.length" :message="t('home.noCategories')" />
      <div v-else class="grid grid-cols-4 gap-x-3 gap-y-4">
        <button
          v-for="(category, index) in leafCategories"
          :key="category.id"
          class="flex flex-col items-center gap-2 group"
          @click="selectCategory(category)"
        >
          <div
            class="h-14 w-14 rounded-[1.15rem] flex items-center justify-center transition-all active:scale-95 shadow-sm ring-1 ring-black/[.03] group-hover:-translate-y-0.5"
            :style="{ background: categoryTint(index).bg }"
          >
            <CategoryIcon :icon="category.icon" :style="{ color: categoryTint(index).fg }" />
          </div>
          <span class="text-[10px] font-semibold text-center truncate w-[4.35rem]" style="color: var(--color-navy)">{{ localizedName(category, locale) }}</span>
        </button>
      </div>
    </section>

    <section class="mt-8">
      <div class="flex items-center justify-between px-5 mb-4">
        <h2 class="ec-section-title">{{ t('home.featured') }}</h2>
        <RouterLink to="/explore" class="text-xs font-semibold flex items-center gap-0.5" style="color: var(--color-teal)">
          {{ t('common.seeAll') }} <ChevronRight :size="14" />
        </RouterLink>
      </div>
      <div v-if="loadingHome" class="px-5 flex gap-3">
        <BusinessCardSkeleton horizontal />
        <BusinessCardSkeleton horizontal />
      </div>
      <EmptyState v-else-if="!featured.length" :message="t('home.noFeatured')" />
      <div v-else class="px-5">
        <BusinessSlider :businesses="featured" />
      </div>
    </section>

    <section class="mt-8 px-5 flex flex-col gap-3.5">
      <h2 class="ec-section-title mb-0.5">{{ t('home.popular') }}</h2>
      <template v-if="loadingHome">
        <BusinessCardSkeleton v-for="n in 4" :key="n" />
      </template>
      <EmptyState v-else-if="!popular.length" :message="t('home.noPopular')" />
      <BusinessCard v-for="business in popular" :key="business.id" :business="business" />
    </section>

    <BottomNav />

    <Teleport to="body">
      <div
        v-if="pendingCategory"
        class="fixed inset-0 bg-slate-950/45 backdrop-blur-sm z-50 flex items-end justify-center"
        @click.self="closeCityPicker"
      >
        <div class="bg-white w-full max-w-[520px] rounded-t-[2rem] p-5 pb-8 safe-x shadow-2xl">
          <div class="w-10 h-1 rounded-full bg-slate-200 mx-auto mb-5"></div>
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-base" style="color: var(--color-navy)">
              {{ localizedName(pendingCategory, locale) }} — {{ t('explore.chooseCity') }}
            </h3>
            <button class="text-xs" style="color: var(--color-muted)" @click="closeCityPicker">
              {{ t('common.close') }}
            </button>
          </div>
          <div class="grid grid-cols-2 gap-2 max-h-80 overflow-y-auto">
            <button
              v-for="city in cities"
              :key="city.id"
              class="text-sm py-2.5 rounded-xl"
              :class="cityStore.city?.id === city.id ? 'ec-btn-primary' : 'bg-gray-50'"
              @click="chooseCityForCategory(city)"
            >
              {{ localizedName(city, locale) }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
