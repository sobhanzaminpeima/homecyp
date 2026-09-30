<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { ArrowUp, BedDouble, Building2, Check, ChevronDown, Globe2, Home, Menu, MessageSquarePlus, Search, ShieldCheck, Sparkles, X } from 'lucide-vue-next'
import api from '@/lib/api'
import ProjectCard from '@/components/ProjectCard.vue'
import { applyDirection, persistLocale, type SupportedLocale } from '@/i18n'
import type { BusinessProject } from '@/types'

type ChatMessage = { role: 'user' | 'assistant'; content: string; properties?: BusinessProject[] }
type ChatSession = { id: string; title: string; updatedAt: number }
type UiCopy = {
  title: string; subtitle: string; placeholder: string; notice: string; newChat: string; recent: string
  online: string; browse: string; back: string; capabilities: string; emptyHistory: string; error: string
  suggestionLabels: string[]; suggestions: string[]; capabilityLabels: string[]
}

const { locale } = useI18n()
const conversationId = ref(localStorage.getItem('ec_ai_conversation') || '')
const messages = ref<ChatMessage[]>([])
const sessions = ref<ChatSession[]>(readSessions())
const input = ref('')
const sending = ref(false)
const showSidebar = ref(false)
const showLanguages = ref(false)
const chatEnd = ref<HTMLElement | null>(null)
const textarea = ref<HTMLTextAreaElement | null>(null)

const languages: { code: SupportedLocale; label: string; short: string }[] = [
  { code: 'en', label: 'English', short: 'EN' }, { code: 'fa', label: 'فارسی', short: 'FA' },
  { code: 'tr', label: 'Türkçe', short: 'TR' }, { code: 'ru', label: 'Русский', short: 'RU' },
  { code: 'he', label: 'עברית', short: 'HE' },
]

