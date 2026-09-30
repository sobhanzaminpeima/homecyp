<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '@/lib/api'

const { t } = useI18n()

const viewsBase = ref(0)
const counterLabel = ref('')
const siteName = ref('')
const supportEmail = ref('')
const contactPhone = ref('')
const iosAppUrl = ref('')
const androidAppUrl = ref('')
const loading = ref(true)
const saving = ref(false)
const saved = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/settings')
    viewsBase.value = data.views_base ?? 0
    counterLabel.value = data.counter_label ?? ''
    siteName.value = data.site_name ?? ''
    supportEmail.value = data.support_email ?? ''
    contactPhone.value = data.contact_phone ?? ''
    iosAppUrl.value = data.ios_app_url ?? ''
    androidAppUrl.value = data.android_app_url ?? ''
  } catch (error) {
    console.warn('Failed to load settings', error)
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  saved.value = false
  try {
    await api.put('/admin/settings', {
      views_base: viewsBase.value,
      counter_label: counterLabel.value,
      site_name: siteName.value,
      support_email: supportEmail.value,
      contact_phone: contactPhone.value,
      ios_app_url: iosAppUrl.value || null,
      android_app_url: androidAppUrl.value || null,
    })
    saved.value = true
  } catch (error) {
    console.warn('Failed to save settings', error)
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<template>
  <h1 class="font-bold text-xl mb-6" style="color: var(--color-navy)">{{ t('admin.settings') }}</h1>

  <div v-if="loading" class="text-sm" style="color: var(--color-muted)">{{ t('common.loading') }}</div>

  <form v-else class="bg-white rounded-2xl shadow-sm p-6 max-w-md flex flex-col gap-4" @submit.prevent="save">
    <label class="flex flex-col gap-1 text-sm">
      {{ t('admin.viewsBase') }}
      <input v-model.number="viewsBase" type="number" min="0" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
      <span class="text-xs" style="color: var(--color-muted)">
        The homepage counter shows this number plus the real sum of all business view counts.
      </span>
    </label>

    <label class="flex flex-col gap-1 text-sm">
      Counter label text
      <input v-model="counterLabel" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
      <span class="text-xs" style="color: var(--color-muted)">
        Shown under the counter number on the homepage, e.g. "people explored Easy Cyprus".
      </span>
    </label>

    <label class="flex flex-col gap-1 text-sm">
      Site name
      <input v-model="siteName" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
    </label>

    <label class="flex flex-col gap-1 text-sm">
      Support email
      <input v-model="supportEmail" type="email" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
    </label>

    <label class="flex flex-col gap-1 text-sm">
      Contact phone
      <input v-model="contactPhone" class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
    </label>

    <hr class="border-gray-100" />

    <label class="flex flex-col gap-1 text-sm">
      iOS App Store URL
      <input v-model="iosAppUrl" type="url" placeholder="https://apps.apple.com/..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
      <span class="text-xs" style="color: var(--color-muted)">
        Leave empty to hide the iOS option in the Install menu until you have a real App Store link.
      </span>
    </label>

    <label class="flex flex-col gap-1 text-sm">
      Android Play Store URL
      <input v-model="androidAppUrl" type="url" placeholder="https://play.google.com/store/apps/..." class="border border-gray-200 rounded-lg px-3 py-2 text-sm" />
      <span class="text-xs" style="color: var(--color-muted)">
        Leave empty to hide the Android option in the Install menu until you have a real Play Store link.
      </span>
    </label>

    <button type="submit" class="ec-btn-primary py-3" :disabled="saving">{{ t('common.save') }}</button>
    <p v-if="saved" class="text-xs text-green-600">Saved.</p>
  </form>
</template>
