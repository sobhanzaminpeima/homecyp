<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import { useToast } from '@/composables/useToast'
import type { BusinessProject } from '@/types'

const { t } = useI18n()
const toast = useToast()

const projects = ref<BusinessProject[]>([])
const loading = ref(true)
const statusFilter = ref('')

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/projects', {
      params: { status: statusFilter.value || undefined },
    })
    projects.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load projects', error)
  } finally {
    loading.value = false
  }
}

async function setStatus(project: BusinessProject, status: BusinessProject['status']) {
  try {
    await api.put(`/admin/projects/${project.id}`, { status })
    project.status = status
    toast.success('Project updated.')
  } catch (error) {
    console.warn('Failed to update project', error)
    toast.error('Failed to update project.')
  }
}

async function toggleFeatured(project: BusinessProject) {
  try {
    await api.put(`/admin/projects/${project.id}`, { is_featured: !project.is_featured })
    project.is_featured = !project.is_featured
  } catch (error) {
    console.warn('Failed to toggle featured', error)
  }
}

async function remove(project: BusinessProject) {
  if (!confirm('Delete this project?')) return
  try {
    await api.delete(`/admin/projects/${project.id}`)
    projects.value = projects.value.filter((p) => p.id !== project.id)
    toast.success('Project deleted.')
  } catch (error) {
    console.warn('Failed to delete project', error)
    toast.error('Failed to delete project.')
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
    <h1 class="font-bold text-xl" style="color: var(--color-navy)">{{ t('admin.projects') }}</h1>
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

  <div v-else-if="!projects.length" class="text-sm" style="color: var(--color-muted)">No projects found.</div>

  <div v-else class="bg-white rounded-2xl shadow-sm divide-y divide-gray-50">
    <div v-for="project in projects" :key="project.id" class="p-4 flex flex-wrap items-start justify-between gap-3">
      <div class="flex gap-3 min-w-0 flex-1">
        <img
          v-if="project.images[0]"
          :src="project.images[0]"
          class="w-14 h-14 rounded-lg object-cover shrink-0"
        />
        <div class="min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="font-medium text-sm" style="color: var(--color-navy)">{{ project.title }}</p>
            <span class="text-xs px-2 py-0.5 rounded-full" :class="statusStyles[project.status]">{{ project.status }}</span>
            <span v-if="project.is_featured" class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">Featured</span>
          </div>
          <p class="text-xs mt-1" style="color: var(--color-muted)">{{ project.business?.name }}</p>
          <p v-if="project.price" class="text-xs mt-1" style="color: var(--color-teal)">{{ project.currency }} {{ Number(project.price).toLocaleString() }}</p>
        </div>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button
          v-if="project.status !== 'approved'"
          class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700"
          @click="setStatus(project, 'approved')"
        >
          Approve
        </button>
        <button
          v-if="project.status !== 'rejected'"
          class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-700"
          @click="setStatus(project, 'rejected')"
        >
          Reject
        </button>
        <button class="text-xs px-2 py-1 rounded-full bg-gray-100" @click="toggleFeatured(project)">
          {{ project.is_featured ? 'Unfeature' : 'Feature' }}
        </button>
        <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700" @click="remove(project)">
          {{ t('common.delete') }}
        </button>
      </div>
    </div>
  </div>
</template>
