<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { CalendarDays, Tag, MapPin, Ticket, Sparkles } from 'lucide-vue-next'
import api from '@/lib/api'
import { useCityStore } from '@/stores/city'
import BottomNav from '@/components/BottomNav.vue'
import EmptyState from '@/components/EmptyState.vue'
import type { Deal, LocalEvent } from '@/types'

const city = useCityStore()
const tab = ref<'deals'|'events'>('deals')
const deals = ref<Deal[]>([])
const events = ref<LocalEvent[]>([])
const loading = ref(true)

async function load(){ loading.value=true; try { const [d,e]=await Promise.all([api.get('/deals',{params:{city:city.city?.slug}}),api.get('/events',{params:{city:city.city?.slug}})]); deals.value=d.data.data??[]; events.value=e.data.data??[] } finally { loading.value=false } }
function fmt(value:string){ return new Intl.DateTimeFormat(undefined,{dateStyle:'medium',timeStyle:'short'}).format(new Date(value)) }
onMounted(load)
</script>

<template>
  <div class="pb-28">
    <header class="ec-gradient text-white px-5 pt-7 pb-9 rounded-b-[2.4rem] relative overflow-hidden">
      <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full border border-white/10"></div>
      <div class="relative"><p class="text-[10px] uppercase tracking-[.2em] opacity-65">Curated for {{ city.city?.name }}</p><h1 class="text-2xl font-extrabold mt-1">Discover something new</h1><p class="text-sm opacity-75 mt-2">Fresh offers and memorable experiences around you.</p></div>
      <div class="relative grid grid-cols-2 gap-2 mt-6 bg-black/10 p-1 rounded-2xl border border-white/10">
        <button class="py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2" :class="tab==='deals'?'bg-white text-teal-800 shadow-lg':'text-white/75'" @click="tab='deals'"><Tag :size="16"/> Deals</button>
        <button class="py-2.5 rounded-xl text-sm font-semibold flex items-center justify-center gap-2" :class="tab==='events'?'bg-white text-teal-800 shadow-lg':'text-white/75'" @click="tab='events'"><CalendarDays :size="16"/> Events</button>
      </div>
    </header>

    <main class="px-5 mt-6">
      <div v-if="loading" class="grid gap-3"><div v-for="n in 3" :key="n" class="h-36 rounded-3xl bg-slate-200 animate-pulse"></div></div>
      <template v-else-if="tab==='deals'">
        <EmptyState v-if="!deals.length" message="New local deals are coming soon." />
        <div v-else class="grid gap-4">
          <RouterLink v-for="deal in deals" :key="deal.id" :to="`/business/${deal.business.slug}`" class="ec-card overflow-hidden flex min-h-32">
            <div class="w-28 ec-gradient shrink-0 flex items-center justify-center"><img v-if="deal.image" :src="deal.image" class="h-full w-full object-cover"/><Sparkles v-else :size="34" class="text-white/80"/></div>
            <div class="p-4 min-w-0"><span v-if="deal.discount_label" class="text-[10px] font-bold px-2 py-1 rounded-full bg-orange-100 text-orange-700">{{ deal.discount_label }}</span><h2 class="font-extrabold mt-2 truncate">{{ deal.title }}</h2><p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ deal.description }}</p><p class="text-[11px] text-teal-700 font-semibold mt-2">{{ deal.business.name }}</p></div>
          </RouterLink>
        </div>
      </template>
      <template v-else>
        <EmptyState v-if="!events.length" message="Upcoming events will appear here." />
        <div v-else class="grid gap-4">
          <article v-for="event in events" :key="event.id" class="ec-card overflow-hidden"><img v-if="event.image" :src="event.image" class="h-40 w-full object-cover"/><div class="p-4"><div class="flex items-center gap-2 text-[11px] text-teal-700 font-semibold"><CalendarDays :size="14"/>{{ fmt(event.starts_at) }}</div><h2 class="text-lg font-extrabold mt-2">{{ event.title }}</h2><p v-if="event.venue" class="text-xs text-slate-500 mt-2 flex items-center gap-1"><MapPin :size="13"/>{{ event.venue }}</p><a v-if="event.booking_url" :href="event.booking_url" target="_blank" class="ec-btn-primary mt-4 py-2.5 px-4 text-xs inline-flex items-center gap-2"><Ticket :size="14"/> Get details</a></div></article>
        </div>
      </template>
    </main>
    <BottomNav />
  </div>
</template>
