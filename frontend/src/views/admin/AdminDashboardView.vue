<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Store, FolderTree, MapPin, Megaphone, Package, Users, Settings } from 'lucide-vue-next'
import api from '@/lib/api'

const { t } = useI18n()

const stats = ref<Record<string, number> | null>(null)
const loading = ref(true)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/dashboard')
    stats.value = data
  } catch (error) {
    console.warn('Failed to load admin dashboard', error)
  } finally {
    loading.value = false
  }
}

onMounted(load)

const cards: Array<{ key: string; label: string; to?: string }> = [
  { key: 'businesses_total', label: 'Total businesses', to: '/admin/businesses' },
  { key: 'businesses_pending', label: 'Pending approval', to: '/admin/businesses' },
  { key: 'businesses_approved', label: 'Approved', to: '/admin/businesses' },
  { key: 'users_total', label: 'Users', to: '/admin/users' },
  { key: 'business_owners_total', label: 'Business owners', to: '/admin/users' },
  { key: 'reviews_total', label: 'Reviews' },
  { key: 'views_total', label: 'Total views' },
]

const managementLinks = [
  { to: '/admin/businesses', label: () => t('admin.businesses'), icon: Store, desc: 'Approve, edit, feature, and remove listings' },
  { to: '/admin/categories', label: () => t('admin.categories'), icon: FolderTree, desc: 'Manage the category taxonomy' },
  { to: '/admin/cities', label: () => t('admin.cities'), icon: MapPin, desc: 'Manage covered cities' },
  { to: '/admin/advertisements', label: () => t('admin.advertisements'), icon: Megaphone, desc: 'Home/Explore ad slider' },
  { to: '/admin/packages', label: () => t('admin.packages'), icon: Package, desc: 'Subscription packages for owners' },
  { to: '/admin/users', label: () => t('admin.users'), icon: Users, desc: 'All app users, owners, and admins' },
  { to: '/admin/settings', label: () => t('admin.settings'), icon: Settings, desc: 'Global app settings' },
]
</script>

<template>
  <h1 class="font-bold text-xl mb-6" style="color: var(--color-navy)">{{ t('admin.dashboard') }}</h1>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <template v-else>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <component
        :is="card.to ? 'RouterLink' : 'div'"
        v-for="card in cards"
        :key="card.key"
        :to="card.to"
        class="bg-white rounded-2xl p-5 shadow-sm transition-transform"
        :class="card.to ? 'hover:-translate-y-0.5 hover:shadow-md cursor-pointer' : ''"
      >
        <p class="text-2xl font-bold" style="color: var(--color-teal)">{{ stats?.[card.key] ?? 0 }}</p>
        <p class="text-xs mt-1" style="color: var(--color-muted)">{{ card.label }}</p>
      </component>
    </div>

    <h2 class="font-bold text-sm mb-3" style="color: var(--color-navy)">Manage the app</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <RouterLink
        v-for="link in managementLinks"
        :key="link.to"
        :to="link.to"
        class="bg-white rounded-2xl p-4 shadow-sm flex items-start gap-3 transition-transform hover:-translate-y-0.5 hover:shadow-md"
      >
        <div class="h-10 w-10 rounded-xl flex items-center justify-center shrink-0" style="background: var(--color-teal); background: color-mix(in srgb, var(--color-teal) 12%, white)">
          <component :is="link.icon" :size="18" style="color: var(--color-teal)" />
        </div>
        <div class="min-w-0">
          <p class="font-semibold text-sm" style="color: var(--color-navy)">{{ link.label() }}</p>
          <p class="text-xs mt-0.5" style="color: var(--color-muted)">{{ link.desc }}</p>
        </div>
      </RouterLink>
    </div>
  </template>
</template>