const copy: Record<string, UiCopy> = {
  en: { title: 'Find your place in Northern Cyprus', subtitle: 'One intelligent conversation for buying, selling, long-term renting and daily stays. Ask naturally in any language.', placeholder: 'Describe the property you are looking for…', notice: 'AI can make mistakes. Confirm price, availability and legal details with the listing agent.', newChat: 'New chat', recent: 'Recent chats', online: 'Property assistant online', browse: 'Browse properties', back: 'Back to Easy Cyprus', capabilities: 'What I can help with', emptyHistory: 'Your conversations will appear here.', error: 'I could not complete that search. Please try again in a moment.', suggestionLabels: ['Buy a home', 'Daily stays', 'Long-term rent', 'Invest'], suggestions: ['Find a 2-bedroom apartment in Iskele under £150,000', 'I need a villa for a daily stay near Kyrenia', 'Which areas are best for a long-term rental?', 'Show me investment properties for sale'], capabilityLabels: ['Search verified listings', 'Speak your language', 'Buy, rent and daily stays', 'Use live property data'] },
  fa: { title: 'ملک مناسب‌تان را در قبرس شمالی پیدا کنید', subtitle: 'یک گفتگوی هوشمند برای خرید، فروش، اجاره بلندمدت و اقامت روزانه؛ کاملاً به زبان شما.', placeholder: 'ملکی که دنبالش هستید را توضیح دهید…', notice: 'هوش مصنوعی ممکن است خطا کند. قیمت، موجودی و موارد حقوقی را با مشاور فایل تأیید کنید.', newChat: 'گفتگوی جدید', recent: 'گفتگوهای اخیر', online: 'مشاور هوشمند آنلاین است', browse: 'مشاهده فایل‌ها', back: 'بازگشت به ایزی سایپرس', capabilities: 'چه کمکی می‌توانم بکنم؟', emptyHistory: 'گفتگوهای شما اینجا نمایش داده می‌شوند.', error: 'در حال حاضر جستجو کامل نشد. لطفاً چند لحظه دیگر دوباره امتحان کنید.', suggestionLabels: ['خرید ملک', 'اجاره روزانه', 'اجاره بلندمدت', 'سرمایه‌گذاری'], suggestions: ['آپارتمان دو خوابه در اسکله زیر ۱۵۰ هزار پوند', 'ویلای اجاره روزانه نزدیک گیرنه می‌خواهم', 'بهترین منطقه برای اجاره بلندمدت کجاست؟', 'فایل‌های مناسب سرمایه‌گذاری را نشان بده'], capabilityLabels: ['جستجو در فایل‌های تأییدشده', 'گفتگو به زبان شما', 'خرید، اجاره و اقامت روزانه', 'پیشنهاد بر اساس اطلاعات واقعی'] },
  tr: { title: 'Kuzey Kıbrıs’taki yerinizi bulun', subtitle: 'Satın alma, satış, uzun dönem kiralama ve günlük konaklama için tek akıllı sohbet.', placeholder: 'Aradığınız mülkü anlatın…', notice: 'AI hata yapabilir. Fiyatı, müsaitliği ve hukuki detayları danışmanla doğrulayın.', newChat: 'Yeni sohbet', recent: 'Son sohbetler', online: 'Emlak asistanı çevrimiçi', browse: 'İlanlara göz at', back: 'Easy Cyprus’a dön', capabilities: 'Size nasıl yardımcı olabilirim?', emptyHistory: 'Sohbetleriniz burada görünecek.', error: 'Arama şu anda tamamlanamadı. Lütfen biraz sonra tekrar deneyin.', suggestionLabels: ['Satın al', 'Günlük konaklama', 'Uzun dönem kira', 'Yatırım'], suggestions: ['İskele’de £150.000 altı 2 yatak odalı daire', 'Girne yakınında günlük villa kiralama', 'Uzun dönem kiralama için en iyi bölgeler', 'Satılık yatırım fırsatlarını göster'], capabilityLabels: ['Onaylı ilanları ara', 'Kendi dilinizde konuşun', 'Satın alma ve kiralama', 'Canlı ilan verilerini kullan'] },
  ru: { title: 'Найдите своё место на Северном Кипре', subtitle: 'Один умный чат для покупки, продажи, долгосрочной и посуточной аренды.', placeholder: 'Опишите недвижимость, которую вы ищете…', notice: 'ИИ может ошибаться. Подтвердите цену, доступность и юридические детали у агента.', newChat: 'Новый чат', recent: 'Недавние чаты', online: 'Консультант онлайн', browse: 'Смотреть объекты', back: 'Назад в Easy Cyprus', capabilities: 'Чем я могу помочь?', emptyHistory: 'Ваши чаты появятся здесь.', error: 'Не удалось выполнить поиск. Попробуйте ещё раз через минуту.', suggestionLabels: ['Купить', 'Посуточно', 'Долгосрочно', 'Инвестиции'], suggestions: ['2-комнатная квартира в Искеле до £150 000', 'Посуточная вилла рядом с Киренией', 'Лучшие районы для долгосрочной аренды', 'Покажите инвестиционные объекты'], capabilityLabels: ['Проверенные объявления', 'Общение на вашем языке', 'Покупка и аренда', 'Актуальные данные объектов'] },
  he: { title: 'מצאו את המקום שלכם בצפון קפריסין', subtitle: 'שיחה חכמה אחת לקנייה, מכירה, שכירות ארוכה ואירוח יומי.', placeholder: 'תארו את הנכס שאתם מחפשים…', notice: 'בינה מלאכותית עלולה לטעות. אשרו מחיר, זמינות ופרטים משפטיים מול הסוכן.', newChat: 'שיחה חדשה', recent: 'שיחות אחרונות', online: 'יועץ הנדל״ן מחובר', browse: 'עיון בנכסים', back: 'חזרה ל-Easy Cyprus', capabilities: 'איך אוכל לעזור?', emptyHistory: 'השיחות שלכם יופיעו כאן.', error: 'לא ניתן היה להשלים את החיפוש. נסו שוב בעוד רגע.', suggestionLabels: ['קניית נכס', 'אירוח יומי', 'שכירות ארוכה', 'השקעה'], suggestions: ['דירת 2 חדרי שינה באיסקלה עד £150,000', 'וילה להשכרה יומית ליד קירניה', 'אזורים מומלצים לשכירות ארוכה', 'הצג נכסים להשקעה'], capabilityLabels: ['חיפוש בנכסים מאומתים', 'שיחה בשפה שלכם', 'קנייה ושכירות', 'מידע עדכני מהמאגר'] },
}
const ui = computed(() => copy[locale.value] || copy.en)
const currentLanguage = computed(() => languages.find(item => item.code === locale.value) || languages[0])
const isRtl = computed(() => ['fa', 'he'].includes(locale.value))

