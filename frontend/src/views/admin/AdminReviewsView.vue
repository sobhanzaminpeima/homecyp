<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'

interface AdminReview {
  id: number
  rating: number
  body: string | null
  status: 'pending' | 'approved' | 'rejected'
  user_name: string | null
  business_name: string | null
  business_slug: string | null
  created_at: string | null
}

const { t } = useI18n()
const toast = useToast()

const reviews = ref<AdminReview[]>([])
const loading = ref(true)
const statusFilter = ref('')

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/reviews', {
      params: { status: statusFilter.value || undefined },
    })
    reviews.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load reviews', error)
  } finally {
    loading.value = false
  }
}

async function setStatus(review: AdminReview, status: AdminReview['status']) {
  try {
    await api.put(`/admin/reviews/${review.id}`, { status })
    review.status = status
    toast.success('Review updated.')
  } catch (error) {
    console.warn('Failed to update review', error)
    toast.error('Failed to update review.')
  }
}

async function remove(review: AdminReview) {
  if (!confirm('Delete this review?')) return
  try {
    await api.delete(`/admin/reviews/${review.id}`)
    reviews.value = reviews.value.filter((r) => r.id !== review.id)
    toast.success('Review deleted.')
  } catch (error) {
    console.warn('Failed to delete review', error)
    toast.error('Failed to delete review.')
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
    <h1 class="font-bold text-xl" style="color: var(--color-navy)">{{ t('admin.reviews') }}</h1>
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

  <div v-else-if="!reviews.length" class="text-sm" style="color: var(--color-muted)">No reviews found.</div>

  <div v-else class="bg-white rounded-2xl shadow-sm divide-y divide-gray-50">
    <div v-for="review in reviews" :key="review.id" class="p-4 flex flex-wrap items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 flex-wrap">
          <p class="font-medium text-sm" style="color: var(--color-navy)">{{ review.business_name }}</p>
          <span class="text-xs px-2 py-0.5 rounded-full" :class="statusStyles[review.status]">{{ review.status }}</span>
          <span class="text-xs" style="color: var(--color-orange)">{{ '★'.repeat(review.rating) }}</span>
        </div>
        <p class="text-xs mt-1" style="color: var(--color-muted)">by {{ review.user_name }}</p>
        <p v-if="review.body" class="text-sm mt-2">{{ review.body }}</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button
          v-if="review.status !== 'approved'"
          class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700"
          @click="setStatus(review, 'approved')"
        >
          Approve
        </button>
        <button
          v-if="review.status !== 'rejected'"
          class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700"
          @click="setStatus(review, 'rejected')"
        >
          Reject
        </button>
        <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700" @click="remove(review)">
          {{ t('common.delete') }}
        </button>
      </div>
    </div>
  </div>
</template>
