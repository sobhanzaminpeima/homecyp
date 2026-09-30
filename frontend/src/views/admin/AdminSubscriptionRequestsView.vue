<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'
import type { SubscriptionRequest } from '@/types'

const { t } = useI18n()
const toast = useToast()

const requests = ref<SubscriptionRequest[]>([])
const loading = ref(true)
const statusFilter = ref('pending')

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/subscription-requests', {
      params: { status: statusFilter.value || undefined },
    })
    requests.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load subscription requests', error)
  } finally {
    loading.value = false
  }
}

async function setStatus(request: SubscriptionRequest, status: 'approved' | 'rejected') {
  try {
    await api.put(`/admin/subscription-requests/${request.id}`, { status })
    request.status = status
    toast.success(status === 'approved' ? 'Subscription approved.' : 'Subscription rejected.')
  } catch (error) {
    console.warn('Failed to update subscription request', error)
    toast.error('Failed to update request.')
  }
}

const statusStyles: Record<string, string> = {
  approved: 'bg-green-100 text-green-700',
  pending: 'bg-yellow-100 text-yellow-700',
  rejected: 'bg-red-100 text-red-700',
}

onMounted(load)
</script>

<template>
  <div class="flex items-center justify-between mb-6">
    <h1 class="font-bold text-xl" style="color: var(--color-navy)">Subscription requests</h1>
  </div>

  <div class="flex flex-wrap gap-2 mb-4">
    <select v-model="statusFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" @change="load">
      <option value="">All statuses</option>
      <option value="pending">Pending</option>
      <option value="approved">Approved</option>
      <option value="rejected">Rejected</option>
    </select>
    <button class="ec-btn-primary px-4 text-sm" @click="load">{{ t('common.search') }}</button>
  </div>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else-if="!requests.length" class="text-sm" style="color: var(--color-muted)">No requests found.</div>

  <div v-else class="bg-white rounded-2xl shadow-sm divide-y divide-gray-50">
    <div v-for="request in requests" :key="request.id" class="p-4 flex flex-wrap items-center justify-between gap-3">
      <div class="min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <p class="font-medium text-sm" style="color: var(--color-navy)">{{ request.business_name }}</p>
          <span class="text-xs px-2 py-0.5 rounded-full" :class="statusStyles[request.status]">{{ request.status }}</span>
        </div>
        <p class="text-xs mt-1" style="color: var(--color-muted)">
          Wants: <strong>{{ request.package.name }}</strong> — €{{ request.package.price.toFixed(2) }} / {{ request.package.duration_days }}d
        </p>
        <p v-if="request.note" class="text-xs mt-1" style="color: var(--color-muted)">"{{ request.note }}"</p>
      </div>
      <div v-if="request.status === 'pending'" class="flex items-center gap-2 shrink-0">
        <button class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700" @click="setStatus(request, 'approved')">Approve</button>
        <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700" @click="setStatus(request, 'rejected')">Reject</button>
      </div>
    </div>
  </div>
</template>
