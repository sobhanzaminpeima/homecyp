<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  LayoutDashboard,
  Store,
  FolderTree,
  MapPin,
  Megaphone,
  Package,
  Users,
  Star,
  Building2,
  CreditCard,
  Settings,
  LogOut,
  Menu as MenuIcon,
  X,
  Sparkles,
} from 'lucide-vue-next'
import EcLogo from '@/components/EcLogo.vue'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const router = useRouter()
const authStore = useAuthStore()

const mobileOpen = ref(false)

const links = [
  { to: '/admin/dashboard', label: () => t('admin.dashboard'), icon: LayoutDashboard },
  { to: '/admin/businesses', label: () => t('admin.businesses'), icon: Store },
  { to: '/admin/reviews', label: () => t('admin.reviews'), icon: Star },
  { to: '/admin/projects', label: () => t('admin.projects'), icon: Building2 },
  { to: '/admin/categories', label: () => t('admin.categories'), icon: FolderTree },
  { to: '/admin/cities', label: () => t('admin.cities'), icon: MapPin },
  { to: '/admin/advertisements', label: () => t('admin.advertisements'), icon: Megaphone },
  { to: '/admin/packages', label: () => t('admin.packages'), icon: Package },
  { to: '/admin/subscription-requests', label: () => t('admin.subscriptionRequests'), icon: CreditCard },
  { to: '/admin/users', label: () => t('admin.users'), icon: Users },
  { to: '/admin/growth', label: () => 'Growth & Trust', icon: Sparkles },
  { to: '/admin/settings', label: () => t('admin.settings'), icon: Settings },
]

async function logout() {
  await authStore.logout()
  router.replace('/admin/login')
}
</script>

<template>
  <div class="min-h-screen flex bg-[#f3f7f7]">
    <!-- Mobile top bar -->
    <div class="lg:hidden fixed top-0 left-0 right-0 z-30 bg-white border-b border-gray-100 flex items-center justify-between px-4 py-3">
      <div class="flex items-center gap-2">
        <EcLogo :size="28" />
        <span class="font-bold text-sm" style="color: var(--color-navy)">Easy Cyprus Admin</span>
      </div>
      <button class="p-2 rounded-lg bg-gray-100" @click="mobileOpen = true">
        <MenuIcon :size="20" />
      </button>
    </div>

    <!-- Mobile drawer backdrop -->
    <div v-if="mobileOpen" class="lg:hidden fixed inset-0 bg-black/40 z-40" @click="mobileOpen = false" />

    <aside
      class="w-72 ec-gradient text-white flex flex-col shrink-0 fixed lg:sticky top-0 h-screen z-50 transition-transform lg:translate-x-0 shadow-2xl shadow-teal-950/20"
      :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <div class="flex items-center justify-between gap-2 p-5">
        <div class="flex items-center gap-2">
          <EcLogo :size="32" />
          <span class="font-bold text-sm text-white">Easy Cyprus Admin</span>
        </div>
        <button class="lg:hidden p-1 rounded-lg bg-gray-100" @click="mobileOpen = false">
          <X :size="18" />
        </button>
      </div>
      <nav class="flex-1 flex flex-col gap-1 px-3 overflow-y-auto">
        <RouterLink
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm text-white/75 hover:text-white hover:bg-white/10 transition-colors"
          active-class="!bg-white/16 !text-white font-semibold shadow-sm"
          @click="mobileOpen = false"
        >
          <component :is="link.icon" :size="18" />
          {{ link.label() }}
        </RouterLink>
      </nav>
      <button class="flex items-center gap-3 px-3 py-2.5 m-3 rounded-xl text-sm text-white/75 hover:bg-white/10" @click="logout">
        <LogOut :size="18" /> {{ t('admin.logout') }}
      </button>
    </aside>

    <main class="flex-1 p-4 pt-20 lg:p-8 lg:pt-7 overflow-y-auto min-w-0">
      <RouterView />
    </main>
  </div>
</template>
