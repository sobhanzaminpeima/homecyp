<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { Download, Apple, Smartphone, Globe, Share, SquarePlus, ChevronLeft } from 'lucide-vue-next'
import api from '@/lib/api'

const { t } = useI18n()

const showMenu = ref(false)
const showWebAppGuide = ref(false)
const iosAppUrl = ref<string | null>(null)
const androidAppUrl = ref<string | null>(null)

async function loadAppLinks() {
  try {
    const { data } = await api.get('/settings')
    iosAppUrl.value = data.ios_app_url || null
    androidAppUrl.value = data.android_app_url || null
  } catch (error) {
    console.warn('Failed to load app links', error)
  }
}

function openMenu() {
  showWebAppGuide.value = false
  showMenu.value = true
}

function closeMenu() {
  showMenu.value = false
  showWebAppGuide.value = false
}

function openIos() {
  if (iosAppUrl.value) window.open(iosAppUrl.value, '_blank', 'noopener')
}

function openAndroid() {
  if (androidAppUrl.value) window.open(androidAppUrl.value, '_blank', 'noopener')
}

onMounted(loadAppLinks)
</script>

<template>
  <div>
    <button class="p-2 rounded-full bg-white/15" @click="openMenu">
      <Download :size="18" />
    </button>

    <Teleport to="body">
      <div v-if="showMenu" class="fixed inset-0 bg-black/40 z-50 flex items-end justify-center" @click.self="closeMenu">
        <div class="bg-white w-full max-w-[480px] rounded-t-3xl p-5 pb-8 safe-x">
          <template v-if="!showWebAppGuide">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-bold text-sm" style="color: var(--color-navy)">{{ t('install.title') }}</h3>
              <button class="text-xs" style="color: var(--color-muted)" @click="closeMenu">{{ t('common.close') }}</button>
            </div>

            <div class="flex flex-col gap-2">
              <button v-if="iosAppUrl" class="ec-card p-4 flex items-center gap-3" @click="openIos">
                <Apple :size="20" style="color: var(--color-navy)" />
                <span class="text-sm" style="color: var(--color-navy)">{{ t('install.iosApp') }}</span>
              </button>
              <button v-if="androidAppUrl" class="ec-card p-4 flex items-center gap-3" @click="openAndroid">
                <Smartphone :size="20" style="color: var(--color-teal)" />
                <span class="text-sm" style="color: var(--color-navy)">{{ t('install.androidApp') }}</span>
              </button>
              <button class="ec-card p-4 flex items-center gap-3" @click="showWebAppGuide = true">
                <Globe :size="20" style="color: var(--color-orange)" />
                <span class="text-sm" style="color: var(--color-navy)">{{ t('install.webApp') }}</span>
              </button>
            </div>
          </template>

          <template v-else>
            <div class="flex items-center gap-2 mb-4">
              <button @click="showWebAppGuide = false"><ChevronLeft :size="20" /></button>
              <h3 class="font-bold text-sm flex-1" style="color: var(--color-navy)">{{ t('install.webAppTitle') }}</h3>
              <button class="text-xs" style="color: var(--color-muted)" @click="closeMenu">{{ t('common.close') }}</button>
            </div>

            <div class="flex flex-col items-center gap-4">
              <img src="/apple-touch-icon.png" alt="Easy Cyprus" class="h-16 w-16 rounded-2xl shadow" />

              <ol class="flex flex-col gap-3 w-full text-sm" style="color: var(--color-navy)">
                <li class="flex items-start gap-3">
                  <span class="ec-btn-primary h-6 w-6 flex items-center justify-center rounded-full text-xs shrink-0">1</span>
                  <span class="flex items-center gap-1">{{ t('install.step1') }} <Share :size="15" class="inline" /></span>
                </li>
                <li class="flex items-start gap-3">
                  <span class="ec-btn-primary h-6 w-6 flex items-center justify-center rounded-full text-xs shrink-0">2</span>
                  <span class="flex items-center gap-1">{{ t('install.step2') }} <SquarePlus :size="15" class="inline" /></span>
                </li>
                <li class="flex items-start gap-3">
                  <span class="ec-btn-primary h-6 w-6 flex items-center justify-center rounded-full text-xs shrink-0">3</span>
                  <span>{{ t('install.step3') }}</span>
                </li>
              </ol>
            </div>
          </template>
        </div>
      </div>
    </Teleport>
  </div>
</template>
