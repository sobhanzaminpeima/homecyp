<script setup lang="ts">
import { Home, Search, Sparkles, Heart, User } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const items = [
  { to: '/home', icon: Home, label: () => t('nav.home') },
  { to: '/explore', icon: Search, label: () => t('nav.explore') },
  { to: '/ai', icon: Sparkles, label: () => 'AI' },
  { to: '/favorites', icon: Heart, label: () => t('nav.favorites') },
  { to: '/profile', icon: User, label: () => t('nav.profile') },
]
</script>

<template>
  <nav
    class="fixed left-3 right-3 max-w-[496px] mx-auto flex items-center justify-around safe-x z-40"
    style="bottom: max(.65rem, env(safe-area-inset-bottom)); padding: .45rem; background: rgba(255,255,255,.9); border: 1px solid rgba(16,42,56,.08); border-radius: 1.55rem; box-shadow: 0 18px 45px -18px rgba(8,49,61,.42); backdrop-filter: blur(20px); transform: translateZ(0)"
  >
    <RouterLink
      v-for="item in items"
      :key="item.to"
      :to="item.to"
      class="relative flex flex-col items-center gap-0.5 px-2 py-2 rounded-2xl text-gray-400 transition-all min-w-[3.75rem]"
      active-class="ec-bottom-nav-active"
    >
      <component :is="item.icon" :size="21" :stroke-width="2.1" />
      <span class="text-[10px] font-medium">{{ item.label() }}</span>
    </RouterLink>
  </nav>
</template>

<style scoped>
.ec-bottom-nav-active {
  color: var(--color-teal);
  background: rgba(8, 127, 120, .09);
}

.ec-bottom-nav-active::before {
  content: '';
  position: absolute;
  top: -.45rem;
  left: 50%;
  transform: translateX(-50%);
  width: 1.15rem;
  height: 2px;
  border-radius: 9999px;
  background: linear-gradient(135deg, var(--color-teal-light), var(--color-teal-dark));
}

a[href='/favorites'].ec-bottom-nav-active {
  color: var(--color-orange-dark);
}

a[href='/favorites'].ec-bottom-nav-active::before {
  background: linear-gradient(135deg, var(--color-gold), var(--color-orange-dark));
}
</style>
