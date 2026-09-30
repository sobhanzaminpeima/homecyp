<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  ArrowLeft,
  Phone,
  MessageCircle,
  Globe as GlobeIcon,
  Instagram,
  Share2,
  MapPin,
  Star,
  Heart,
  BadgeCheck,
  ShieldCheck,
  Flag,
  CalendarCheck,
  MessageSquareText,
} from 'lucide-vue-next'
import api from '@/lib/api'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import EmptyState from '@/components/EmptyState.vue'
import BusinessCard from '@/components/BusinessCard.vue'

// Businesses backfilled from a small square logo (no real wide cover photo) look
// blurry/cropped when stretched full-bleed with object-cover — detect that case
// once the image has actually loaded and render it "contained" on a soft backdrop
// instead.
const coverIsSquareLogo = ref(false)
const coverFailed = ref(false)
function onCoverLoad(event: Event) {
  const img = event.target as HTMLImageElement
  const ratio = img.naturalWidth / img.naturalHeight
  coverIsSquareLogo.value = ratio > 0.85 && ratio < 1.15 && img.naturalWidth <= 256
}
function onCoverError() {
  coverFailed.value = true
}
import { localizedName } from '@/i18n'
import { useSmartBack } from '@/composables/useSmartBack'
import BottomNav from '@/components/BottomNav.vue'
import BusinessDetailSkeleton from '@/components/skeletons/BusinessDetailSkeleton.vue'
import type { Business } from '@/types'

const { t, locale } = useI18n()
const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const goBack = useSmartBack('/explore')
const toast = useToast()

const business = ref<Business | null>(null)
const similar = ref<Business[]>([])
const loading = ref(true)
const failed = ref(false)

const newRating = ref(5)
const newReviewBody = ref('')
const submittingReview = ref(false)
const actionModal = ref<'booking'|'quote'|'claim'|'report'|null>(null)
const actionLoading = ref(false)
const actionForm = ref({ name:'', phone:'', email:'', preferred_at:'', message:'', role:'Owner', reason:'wrong_hours', details:'' })

const slug = computed(() => route.params.slug as string)

const hoursEntries = computed(() => Object.entries(business.value?.hours ?? {}))

async function loadBusiness() {
  loading.value = true
  failed.value = false
  coverIsSquareLogo.value = false
  coverFailed.value = false
  try {
    const { data } = await api.get(`/businesses/${slug.value}`)
    business.value = data.data
  } catch (error) {
    console.warn('Failed to load business', error)
    failed.value = true
  } finally {
    loading.value = false
  }
}

async function loadSimilar() {
  try {
    const { data } = await api.get(`/businesses/${slug.value}/similar`)
    similar.value = data.data ?? []
  } catch (error) {
    console.warn('Failed to load similar businesses', error)
  }
}

async function toggleFavorite() {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  if (!business.value) return
  try {
    const { data } = await api.post(`/businesses/${business.value.slug}/favorite`)
    business.value.is_favorited = data.favorited
  } catch (error) {
    console.warn('Failed to toggle favorite', error)
    toast.error(t('business.favoriteFailed'))
  }
}

async function submitReview() {
  if (!authStore.isAuthenticated) {
    router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }
  if (!business.value) return

  submittingReview.value = true
  try {
    await api.post(`/businesses/${business.value.slug}/reviews`, {
      rating: newRating.value,
      body: newReviewBody.value || undefined,
    })
    newReviewBody.value = ''
    toast.success(t('business.reviewSubmitted'))
    await loadBusiness()
  } catch (error) {
    console.warn('Failed to submit review', error)
    toast.error(t('business.reviewFailed'))
  } finally {
    submittingReview.value = false
  }
}

async function deleteReview(reviewId: number) {
  if (!business.value || !confirm(t('business.confirmDeleteReview'))) return
  try {
    await api.delete(`/businesses/${business.value.slug}/reviews/${reviewId}`)
    toast.success(t('business.reviewDeleted'))
    await loadBusiness()
  } catch (error) {
    console.warn('Failed to delete review', error)
    toast.error(t('business.reviewFailed'))
  }
}

