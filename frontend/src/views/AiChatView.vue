<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { ArrowUp, Building2, Globe2, Home, Menu, MessageSquarePlus, Search, ShieldCheck, Sparkles, X } from 'lucide-vue-next'
import api from '@/lib/api'
import ProjectCard from '@/components/ProjectCard.vue'
import type { BusinessProject } from '@/types'

type ChatMessage={role:'user'|'assistant';content:string;properties?:BusinessProject[]}
type UiCopy={title:string;subtitle:string;placeholder:string;notice:string;new:string;suggestions:string[]}
const { locale }=useI18n()
const conversationId=ref(localStorage.getItem('ec_ai_conversation')||'')
const messages=ref<ChatMessage[]>([])
const input=ref('')
const sending=ref(false)
const showSidebar=ref(false)
const chatEnd=ref<HTMLElement|null>(null)
const textarea=ref<HTMLTextAreaElement|null>(null)

const copy:Record<string,UiCopy>={
 en:{title:'Your Northern Cyprus property expert',subtitle:'Buy, sell, rent or find a daily stay - in any language.',placeholder:'Ask about a property, area, budget or investment…',notice:'AI can make mistakes. Confirm price, availability and legal details with the listing agent.',new:'New chat',suggestions:['Find a 2-bedroom apartment in Iskele under £150,000','I need a villa for daily rent near Kyrenia','Which areas are best for a long-term rental?','Show me investment properties for sale']},
 fa:{title:'مشاور هوشمند املاک قبرس شمالی',subtitle:'خرید، فروش، اجاره یا اقامت روزانه؛ با هر زبانی گفتگو کنید.',placeholder:'درباره ملک، منطقه، بودجه یا سرمایه‌گذاری بپرسید…',notice:'هوش مصنوعی ممکن است خطا کند. قیمت، موجودی و موارد حقوقی را با مشاور فایل تأیید کنید.',new:'گفتگوی جدید',suggestions:['آپارتمان دو خوابه در اسکله زیر ۱۵۰ هزار پوند','ویلای اجاره روزانه نزدیک گیرنه می‌خواهم','بهترین منطقه برای اجاره بلندمدت کجاست؟','فایل‌های مناسب سرمایه‌گذاری را نشان بده']},
 tr:{title:'Kuzey Kıbrıs emlak asistanınız',subtitle:'Satın alın, satın, kiralayın veya günlük konaklama bulun.',placeholder:'Mülk, bölge, bütçe veya yatırım sorun…',notice:'AI hata yapabilir. Fiyatı, müsaitliği ve hukuki detayları danışmanla doğrulayın.',new:'Yeni sohbet',suggestions:['İskele’de £150.000 altı 2 yatak odalı daire','Girne yakınında günlük villa kiralama','Uzun dönem kiralama için en iyi bölgeler','Satılık yatırım fırsatlarını göster']},
 ru:{title:'Ваш AI-консультант по недвижимости',subtitle:'Покупка, продажа, аренда и посуточное проживание на любом языке.',placeholder:'Спросите о недвижимости, районе или бюджете…',notice:'ИИ может ошибаться. Подтвердите цену, доступность и юридические детали у агента.',new:'Новый чат',suggestions:['2-комнатная квартира в Искеле до £150 000','Посуточная вилла рядом с Киренией','Лучшие районы для долгосрочной аренды','Покажите инвестиционные объекты']},
 he:{title:'יועץ הנדל״ן החכם שלך בצפון קפריסין',subtitle:'קנייה, מכירה, שכירות או אירוח יומי בכל שפה.',placeholder:'שאלו על נכס, אזור, תקציב או השקעה…',notice:'בינה מלאכותית עלולה לטעות. אשרו מחיר, זמינות ופרטים משפטיים מול הסוכן.',new:'שיחה חדשה',suggestions:['דירת 2 חדרי שינה באיסקלה עד £150,000','וילה להשכרה יומית ליד קירניה','אזורים מומלצים לשכירות ארוכה','הצג נכסים להשקעה']}
}
const ui=computed(()=>copy[locale.value]||copy.en)

