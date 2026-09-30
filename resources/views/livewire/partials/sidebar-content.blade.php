<div class="p-3">
    <a href="{{ route('home') }}" class="flex items-center gap-2.5 px-2 py-2 min-w-0">
        <span class="w-8 h-8 rounded-xl hc-primary-bg flex items-center justify-center shrink-0"><x-chat.icon name="sparkles" :size="16" /></span>
        <span class="hc-font-display font-bold truncate">HomeCyp AI</span>
    </a>
    <button type="button" wire:click="newConversation" class="hc-focusable mt-3 w-full rounded-xl border hc-border px-3 py-2.5 flex items-center gap-2.5 text-sm font-semibold hover:bg-[var(--hc-hover)] transition">
        <span class="text-xl leading-none">+</span><span>{{ __('New conversation') }}</span>
    </button>
</div>
<div class="px-4 pt-4 pb-2 text-[11px] font-semibold hc-text-secondary uppercase tracking-[.13em]">{{ __('Recent chats') }}</div>
<div class="flex-1 overflow-y-auto px-2 pb-3 text-sm">
    @if(empty($conversationHistory))
        <p class="mx-2 p-3 rounded-xl hc-text-secondary leading-relaxed" style="background:var(--hc-hover)">{{ $leadId ? __('Start a conversation to see it here') : __('Sign in to see your past conversations') }}</p>
    @else
        @foreach($conversationHistory as $conv)
            <button type="button" wire:click="switchConversation('{{ $conv['uuid'] }}')" class="hc-focusable w-full text-start px-3 py-2.5 rounded-xl mb-1 truncate block transition hover:bg-[var(--hc-hover)]" style="{{ $conv['is_current'] ? 'background:var(--hc-hover);color:var(--hc-text);font-weight:600' : 'color:var(--hc-text-secondary)' }}">{{ \Illuminate\Support\Str::limit($conv['title'], 38) }}</button>
        @endforeach
    @endif
</div>
<div class="p-3 border-t hc-border space-y-1">
    <a href="{{ route('listings') }}" class="hc-focusable rounded-xl px-3 py-2.5 flex items-center gap-2 text-sm hc-text-secondary hover:bg-[var(--hc-hover)] transition"><x-chat.icon name="home" :size="17" /> <span>{{ __('Explore properties') }}</span></a>
    <button type="button" wire:click="{{ $leadId ? 'signOut' : 'toggleSignIn' }}" class="hc-focusable w-full rounded-xl px-3 py-2.5 flex items-center gap-2 text-sm hc-text-secondary hover:bg-[var(--hc-hover)] transition"><x-chat.icon name="user" :size="17" /> <span>{{ $leadId ? __('Sign out') : __('Sign in') }}</span></button>
    <div class="flex items-center gap-1 pt-2 px-1">
        <label for="locale" class="sr-only">{{ __('Language') }}</label>
        <select id="locale" onchange="if(this.value) window.location.href=this.value" class="hc-focusable min-w-0 flex-1 bg-transparent text-xs hc-text-secondary border hc-border rounded-lg px-2 py-2">
            @foreach(['en'=>'English','tr'=>'Türkçe','fa'=>'فارسی','ar'=>'العربية','ru'=>'Русский','de'=>'Deutsch'] as $code => $label)
                <option value="{{ route('lang.switch', $code) }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
            @endforeach
        </select>
        <button @click="dark = !dark" type="button" aria-label="{{ __('Toggle dark mode') }}" class="hc-icon-button hc-focusable hc-text-secondary shrink-0"><x-chat.icon name="moon" :size="17" /></button>
    </div>
</div>
