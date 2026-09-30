<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'
import type { User } from '@/types'

const { t } = useI18n()

const users = ref<User[]>([])
const loading = ref(true)
const search = ref('')
const roleFilter = ref('')

const creating = ref(false)
const createForm = ref({ name: '', email: '', password: '', account_type: 'user' })
const createError = ref('')
const saving = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/users', {
      params: { q: search.value || undefined, account_type: roleFilter.value || undefined },
    })
    users.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load users', error)
  } finally {
    loading.value = false
  }
}

async function remove(user: User) {
  if (!confirm(`Delete ${user.name}?`)) return
  try {
    await api.delete(`/admin/users/${user.id}`)
    users.value = users.value.filter((u) => u.id !== user.id)
  } catch (error) {
    console.warn('Failed to delete user', error)
  }
}

async function changeRole(user: User, account_type: string) {
  try {
    await api.put(`/admin/users/${user.id}`, { account_type })
    user.account_type = account_type as User['account_type']
  } catch (error) {
    console.warn('Failed to change role', error)
  }
}

function openCreate() {
  creating.value = true
  createError.value = ''
  createForm.value = { name: '', email: '', password: '', account_type: 'user' }
}

function closeCreate() {
  creating.value = false
}

async function saveCreate() {
  if (!createForm.value.name.trim() || !createForm.value.email.trim() || createForm.value.password.length < 8) {
    createError.value = 'Name, email, and an 8+ character password are required.'
    return
  }
  saving.value = true
  createError.value = ''
  try {
    const { data } = await api.post('/admin/users', createForm.value)
    users.value.unshift(data.data)
    closeCreate()
  } catch (error: any) {
    createError.value = error?.response?.data?.message ?? 'Failed to create user.'
    console.warn('Failed to create user', error)
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <div class="flex items-center justify-between mb-6">
    <h1 class="font-bold text-xl" style="color: var(--color-navy)">{{ t('admin.users') }}</h1>
    <button class="ec-btn-primary px-4 text-sm" @click="openCreate">+ Add user</button>
  </div>

  <div class="flex flex-wrap gap-2 mb-4">
    <input v-model="search" placeholder="Search name / email..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1 min-w-[200px]" @keyup.enter="load" />
    <select v-model="roleFilter" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" @change="load">
      <option value="">All roles</option>
      <option value="user">User</option>
      <option value="business">Business owner</option>
      <option value="admin">Admin</option>
    </select>
    <button class="ec-btn-primary px-4 text-sm" @click="load">{{ t('common.search') }}</button>
  </div>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <div v-else class="bg-white rounded-2xl shadow-sm divide-y divide-gray-50">
    <div v-for="user in users" :key="user.id" class="p-4 flex flex-wrap items-center justify-between gap-2">
      <div class="min-w-0">
        <p class="font-medium text-sm" style="color: var(--color-navy)">{{ user.name }}</p>
        <p class="text-xs truncate" style="color: var(--color-muted)">{{ user.email }}</p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <select
          :value="user.account_type"
          class="border border-gray-200 rounded-lg px-2 py-1 text-xs"
          @change="changeRole(user, ($event.target as HTMLSelectElement).value)"
        >
          <option value="user">User</option>
          <option value="business">Business owner</option>
          <option value="admin">Admin</option>
        </select>
        <button class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700" @click="remove(user)">{{ t('common.delete') }}</button>
      </div>
    </div>
  </div>

  <div v-if="creating" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4" @click.self="closeCreate">
    <div class="bg-white w-full max-w-sm rounded-2xl p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-bold text-sm" style="color: var(--color-navy)">Add user</h3>
        <button class="text-xs" style="color: var(--color-muted)" @click="closeCreate">{{ t('common.close') }}</button>
      </div>

      <div v-if="createError" class="text-xs text-red-600 mb-3">{{ createError }}</div>

      <div class="flex flex-col gap-3 text-sm">
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Name *</span>
          <input v-model="createForm.name" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Email *</span>
          <input v-model="createForm.email" type="email" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Password * (min 8 chars)</span>
          <input v-model="createForm.password" type="password" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1" />
        </label>
        <label>
          <span class="text-xs" style="color: var(--color-muted)">Role</span>
          <select v-model="createForm.account_type" class="w-full border border-gray-200 rounded-lg px-3 py-2 mt-1">
            <option value="user">User</option>
            <option value="business">Business owner</option>
            <option value="admin">Admin</option>
          </select>
        </label>
      </div>

      <div class="flex justify-end gap-2 mt-4">
        <button class="text-sm px-4 py-2 rounded-lg bg-gray-100" @click="closeCreate">Cancel</button>
        <button class="ec-btn-primary px-4 py-2 text-sm" :disabled="saving" @click="saveCreate">{{ t('common.save') }}</button>
      </div>
    </div>
  </div>
</template>