function readSessions(): ChatSession[] { try { return JSON.parse(localStorage.getItem('ec_ai_sessions') || '[]') } catch { return [] } }
function saveSessions() { localStorage.setItem('ec_ai_sessions', JSON.stringify(sessions.value.slice(0, 12))) }
function rememberSession(id: string, title: string) { sessions.value = [{ id, title, updatedAt: Date.now() }, ...sessions.value.filter(s => s.id !== id)].slice(0, 12); saveSessions() }
function grow() { nextTick(() => { if (textarea.value) { textarea.value.style.height = '0'; textarea.value.style.height = `${Math.min(textarea.value.scrollHeight, 160)}px` } }) }
function scrollDown(behavior: ScrollBehavior = 'smooth') { nextTick(() => chatEnd.value?.scrollIntoView({ behavior })) }
function changeLanguage(code: SupportedLocale) { locale.value = code; persistLocale(code); applyDirection(code); showLanguages.value = false }

async function send(text?: string) {
  const value = (text ?? input.value).trim(); if (!value || sending.value) return
  messages.value.push({ role: 'user', content: value }); input.value = ''; grow(); sending.value = true; scrollDown()
  try {
    const { data } = await api.post('/ai/chat', { conversation_id: conversationId.value || undefined, message: value, locale: locale.value })
    conversationId.value = data.conversation_id; localStorage.setItem('ec_ai_conversation', data.conversation_id)
    rememberSession(data.conversation_id, messages.value.find(m => m.role === 'user')?.content.slice(0, 54) || ui.value.newChat)
    messages.value.push({ role: 'assistant', content: data.message, properties: data.properties || [] })
  } catch { messages.value.push({ role: 'assistant', content: ui.value.error }) }
  finally { sending.value = false; scrollDown() }
}
function newChat() { conversationId.value = ''; messages.value = []; localStorage.removeItem('ec_ai_conversation'); showSidebar.value = false; nextTick(() => textarea.value?.focus()) }
async function openSession(id: string) { conversationId.value = id; localStorage.setItem('ec_ai_conversation', id); showSidebar.value = false; await loadHistory(); scrollDown('auto') }
async function loadHistory() { if (!conversationId.value) return; try { const { data } = await api.get(`/ai/conversations/${conversationId.value}`); messages.value = (data.messages || []).map((m: ChatMessage) => ({ role: m.role, content: m.content, properties: m.properties || [] })) } catch { newChat() } }
function keydown(e: KeyboardEvent) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send() } }
function typingDelay(index: number) { return { animationDelay: `${index * 120}ms` } }
watch(locale, code => applyDirection(code as SupportedLocale))
onMounted(() => { loadHistory(); nextTick(() => textarea.value?.focus()) })
</script>