function grow(){nextTick(()=>{if(textarea.value){textarea.value.style.height='0';textarea.value.style.height=Math.min(textarea.value.scrollHeight,160)+'px'}})}
function scrollDown(){nextTick(()=>chatEnd.value?.scrollIntoView({behavior:'smooth'}))}
async function send(text?:string){const value=(text??input.value).trim();if(!value||sending.value)return;messages.value.push({role:'user',content:value});input.value='';grow();sending.value=true;scrollDown();try{const {data}=await api.post('/ai/chat',{conversation_id:conversationId.value||undefined,message:value,locale:locale.value});conversationId.value=data.conversation_id;localStorage.setItem('ec_ai_conversation',data.conversation_id);messages.value.push({role:'assistant',content:data.message,properties:data.properties||[]})}catch{messages.value.push({role:'assistant',content:'I could not complete that search right now. Please try again in a moment.'})}finally{sending.value=false;scrollDown()}}
function newChat(){conversationId.value='';messages.value=[];localStorage.removeItem('ec_ai_conversation');showSidebar.value=false}
async function loadHistory(){if(!conversationId.value)return;try{const {data}=await api.get(`/ai/conversations/${conversationId.value}`);messages.value=(data.messages||[]).map((m:any)=>({role:m.role,content:m.content}))}catch{newChat()}}
function keydown(e:KeyboardEvent){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();send()}}
function typingDelay(index:number){return {animationDelay:`${index*120}ms`}}
onMounted(loadHistory)
</script>