async function share() {
  if (!business.value) return
  const shareData = { title: business.value.name, url: window.location.href }
  try {
    if (navigator.share) {
      await navigator.share(shareData)
    } else {
      await navigator.clipboard.writeText(window.location.href)
    }
  } catch (error) {
    console.warn('Share failed', error)
  }
}

async function submitAction(){
  if(!business.value || !actionModal.value) return
  if(actionModal.value==='claim' && !authStore.isAuthenticated){ router.push({name:'login',query:{redirect:route.fullPath}}); return }
  actionLoading.value=true
  try {
    if(actionModal.value==='claim') await api.post(`/businesses/${business.value.slug}/claim`,{role:actionForm.value.role,note:actionForm.value.message})
    else if(actionModal.value==='report') await api.post(`/businesses/${business.value.slug}/report`,{reason:actionForm.value.reason,details:actionForm.value.details})
    else await api.post(`/businesses/${business.value.slug}/inquiries`,{type:actionModal.value,name:actionForm.value.name,phone:actionForm.value.phone,email:actionForm.value.email||undefined,preferred_at:actionForm.value.preferred_at||undefined,message:actionForm.value.message||undefined})
    toast.success(actionModal.value==='report'?'Thanks — we will verify it.':'Request sent successfully.')
    actionModal.value=null
  } catch(error:any){ toast.error(error?.response?.data?.message||'Could not send your request.') } finally { actionLoading.value=false }
}

onMounted(() => {
  loadBusiness()
  loadSimilar()
})

// Vue Router reuses this same component instance when navigating between two
// /business/:slug routes (e.g. clicking a "You might also like" card), so
// onMounted alone won't refire — reload whenever the slug itself changes.
watch(slug, () => {
  window.scrollTo({ top: 0 })
  loadBusiness()
  loadSimilar()
})
</script>

