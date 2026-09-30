<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Bell, CheckCheck, ChevronLeft } from 'lucide-vue-next'
import api from '@/lib/api'

const items = ref<any[]>([])
const loading = ref(true)
async function load(){ loading.value=true; try { items.value=(await api.get('/notifications')).data.data } finally { loading.value=false } }
async function open(item:any){ if(!item.read_at){ await api.put(`/notifications/${item.id}/read`); item.read_at=new Date().toISOString() } if(item.url) location.href=item.url }
onMounted(load)
</script>
<template>
  <main class="p-5 pb-24 safe-x max-w-2xl mx-auto">
    <header class="flex items-center gap-3 mb-6"><RouterLink to="/profile" class="h-10 w-10 rounded-2xl bg-white shadow-sm flex items-center justify-center"><ChevronLeft :size="20" /></RouterLink><div><p class="text-[10px] uppercase tracking-[.18em] text-teal-600">Updates</p><h1 class="font-extrabold text-2xl text-slate-900">Notifications</h1></div></header>
    <div v-if="loading" class="ec-card p-8 text-center text-sm text-gray-400">Loading…</div>
    <div v-else-if="!items.length" class="ec-card p-10 text-center"><Bell class="mx-auto mb-3 text-teal-600"/><p class="font-bold text-slate-800">You're all caught up</p><p class="text-sm text-gray-400 mt-1">New booking, claim and account updates appear here.</p></div>
    <button v-for="item in items" :key="item.id" class="ec-card p-4 mb-3 w-full text-left flex gap-3" @click="open(item)"><span class="h-10 w-10 rounded-2xl flex items-center justify-center shrink-0" :class="item.read_at?'bg-gray-100':'bg-teal-50'"><CheckCheck :size="18" :class="item.read_at?'text-gray-400':'text-teal-600'"/></span><span><strong class="block text-sm text-slate-800">{{ item.title }}</strong><span class="text-xs text-gray-500">{{ item.body }}</span></span></button>
  </main>
</template>