<template>
  <div class="min-h-screen flex text-slate-900" :dir="['fa','he'].includes(locale)?'rtl':'ltr'">
    <div v-if="showSidebar" class="fixed inset-0 bg-slate-950/30 z-40 lg:hidden" @click="showSidebar=false"/>
    <aside class="fixed lg:sticky top-0 h-screen z-50 w-72 bg-[#0d292f] text-white flex flex-col transition-transform" :class="showSidebar?'translate-x-0':'-translate-x-full lg:translate-x-0'">
      <div class="p-4 flex items-center justify-between"><RouterLink to="/home" class="flex items-center gap-2"><span class="h-9 w-9 rounded-xl bg-gradient-to-br from-teal-400 to-emerald-600 flex items-center justify-center"><Sparkles :size="18"/></span><span class="font-bold">Easy Cyprus AI</span></RouterLink><button class="lg:hidden" @click="showSidebar=false"><X :size="20"/></button></div>
      <button class="mx-3 p-3 rounded-xl border border-white/15 hover:bg-white/10 flex items-center gap-2 text-sm" @click="newChat"><MessageSquarePlus :size="17"/>{{ui.new}}</button>
      <div class="px-4 mt-7"><p class="text-[10px] uppercase tracking-[.16em] text-white/40 mb-3">Assistant capabilities</p><div class="space-y-3 text-xs text-white/65"><p class="flex gap-2"><Search :size="15" class="text-teal-300"/>Search verified listings</p><p class="flex gap-2"><Globe2 :size="15" class="text-teal-300"/>Speak any language</p><p class="flex gap-2"><Building2 :size="15" class="text-teal-300"/>Buy, rent & daily stays</p><p class="flex gap-2"><ShieldCheck :size="15" class="text-teal-300"/>Grounded in live data</p></div></div>
      <div class="mt-auto p-4"><RouterLink to="/home" class="rounded-xl bg-white/8 p-3 flex items-center gap-2 text-sm text-white/75"><Home :size="16"/>Back to Easy Cyprus</RouterLink></div>
    </aside>

    <main class="flex-1 min-w-0 min-h-screen flex flex-col">
      <header class="h-16 px-4 md:px-6 flex items-center justify-between border-b border-slate-200/70 bg-white/80 backdrop-blur-xl sticky top-0 z-30"><div class="flex items-center gap-3"><button class="lg:hidden h-9 w-9 rounded-xl bg-slate-100 flex items-center justify-center" @click="showSidebar=true"><Menu :size="19"/></button><div><p class="font-bold text-sm">Easy Cyprus AI</p><p class="text-[10px] text-emerald-600 flex items-center gap-1"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Property assistant online</p></div></div><RouterLink to="/explore?category=real-estate" class="hidden sm:flex items-center gap-2 text-xs font-semibold px-3 py-2 rounded-xl bg-white border border-slate-200"><Building2 :size="15"/>Browse properties</RouterLink></header>

      <section class="flex-1 w-full max-w-4xl mx-auto px-4 md:px-8 pb-52">
        <div v-if="!messages.length" class="pt-[9vh] md:pt-[13vh]">
          <div class="mx-auto h-16 w-16 rounded-2xl bg-gradient-to-br from-teal-500 via-emerald-500 to-cyan-600 shadow-xl shadow-teal-600/20 flex items-center justify-center text-white"><Sparkles :size="29"/></div>
          <h1 class="mt-6 text-center text-2xl md:text-4xl font-extrabold tracking-tight text-slate-900">{{ui.title}}</h1><p class="text-center text-slate-500 mt-3 max-w-xl mx-auto text-sm md:text-base">{{ui.subtitle}}</p>
          <div class="grid sm:grid-cols-2 gap-3 mt-9 max-w-2xl mx-auto"><button v-for="(s,i) in ui.suggestions" :key="s" class="group bg-white hover:border-teal-300 border border-slate-200 rounded-2xl p-4 text-left text-sm text-slate-700 shadow-sm hover:shadow-md transition-all" @click="send(s)"><span class="h-7 w-7 rounded-lg mb-3 flex items-center justify-center" :class="i%2?'bg-orange-50 text-orange-600':'bg-teal-50 text-teal-700'"><Building2 v-if="i%2" :size="14"/><Sparkles v-else :size="14"/></span>{{s}}</button></div>
        </div>
        <div v-else class="pt-7 space-y-7">
          <div v-for="(m,i) in messages" :key="i" class="flex gap-3" :class="m.role==='user'?'justify-end':''">
            <div v-if="m.role==='assistant'" class="h-9 w-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center shrink-0"><Sparkles :size="16"/></div>
            <div class="max-w-[85%] md:max-w-[76%]" :class="m.role==='user'?'bg-[#0d292f] text-white rounded-3xl rounded-br-lg px-5 py-3.5':'pt-1 text-slate-700'">
              <p class="text-sm leading-6 whitespace-pre-wrap">{{m.content}}</p>
              <div v-if="m.properties?.length" class="grid sm:grid-cols-2 gap-3 mt-4 min-w-[min(72vw,650px)]"><ProjectCard v-for="p in m.properties" :key="p.id" :project="p"/></div>
            </div>
          </div>
          <div v-if="sending" class="flex items-center gap-3"><div class="h-9 w-9 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center"><Sparkles :size="16"/></div><div class="flex gap-1 bg-white rounded-2xl px-4 py-3 border border-slate-100"><span v-for="n in 3" :key="n" class="h-1.5 w-1.5 bg-teal-500 rounded-full animate-bounce" :style="typingDelay(n)"></span></div></div>
          <div ref="chatEnd"/>
        </div>
      </section>

      <div class="fixed bottom-0 left-0 lg:left-72 right-0 z-30 bg-gradient-to-t from-[#f5f8f7] via-[#f5f8f7] to-transparent pt-10 pb-4 px-3">
        <form class="max-w-3xl mx-auto bg-white border border-slate-200 rounded-[1.6rem] shadow-2xl shadow-slate-900/10 p-2.5 flex items-end gap-2" @submit.prevent="send()"><textarea ref="textarea" v-model="input" rows="1" :placeholder="ui.placeholder" class="flex-1 resize-none bg-transparent outline-none px-3 py-2.5 text-sm leading-5 max-h-40" @input="grow" @keydown="keydown"/><button type="submit" :disabled="!input.trim()||sending" class="h-10 w-10 rounded-2xl bg-[#0d292f] disabled:bg-slate-200 text-white flex items-center justify-center shrink-0 transition-colors"><ArrowUp :size="19"/></button></form>
        <p class="max-w-3xl mx-auto text-center text-[10px] text-slate-400 mt-2 px-5">{{ui.notice}}</p>
      </div>
    </main>
  </div>
</template>