<template>
  <BusinessDetailSkeleton v-if="loading" />

  <div v-else-if="failed || !business" class="p-6">
    <EmptyState :message="t('business.loadFailed')" />
  </div>

  <div v-else class="pb-24">
    <div class="relative h-64 overflow-hidden ec-gradient">
      <img
        v-if="business.cover_image && !coverFailed"
        :src="business.cover_image"
        :alt="business.name"
        :class="coverIsSquareLogo ? 'h-full w-full object-contain p-10' : 'h-full w-full object-cover'"
        @load="onCoverLoad"
        @error="onCoverError"
      />
      <div v-else class="absolute inset-0 flex flex-col items-center justify-center text-white/90">
        <img src="/default-business-photo.png" alt="" class="h-24 w-24 object-contain opacity-45 mb-2" />
        <span class="text-xs font-semibold tracking-[.16em] uppercase opacity-70">Easy Cyprus</span>
      </div>
      <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-slate-950/15 pointer-events-none"></div>
      <button
        class="absolute top-5 left-4 h-10 w-10 bg-white/90 backdrop-blur rounded-2xl shadow-lg flex items-center justify-center"
        @click="goBack"
      >
        <ArrowLeft :size="18" />
      </button>
      <button class="absolute top-5 right-4 h-10 w-10 bg-white/90 backdrop-blur rounded-2xl shadow-lg flex items-center justify-center" @click="toggleFavorite">
        <Heart :size="18" :fill="business.is_favorited ? '#f5a623' : 'none'" :style="{ color: business.is_favorited ? '#f5a623' : '#16324a' }" />
      </button>
    </div>

    <div class="px-5 -mt-9 relative z-10">
      <div class="ec-card p-5">
        <div class="flex items-center gap-1">
          <h1 class="font-extrabold text-xl tracking-tight" style="color: var(--color-navy)">{{ business.name }}</h1>
          <BadgeCheck v-if="business.is_verified" :size="16" style="color: var(--color-teal)" />
        </div>
        <p class="text-xs mt-1" style="color: var(--color-muted)">
          {{ business.category ? localizedName(business.category, locale) : '' }} · {{ business.city ? localizedName(business.city, locale) : '' }}
        </p>
        <div class="flex items-center gap-1 mt-2 text-sm">
          <Star :size="15" fill="#f5a623" style="color: #f5a623" />
          <span class="font-semibold">{{ business.rating_avg.toFixed(1) }}</span>
          <span style="color: var(--color-muted)">({{ business.rating_count }} {{ t('business.reviews').toLowerCase() }})</span>
        </div>
        <p v-if="business.description" class="text-sm mt-3" style="color: var(--color-navy)">{{ business.description }}</p>

        <div class="grid grid-cols-3 gap-2 mt-5">
          <a v-if="business.phone" :href="`tel:${business.phone}`" class="ec-btn-primary text-xs px-3 py-2.5 flex items-center justify-center gap-1">
            <Phone :size="14" /> {{ t('common.call') }}
          </a>
          <a v-if="business.whatsapp" :href="`https://wa.me/${business.whatsapp.replace(/[^0-9]/g, '')}`" target="_blank" rel="noopener" class="text-xs px-3 py-2.5 rounded-2xl bg-[#25D366] text-white flex items-center justify-center gap-1">
            <MessageCircle :size="14" /> {{ t('common.whatsapp') }}
          </a>
          <a v-if="business.website" :href="business.website" target="_blank" rel="noopener" class="text-xs px-3 py-2.5 rounded-2xl bg-slate-50 flex items-center justify-center gap-1 border border-slate-100">
            <GlobeIcon :size="14" /> {{ t('common.website') }}
          </a>
          <a v-if="business.social?.instagram" :href="business.social.instagram" target="_blank" rel="noopener" class="text-xs px-3 py-2.5 rounded-2xl bg-slate-50 flex items-center justify-center gap-1 border border-slate-100">
            <Instagram :size="14" /> {{ t('common.instagram') }}
          </a>
          <button class="text-xs px-3 py-2.5 rounded-2xl bg-slate-50 flex items-center justify-center gap-1 border border-slate-100" @click="share">
            <Share2 :size="14" /> {{ t('common.share') }}
          </button>
        </div>

        <div v-if="business.address" class="flex items-start gap-2 mt-4 text-xs" style="color: var(--color-muted)">
          <MapPin :size="14" class="mt-0.5 shrink-0" />
          <a
            :href="`https://maps.google.com/?q=${encodeURIComponent(business.address)}`"
            target="_blank"
            rel="noopener"
            class="underline"
          >
            {{ business.address }}
          </a>
        </div>

        <div class="grid grid-cols-2 gap-2 mt-5 pt-4 border-t border-slate-100">
          <button class="py-3 rounded-2xl bg-teal-50 text-teal-800 text-xs font-bold flex items-center justify-center gap-2" @click="actionModal='booking'"><CalendarCheck :size="15"/> Book / inquire</button>
          <button class="py-3 rounded-2xl bg-orange-50 text-orange-700 text-xs font-bold flex items-center justify-center gap-2" @click="actionModal='quote'"><MessageSquareText :size="15"/> Request a quote</button>
          <button v-if="!business.is_verified" class="py-2.5 rounded-2xl bg-slate-50 text-slate-600 text-[11px] font-semibold flex items-center justify-center gap-2" @click="actionModal='claim'"><ShieldCheck :size="14"/> Claim this business</button>
          <button class="py-2.5 rounded-2xl bg-slate-50 text-slate-600 text-[11px] font-semibold flex items-center justify-center gap-2" @click="actionModal='report'"><Flag :size="14"/> Suggest an edit</button>
        </div>
        <div class="mt-3 flex items-center justify-between text-[10px] text-slate-400">
          <span>{{ business.is_open_now === true ? '● Open now' : business.is_open_now === false ? '● Closed now' : 'Hours not confirmed' }}</span>
          <span v-if="business.last_verified_at">Verified {{ new Date(business.last_verified_at).toLocaleDateString() }}</span>
        </div>
      </div>

      <div v-if="hoursEntries.length" class="ec-card p-4 mt-4">
        <h2 class="font-semibold text-sm mb-2" style="color: var(--color-navy)">{{ t('business.hours') }}</h2>
        <div class="grid grid-cols-2 gap-y-1 text-xs" style="color: var(--color-muted)">
          <template v-for="[day, hours] in hoursEntries" :key="day">
            <span class="capitalize">{{ day }}</span>
            <span>{{ hours }}</span>
          </template>
        </div>
      </div>

      <div v-if="business.menus?.length" class="ec-card p-4 mt-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('business.menu') }}</h2>
        <div v-for="menu in business.menus" :key="menu.id" class="mb-3 last:mb-0">
          <p class="text-xs font-semibold mb-1" style="color: var(--color-teal)">{{ menu.name }}</p>
          <div v-for="item in menu.items" :key="item.id" class="flex justify-between text-sm py-1 border-b border-gray-50 last:border-0">
            <span style="color: var(--color-navy)">{{ item.name }}</span>
            <span class="font-medium" style="color: var(--color-navy)">€{{ item.price.toFixed(2) }}</span>
          </div>
        </div>
      </div>

      <div v-if="business.projects?.length" class="ec-card p-4 mt-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('dashboard.projects') }}</h2>
        <div class="grid grid-cols-2 gap-3">
          <RouterLink
            v-for="project in business.projects"
            :key="project.id"
            :to="`/project/${project.id}`"
            class="rounded-xl overflow-hidden border border-gray-100"
          >
            <div class="h-24 bg-gray-100">
              <img v-if="project.images[0]" :src="project.images[0]" class="w-full h-full object-cover" />
            </div>
            <div class="p-2">
              <p class="text-xs font-semibold truncate" style="color: var(--color-navy)">{{ project.title }}</p>
              <p v-if="project.price" class="text-xs mt-0.5" style="color: var(--color-teal)">{{ project.currency }} {{ Number(project.price).toLocaleString() }}</p>
            </div>
          </RouterLink>
        </div>
      </div>

      <div v-if="business.products?.length" class="ec-card p-4 mt-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('dashboard.products') }}</h2>
        <div class="grid grid-cols-2 gap-3">
          <div v-for="product in business.products" :key="product.id" class="rounded-xl overflow-hidden border border-gray-100">
            <div class="h-20 bg-gray-100">
              <img v-if="product.image" :src="product.image" class="w-full h-full object-cover" />
            </div>
            <div class="p-2">
              <p class="text-xs font-semibold truncate" style="color: var(--color-navy)">{{ product.name }}</p>
              <p v-if="product.price" class="text-xs mt-0.5" style="color: var(--color-teal)">€{{ Number(product.price).toFixed(2) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="business.services?.length" class="ec-card p-4 mt-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('dashboard.services') }}</h2>
        <div v-for="service in business.services" :key="service.id" class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
          <div class="min-w-0">
            <p class="text-sm" style="color: var(--color-navy)">{{ service.name }}</p>
            <p v-if="service.description" class="text-xs mt-0.5 truncate" style="color: var(--color-muted)">{{ service.description }}</p>
          </div>
          <div class="text-right shrink-0 ml-2">
            <p v-if="service.price" class="text-sm font-medium" style="color: var(--color-navy)">€{{ Number(service.price).toFixed(2) }}</p>
            <p v-if="service.duration_minutes" class="text-xs" style="color: var(--color-muted)">{{ service.duration_minutes }} {{ t('project.minutes') }}</p>
          </div>
        </div>
      </div>

      <div class="ec-card p-4 mt-4">
        <h2 class="font-semibold text-sm mb-3" style="color: var(--color-navy)">{{ t('business.reviews') }}</h2>

        <div class="mb-4 pb-4 border-b border-gray-50">
          <p class="text-xs mb-1" style="color: var(--color-muted)">{{ t('business.yourRating') }}</p>
          <div class="flex gap-1 mb-2">
            <button v-for="n in 5" :key="n" @click="newRating = n">
              <Star :size="20" :fill="n <= newRating ? '#f5a623' : 'none'" style="color: #f5a623" />
            </button>
          </div>
          <textarea
            v-model="newReviewBody"
            rows="2"
            class="w-full text-sm border border-gray-100 rounded-xl p-2 outline-none"
            :placeholder="t('business.reviewPlaceholder')"
          />
          <button class="ec-btn-primary text-xs px-4 py-2 mt-2" :disabled="submittingReview" @click="submitReview">
            {{ t('business.submitReview') }}
          </button>
        </div>

        <EmptyState v-if="!business.reviews?.length" :message="t('business.noReviews')" />
        <div v-else class="flex flex-col gap-3">
          <div v-for="review in business.reviews" :key="review.id">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium" style="color: var(--color-navy)">{{ review.user_name }}</span>
              <div class="flex items-center gap-2">
                <div class="flex items-center gap-0.5">
                  <Star v-for="n in review.rating" :key="n" :size="12" fill="#f5a623" style="color: #f5a623" />
                </div>
                <button v-if="review.is_own" class="text-xs" style="color: var(--color-muted)" @click="deleteReview(review.id)">
                  {{ t('common.delete') }}
                </button>
              </div>
            </div>
            <p v-if="review.body" class="text-xs mt-1" style="color: var(--color-muted)">{{ review.body }}</p>
          </div>
        </div>
      </div>

      <section v-if="similar.length" class="mt-6">
        <h2 class="font-bold text-sm mb-3" style="color: var(--color-navy)">{{ t('business.similar') }}</h2>
        <div class="flex gap-3 overflow-x-auto pb-1">
          <BusinessCard v-for="b in similar" :key="b.id" :business="b" horizontal />
        </div>
      </section>
    </div>

    <BottomNav />

    <Teleport to="body"><div v-if="actionModal" class="fixed inset-0 z-[70] bg-slate-950/50 backdrop-blur-sm flex items-end justify-center" @click.self="actionModal=null"><form class="bg-white w-full max-w-[520px] rounded-t-[2rem] p-5 pb-8" @submit.prevent="submitAction"><div class="h-1 w-10 bg-slate-200 rounded-full mx-auto mb-5"></div><h3 class="text-lg font-extrabold capitalize">{{ actionModal==='quote'?'Request a quote':actionModal==='booking'?'Book or inquire':actionModal==='claim'?'Claim this business':'Suggest an edit' }}</h3><p class="text-xs text-slate-500 mt-1 mb-4">{{ business.name }}</p>
      <template v-if="actionModal==='claim'"><input v-model="actionForm.role" required placeholder="Your role" class="w-full rounded-2xl p-3 text-sm mb-3"/><textarea v-model="actionForm.message" rows="3" placeholder="Tell us how we can verify you" class="w-full rounded-2xl p-3 text-sm"/></template>
      <template v-else-if="actionModal==='report'"><select v-model="actionForm.reason" class="w-full rounded-2xl p-3 text-sm mb-3"><option value="closed">Permanently closed</option><option value="wrong_phone">Wrong phone</option><option value="wrong_address">Wrong address</option><option value="wrong_hours">Wrong opening hours</option><option value="duplicate">Duplicate listing</option><option value="other">Other</option></select><textarea v-model="actionForm.details" rows="3" placeholder="What should we correct?" class="w-full rounded-2xl p-3 text-sm"/></template>
      <template v-else><div class="grid grid-cols-2 gap-3"><input v-model="actionForm.name" required placeholder="Your name" class="rounded-2xl p-3 text-sm"/><input v-model="actionForm.phone" required placeholder="Phone / WhatsApp" class="rounded-2xl p-3 text-sm"/></div><input v-model="actionForm.email" type="email" placeholder="Email (optional)" class="w-full rounded-2xl p-3 text-sm mt-3"/><input v-if="actionModal==='booking'" v-model="actionForm.preferred_at" type="datetime-local" class="w-full rounded-2xl p-3 text-sm mt-3"/><textarea v-model="actionForm.message" rows="3" placeholder="How can the business help?" class="w-full rounded-2xl p-3 text-sm mt-3"/></template>
      <div class="grid grid-cols-2 gap-3 mt-5"><button type="button" class="py-3 rounded-2xl bg-slate-100 text-sm font-semibold" @click="actionModal=null">Cancel</button><button class="ec-btn-primary py-3 text-sm" :disabled="actionLoading">{{ actionLoading?'Sending…':'Send request' }}</button></div></form></div></Teleport>
  </div>
</template>