<template>
  <div class="min-h-screen flex text-slate-900 bg-[#f7f8f7]" :dir="isRtl ? 'rtl' : 'ltr'">
    <Transition name="fade"><div v-if="showSidebar" class="fixed inset-0 bg-slate-950/35 backdrop-blur-[2px] z-40 lg:hidden" @click="showSidebar=false" /></Transition>
    <aside class="fixed lg:sticky start-0 top-0 h-screen z-50 w-[18rem] bg-[#102e32] text-white flex flex-col transition-transform duration-300" :class="showSidebar ? 'translate-x-0' : isRtl ? 'translate-x-full lg:translate-x-0' : '-translate-x-full lg:translate-x-0'">
      <div class="p-4 flex items-center justify-between">
        <RouterLink to="/home" class="flex items-center gap-2.5"><span class="h-9 w-9 rounded-xl bg-gradient-to-br from-[#31b6a4] to-[#168070] flex items-center justify-center shadow-lg"><Sparkles :size="17" /></span><span class="font-bold tracking-tight">Easy Cyprus <span class="text-[#72d8c9]">AI</span></span></RouterLink>
        <button class="lg:hidden p-2 rounded-lg hover:bg-white/10" :aria-label="ui.back" @click="showSidebar=false"><X :size="19" /></button>
      </div>
      <button class="mx-3 mt-1 p-3 rounded-xl border border-white/15 hover:bg-white/10 flex items-center gap-2.5 text-sm font-semibold transition" @click="newChat"><MessageSquarePlus :size="17" />{{ ui.newChat }}</button>
      <div class="px-4 mt-7 mb-2 text-[10px] uppercase tracking-[.16em] text-white/40 font-semibold">{{ ui.recent }}</div>
      <div class="px-2 overflow-y-auto flex-1">
        <button v-for="session in sessions" :key="session.id" class="w-full text-start truncate rounded-xl px-3 py-2.5 text-sm transition" :class="session.id===conversationId?'bg-white/10 text-white':'text-white/60 hover:bg-white/5 hover:text-white/90'" @click="openSession(session.id)">{{ session.title }}</button>
        <p v-if="!sessions.length" class="mx-2 rounded-xl border border-white/8 bg-white/5 p-3 text-xs leading-5 text-white/45">{{ ui.emptyHistory }}</p>
      </div>
      <div class="px-4 py-4 border-t border-white/8">
        <p class="text-[10px] uppercase tracking-[.16em] text-white/40 mb-3 font-semibold">{{ ui.capabilities }}</p>
        <div class="space-y-2.5 text-xs text-white/65">
          <p v-for="(label,i) in ui.capabilityLabels" :key="label" class="flex items-center gap-2"><component :is="[Search,Globe2,BedDouble,ShieldCheck][i]" :size="14" class="text-[#72d8c9]" />{{ label }}</p>
        </div>
        <RouterLink to="/home" class="mt-4 rounded-xl bg-white/8 hover:bg-white/12 p-3 flex items-center gap-2 text-sm text-white/75 transition"><Home :size="16" />{{ ui.back }}</RouterLink>
      </div>
    </aside>

    <main class="flex-1 min-w-0 min-h-screen flex flex-col">
      <header class="h-16 px-3 md:px-6 flex items-center justify-between border-b border-slate-200/70 bg-white/80 backdrop-blur-xl sticky top-0 z-30">
        <div class="flex items-center gap-3"><button type="button" class="lg:hidden h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center" :aria-label="ui.recent" @click.stop="showSidebar = !showSidebar"><Menu :size="19" /></button><div><p class="font-bold text-sm tracking-tight">Easy Cyprus AI</p><p class="text-[10px] text-emerald-700 flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />{{ ui.online }}</p></div></div>
        <div class="flex items-center gap-2">
          <div class="relative"><button type="button" class="h-10 px-3 rounded-xl border border-slate-200 bg-white flex items-center gap-2 text-xs font-semibold hover:border-slate-300" aria-label="Language" @click="showLanguages=!showLanguages"><Globe2 :size="15" /><span>{{ currentLanguage.short }}</span><ChevronDown :size="13" /></button><div v-if="showLanguages" class="absolute top-12 end-0 w-40 bg-white rounded-xl border border-slate-200 shadow-xl p-1.5 z-50"><button v-for="item in languages" :key="item.code" type="button" class="w-full px-3 py-2 rounded-lg text-sm flex items-center justify-between hover:bg-slate-50" @click="changeLanguage(item.code)"><span>{{ item.label }}</span><Check v-if="locale===item.code" :size="14" class="text-teal-700" /></button></div></div>
          <RouterLink to="/explore?category=real-estate" class="hidden sm:flex items-center gap-2 text-xs font-semibold px-3 py-2.5 rounded-xl bg-[#102e32] text-white hover:bg-[#174148] transition"><Building2 :size="15" />{{ ui.browse }}</RouterLink>
        </div>
      </header>

      <section class="flex-1 w-full max-w-5xl mx-auto px-4 md:px-8 pb-52">
        <div v-if="!messages.length" class="pt-[7vh] md:pt-[11vh]">
          <div class="mx-auto h-14 w-14 rounded-2xl bg-gradient-to-br from-[#31b6a4] via-[#168d80] to-[#11616a] shadow-xl shadow-teal-800/20 flex items-center justify-center text-white"><Sparkles :size="25" /></div>
          <p class="mt-5 text-center text-[11px] uppercase tracking-[.18em] text-teal-700 font-bold">Easy Cyprus AI</p>
          <h1 class="mt-2 text-center text-3xl md:text-[2.7rem] md:leading-[1.15] font-extrabold tracking-[-.035em] text-[#102e32]">{{ ui.title }}</h1>
          <p class="text-center text-slate-500 mt-4 max-w-2xl mx-auto text-sm md:text-base leading-7">{{ ui.subtitle }}</p>
          <div class="flex justify-center mt-4"><span class="inline-flex items-center gap-2 rounded-full border border-teal-100 bg-teal-50/70 px-3 py-1.5 text-[11px] font-semibold text-teal-800"><Globe2 :size="13" /> English · فارسی · Türkçe · Русский · עברית</span></div>
          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-9 max-w-4xl mx-auto">
            <button v-for="(suggestion,i) in ui.suggestions" :key="suggestion" class="group bg-white hover:-translate-y-1 hover:border-teal-300 border border-slate-200 rounded-2xl p-4 text-start text-sm text-slate-600 shadow-sm hover:shadow-lg transition-all duration-200" @click="send(suggestion)"><span class="h-9 w-9 rounded-xl mb-5 flex items-center justify-center" :class="i%2?'bg-amber-50 text-amber-600':'bg-teal-50 text-teal-700'"><Building2 v-if="i%2" :size="17" /><Sparkles v-else :size="17" /></span><span class="block text-xs font-bold text-slate-900 mb-1">{{ ui.suggestionLabels[i] }}</span><span class="block text-xs leading-5">{{ suggestion }}</span></button>
          </div>
        </div>
        <div v-else class="pt-8 space-y-8">
          <div v-for="(message,i) in messages" :key="i" class="flex gap-3 md:gap-4" :class="message.role==='user'?'justify-end':''">
            <div v-if="message.role==='assistant'" class="h-9 w-9 rounded-xl bg-gradient-to-br from-[#31b6a4] to-[#11616a] text-white flex items-center justify-center shrink-0 shadow-sm"><Sparkles :size="16" /></div>
            <div class="max-w-[86%] md:max-w-[78%]" :class="message.role==='user'?'bg-[#102e32] text-white rounded-3xl rounded-ee-lg px-5 py-3.5 shadow-sm':'pt-1 text-slate-700'">
              <p class="text-[15px] leading-7 whitespace-pre-wrap">{{ message.content }}</p>
              <div v-if="message.properties?.length" class="grid sm:grid-cols-2 gap-3 mt-5 min-w-[min(74vw,680px)]"><ProjectCard v-for="property in message.properties" :key="property.id" :project="property" /></div>
            </div>
          </div>
          <div v-if="sending" class="flex items-center gap-3"><div class="h-9 w-9 rounded-xl bg-gradient-to-br from-[#31b6a4] to-[#11616a] text-white flex items-center justify-center"><Sparkles :size="16" /></div><div class="flex gap-1 bg-white rounded-2xl px-4 py-3 border border-slate-100 shadow-sm"><span v-for="n in 3" :key="n" class="h-1.5 w-1.5 bg-teal-500 rounded-full animate-bounce" :style="typingDelay(n)" /></div></div>
          <div ref="chatEnd" />
        </div>
      </section>

      <div class="fixed bottom-0 z-30 bg-gradient-to-t from-[#f7f8f7] via-[#f7f8f7] to-transparent pt-12 pb-[max(1rem,env(safe-area-inset-bottom))] px-3 transition-[left,right]" :class="isRtl?'left-0 right-0 lg:right-72':'left-0 right-0 lg:left-72'">
        <form class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-[1.65rem] shadow-2xl shadow-slate-900/10 p-2.5 flex items-end gap-2 focus-within:border-teal-400 focus-within:ring-4 focus-within:ring-teal-500/10 transition" @submit.prevent="send()"><textarea ref="textarea" v-model="input" rows="1" :placeholder="ui.placeholder" class="flex-1 resize-none !bg-transparent !border-0 !shadow-none outline-none px-3 py-2.5 text-[15px] leading-6 max-h-40" @input="grow" @keydown="keydown" /><button type="submit" :disabled="!input.trim()||sending" class="h-10 w-10 rounded-2xl bg-[#102e32] disabled:bg-slate-200 text-white flex items-center justify-center shrink-0 transition-all hover:scale-105 active:scale-95"><ArrowUp :size="19" /></button></form>
        <p class="max-w-3xl mx-auto text-center text-[10px] text-slate-400 mt-2 px-5">{{ ui.notice }}</p>
      </div>
    </main>
  </div>
</template>

<style scoped>
.fade-enter-active,.fade-leave-active{transition:opacity .2s ease}.fade-enter-from,.fade-leave-to{opacity:0}
</style>
