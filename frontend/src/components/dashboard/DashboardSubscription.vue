<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Crown, Check } from 'lucide-vue-next'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'
import type { Business, Package, SubscriptionRequest } from '@/types'

const props = defineProps<{ business: Business }>()

const { t } = useI18n()
const toast = useToast()

const packages = ref<Package[]>([])
const requests = ref<SubscriptionRequest[]>([])
const loading = ref(true)
const requesting = ref<number | null>(null)

const pendingRequest = computed(() => requests.value.find((r) => r.status === 'pending') ?? null)

async function load() {
  loading.value = true
  try {
    const [pkgRes, reqRes] = await Promise.all([
      api.get('/packages'),
      api.get('/dashboard/subscription-requests'),
    ])
    packages.value = pkgRes.data.data ?? []
    requests.value = reqRes.data.data ?? []
  } catch (error) {
    console.warn('Failed to load subscription info', error)
  } finally {
    loading.value = false
  }
}

async function requestPackage(pkg: Package) {
  requesting.value = pkg.id
  try {
    const { data } = await api.post('/dashboard/subscription-requests', { package_id: pkg.id })
    requests.value.unshift(data.data)
    toast.success(t('dashboard.subscriptionRequested'))
  } catch (error: any) {
    console.warn('Failed to request subscription', error)
    toast.error(error?.response?.data?.errors?.package_id?.[0] || t('dashboard.subscriptionRequestFailed'))
  } finally {
    requesting.value = null
  }
}

onMounted(load)
</script>

<template>
  <div class="ec-card p-4">
    <h2 class="font-semibold text-sm mb-1 flex items-center gap-2" style="color: var(--color-navy)">
      <Crown :size="16" style="color: var(--color-orange)" /> {{ t('dashboard.plan') }}
    </h2>

    <div v-if="business.package" class="mt-2 p-3 rounded-xl" style="background: rgba(28,107,112,0.08)">
      <p class="text-sm font-semibold" style="color: var(--color-teal-dark)">{{ business.package.name }}</p>
      <p class="text-xs mt-0.5" style="color: var(--color-muted)">
        {{ business.package.listing_limit === null ? t('dashboard.unlimitedListings') : t('dashboard.listingsLimit', { n: business.package.listing_limit }) }}
      </p>
    </div>
    <div v-else class="mt-2 p-3 rounded-xl bg-gray-50">
      <p class="text-sm font-semibold" style="color: var(--color-navy)">{{ t('dashboard.freePlan') }}</p>
      <p class="text-xs mt-0.5" style="color: var(--color-muted)">{{ t('dashboard.listingsLimit', { n: 3 }) }}</p>
    </div>

    <div v-if="pendingRequest" class="mt-3 p-3 rounded-xl bg-yellow-50 text-xs" style="color: #92400e">
      {{ t('dashboard.pendingRequest', { name: pendingRequest.package.name }) }}
    </div>

    <div v-if="loading" class="text-xs mt-3" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

    <div v-else-if="!pendingRequest" class="mt-4 flex flex-col gap-3">
      <p class="text-xs font-semibold" style="color: var(--color-navy)">{{ t('dashboard.availablePlans') }}</p>
      <div v-for="pkg in packages" :key="pkg.id" class="border border-gray-100 rounded-xl p-3">
        <div class="flex items-center justify-between">
          <p class="text-sm font-semibold" style="color: var(--color-navy)">{{ pkg.name }}</p>
          <p class="text-sm font-bold" style="color: var(--color-teal)">€{{ pkg.price.toFixed(2) }}</p>
        </div>
        <p v-if="pkg.description" class="text-xs mt-1" style="color: var(--color-muted)">{{ pkg.description }}</p>
        <ul v-if="pkg.features?.length" class="mt-2 flex flex-col gap-1">
          <li v-for="feature in pkg.features" :key="feature" class="text-xs flex items-center gap-1" style="color: var(--color-navy)">
            <Check :size="12" style="color: var(--color-teal)" /> {{ feature }}
          </li>
        </ul>
        <button
          class="ec-btn-primary text-xs px-4 py-2 mt-3 w-full"
          :disabled="requesting === pkg.id || business.package?.id === pkg.id"
          @click="requestPackage(pkg)"
        >
          {{ business.package?.id === pkg.id ? t('dashboard.currentPlan') : t('dashboard.requestPlan') }}
        </button>
      </div>
      <p v-if="!packages.length" class="text-xs" style="color: var(--color-muted)">{{ t('dashboard.noPlansYet') }}</p>
    </div>
  </div>
</template>
