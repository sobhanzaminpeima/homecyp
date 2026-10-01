<div class="flex h-full w-full" x-data="{ mobileSidebar: false }" style="background:var(--hc-bg)">

    {{-- Sidebar: conversation history (collapsible on mobile) --}}
    <aside class="hidden md:flex md:flex-col w-72 border-r shrink-0 hc-border" style="background:var(--hc-sidebar)">
        @include('livewire.partials.sidebar-content')
    </aside>

    {{-- Mobile drawer --}}
    <div x-show="mobileSidebar" x-cloak class="md:hidden fixed inset-0 z-40 bg-black/30" @click.self="mobileSidebar = false">
        <aside class="w-72 h-full flex flex-col hc-border border-r shadow-2xl" style="background:var(--hc-sidebar)">
            @include('livewire.partials.sidebar-content')
        </aside>
    </div>

    <div class="flex flex-col h-full flex-1 min-w-0"
         x-data="{ scrollToBottom() { $nextTick(() => { $refs.log.scrollTop = $refs.log.scrollHeight }) }, dragOver: false }"
         x-init="scrollToBottom()"
         @reply-received.window="scrollToBottom()"
         @message-sent.window="scrollToBottom(); $wire.processReply()"
         @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
         @drop.prevent="dragOver = false">

        <header class="flex items-center justify-between px-3 md:px-5 h-16 border-b hc-border shrink-0" style="background:color-mix(in srgb,var(--hc-bg) 88%,transparent);backdrop-filter:blur(14px)">
            <button @click="mobileSidebar = true" aria-label="{{ __('History') }}" class="md:hidden hc-focusable hc-text-secondary" style="width:44px;height:44px;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="flex-1 min-w-0 px-2">
                <div class="hc-font-display font-bold text-sm md:text-base">{{ __('North Cyprus AI advisor') }}</div>
                <div class="hidden sm:flex items-center gap-1.5 text-[11px] hc-text-secondary"><span class="w-1.5 h-1.5 rounded-full" style="background:#2f9e68"></span>{{ __('Property search · Investment · Rentals') }}</div>
            </div>
            <a href="{{ route('listings') }}" class="hidden md:inline-flex hc-focusable rounded-xl border hc-border px-3 py-2 text-sm font-medium hover:bg-[var(--hc-hover)] transition">{{ __('Explore properties') }}</a>
            <button wire:click="newConversation" aria-label="{{ __('New conversation') }}" class="md:hidden hc-focusable hc-text-secondary" style="width:44px;height:44px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
        </header>

        <main x-ref="log" role="log" aria-live="polite"
              class="flex-1 px-4 space-y-7 {{ empty($messages) ? 'overflow-hidden py-3 md:py-4' : 'overflow-y-auto py-6 md:py-8' }}">
            @if(empty($messages))
                @php
                    $welcomeTitle = $welcomeVariant['title'] ?? \App\Models\SiteSetting::get('chat_welcome_title', 'Your AI Real Estate Expert for North Cyprus');
                    $welcomeSubtitle = $welcomeVariant['subtitle'] ?? \App\Models\SiteSetting::get('chat_welcome_subtitle', 'Ask me anything about buying, investing, or renting in North Cyprus.');
                    $cardsRaw = \App\Models\SiteSetting::get('chat_suggestion_cards');
                    $cards = $cardsRaw ? json_decode($cardsRaw, true) : [
                        ['emoji' => 'home', 'label' => __('Buy a property'), 'prompt' => __('I want to find a property to buy')],
                        ['emoji' => 'trending-up', 'label' => __('Investment opportunities'), 'prompt' => __('I am interested in investment opportunities')],
                        ['emoji' => 'map-pin', 'label' => __('Daily & Airbnb rentals'), 'prompt' => __('Show me daily rentals and Airbnb options')],
                        ['emoji' => 'sparkles', 'label' => __('Get a local recommendation'), 'prompt' => __('Help me choose the right area in North Cyprus')],
                    ];
                @endphp
                <div class="max-w-3xl mx-auto h-full flex flex-col justify-center hc-empty-state hc-fade-rise">
                    <div class="text-center max-w-2xl mx-auto">
                        <div class="w-12 h-12 rounded-2xl hc-primary-bg flex items-center justify-center mx-auto mb-5 hc-shadow"><x-chat.icon name="sparkles" :size="22" /></div>
                        <p class="text-xs font-bold uppercase tracking-[.16em] hc-accent-text mb-3">HomeCyp AI</p>
                        <h1 class="hc-font-display text-3xl md:text-4xl font-bold leading-tight" style="color:var(--hc-text)">{{ __($welcomeTitle) }}</h1>
                        <p class="hc-text-secondary mt-3 text-base md:text-lg leading-relaxed">{{ __($welcomeSubtitle) }}</p>
                        <div class="inline-flex items-center gap-2 mt-4 text-xs hc-text-secondary rounded-full border hc-border px-3 py-1.5" style="background:var(--hc-surface)"><span>🌐</span>{{ __('Ask in your own language') }}</div>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-9">
                        @foreach($cards as $card)
                            @php
                                $localizedLabel = __($card['label']);
                                $localizedPrompt = __($card['prompt']);
                            @endphp
                            <button type="button" wire:click="startWithPrompt('{{ addslashes($localizedPrompt) }}')" class="hc-suggestion-card hc-focusable"><span class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:var(--hc-accent-soft);color:var(--hc-accent)"><x-chat.icon :name="$card['emoji']" :size="18" /></span><span class="text-sm font-semibold leading-snug">{{ $localizedLabel }}</span></button>
                        @endforeach
                    </div>
                </div>
            @endif

            @php $lastIndex = count($messages) - 1; @endphp
            @foreach($messages as $i => $message)
                <div class="max-w-3xl mx-auto {{ $message['role'] === 'user' ? 'flex justify-end' : '' }}">
                    <x-chat.message-bubble :role="$message['role']">
                        @if(!empty($message['attachment_url']))
                            <div class="mb-2">
                                @if($message['attachment_type'] === 'image')
                                    <img src="{{ $message['attachment_url'] }}" alt="{{ $message['attachment_name'] }}" class="rounded-lg max-h-48 object-cover">
                                @else
                                    <a href="{{ $message['attachment_url'] }}" target="_blank" rel="noopener" class="flex items-center gap-2 text-sm underline">
                                        <x-chat.icon name="file-text" :size="16" /> {{ $message['attachment_name'] }}
                                    </a>
                                @endif
                            </div>
                        @endif
                        <p class="whitespace-pre-line">{{ $message['content'] }}</p>
                    </x-chat.message-bubble>

                    @if($message['role'] === 'assistant')
                        <div class="flex gap-1 mt-1">
                            <button type="button" wire:click="rateMessage({{ $message['id'] }}, 1)" aria-label="{{ __('Helpful') }}"
                                class="hc-focusable text-xs px-1.5 py-0.5 rounded {{ ($message['feedback'] ?? null) === 1 ? 'opacity-100' : 'opacity-30 hover:opacity-70' }}">👍</button>
                            <button type="button" wire:click="rateMessage({{ $message['id'] }}, -1)" aria-label="{{ __('Not helpful') }}"
                                class="hc-focusable text-xs px-1.5 py-0.5 rounded {{ ($message['feedback'] ?? null) === -1 ? 'opacity-100' : 'opacity-30 hover:opacity-70' }}">👎</button>
                        </div>
                    @endif

                    @if($message['role'] === 'assistant' && !empty($message['property_cards']))
                        @include('livewire.partials.property-cards', ['ids' => $message['property_cards']])
                    @endif

                    @if($message['role'] === 'assistant' && !empty($message['sponsored_cards']))
                        @include('livewire.partials.sponsored-cards', ['cards' => $message['sponsored_cards']])
                    @endif

                    @if($message['role'] === 'assistant' && !empty($message['widget']))
                        @include('livewire.partials.widget', ['widget' => $message['widget']])
                    @endif

                    {{-- Guided-flow quick replies: single-tap answer options, only actionable on the latest turn --}}
                    @if($message['role'] === 'assistant' && !empty($message['quick_replies']) && $i === $lastIndex)
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach($message['quick_replies'] as $option)
                                <x-chat.quick-reply-chip action="startWithPrompt('{{ addslashes($option) }}')">{{ $option }}</x-chat.quick-reply-chip>
                            @endforeach
                        </div>
                    @endif

                    {{-- Suggested follow-up questions: lighter chip style, only actionable on the latest turn --}}
                    @if($message['role'] === 'assistant' && !empty($message['suggested_questions']) && $i === $lastIndex)
                        <div class="flex flex-wrap gap-2 mt-3">
                            @foreach($message['suggested_questions'] as $q)
                                <x-chat.quick-reply-chip action="startWithPrompt('{{ addslashes($q) }}')" variant="light">{{ $q }}</x-chat.quick-reply-chip>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach

            @if($thinking)
                <div class="max-w-3xl mx-auto flex items-start gap-3" x-data="{ step: 0, timer: null }" x-init="timer=setInterval(()=>step=(step+1)%3,2200)" x-on:reply-received.window="clearInterval(timer)">
                    <span class="w-8 h-8 rounded-xl hc-primary-bg flex items-center justify-center shrink-0"><x-chat.icon name="sparkles" :size="14" /></span>
                    <div class="hc-surface border hc-border px-4 py-3 inline-flex gap-1" style="border-radius: var(--hc-radius-bubble) var(--hc-radius-bubble) var(--hc-radius-bubble) 6px;">
                        <span class="hc-typing-dot"></span><span class="hc-typing-dot"></span><span class="hc-typing-dot"></span>
                    </div>
                    <span class="text-xs hc-text-secondary pt-3" x-text="[@js(__('Understanding your request…')),@js(__('Searching verified listings…')),@js(__('Preparing a helpful answer…'))][step]"></span>
                </div>
            @endif
        </main>

        <div class="px-3 md:px-5 pb-3 md:pb-5 pt-2 relative" style="background:linear-gradient(to top,var(--hc-bg) 78%,transparent)">
            {{-- Drag & drop overlay --}}
            <div x-show="dragOver" x-cloak @drop="dragOver = false"
                 class="absolute inset-0 flex items-center justify-center z-10 border-2 border-dashed rounded-xl m-2"
                 style="background: color-mix(in srgb, var(--hc-accent) 8%, var(--hc-bg)); border-color: var(--hc-accent);">
                <p class="hc-accent-text font-medium">{{ __('Drop file to upload') }}</p>
            </div>

            <div class="max-w-3xl mx-auto">
                {{-- Attachment preview chip, shown before send (ChatGPT-style pattern) --}}
                @if($attachment)
                    <div class="flex items-center gap-2 mb-2 px-3 py-2 rounded-lg hc-surface border hc-border w-fit">
                        <x-chat.icon :name="str_starts_with($attachment->getMimeType() ?? '', 'image/') ? 'image' : 'file-text'" :size="16" />
                        <span class="text-sm truncate max-w-[160px]">{{ $attachment->getClientOriginalName() }}</span>
                        <button type="button" wire:click="removeAttachment" aria-label="{{ __('Remove attachment') }}" class="hc-focusable hc-text-secondary hover:opacity-70">
                            <x-chat.icon name="x" :size="14" />
                        </button>
                    </div>
                    @error('attachment') <p class="text-xs mb-2" style="color: var(--hc-coral);">{{ __("This file type isn't supported yet. Please upload an image or PDF.") }}</p> @enderror
                @endif

                <form wire:submit.prevent="send" class="hc-composer p-2 flex items-end gap-1.5" x-on:drop="const dt = $event.dataTransfer; if (dt.files.length) { $refs.fileInput.files = dt.files; $refs.fileInput.dispatchEvent(new Event('change')); }">
                    <label class="hc-icon-button hc-focusable hc-text-secondary cursor-pointer shrink-0" aria-label="{{ __('Attach file') }}">
                        <input x-ref="fileInput" type="file" wire:model="attachment" accept="image/*,.pdf" class="hidden">
                        <x-chat.icon name="paperclip" :size="20" />
                    </label>

                    <textarea wire:model="input" x-on:keydown.enter.exact.prevent="$wire.send()" x-on:input="$el.style.height='auto';$el.style.height=Math.min($el.scrollHeight,160)+'px'" rows="1"
                        placeholder="{{ __('Ask about properties, investment, ROI...') }}"
                        class="flex-1 resize-none border-0 bg-transparent px-2 py-2.5 focus:outline-none max-h-40" style="color:var(--hc-text)"></textarea>

                    @if(\App\Models\SiteSetting::get('voice_enabled', '1') === '1')
                        <button type="button" x-data="voiceInput()" @click="toggle()"
                            aria-label="{{ __('Voice input') }}"
                            class="hc-icon-button hc-focusable border transition shrink-0"
                            :style="listening ? 'background: var(--hc-accent); border-color: var(--hc-accent); color: var(--hc-accent-contrast);' : 'border-color: var(--hc-border); color: var(--hc-text-secondary);'"
                            :class="listening ? 'animate-pulse' : ''">
                            <x-chat.icon name="mic" :size="20" />
                        </button>
                    @endif

                    <button type="submit" aria-label="{{ __('Send') }}" @disabled($thinking)
                        class="hc-focusable rounded-xl flex items-center justify-center hc-primary-bg disabled:opacity-40 shrink-0 transition" style="width:40px;height:40px">
                        <x-chat.icon name="send" :size="18" />
                    </button>
                </form>
                <p class="text-center text-[11px] hc-text-secondary mt-2">{{ __('AI can make mistakes. Verify legal and financial details with a qualified professional.') }}</p>
                <p class="text-center mt-2"><a class="text-xs font-semibold hc-accent-text hover:underline" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/\D+/', '', config('services.whatsapp.number')) }}?text={{ urlencode(__('Hello HomeCyp, I need help finding a property in North Cyprus.')) }}">{{ __('Talk to a property advisor on WhatsApp') }}</a></p>
            </div>
        </div>

        {{-- Optional save prompt: visitors can continue chatting without creating an account. --}}
        @if($showLeadModal)
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 px-4" wire:click.self="dismissLeadModal">
                <div class="rounded-2xl hc-shadow max-w-sm w-full p-6 relative hc-surface">
                    <button type="button" wire:click="dismissLeadModal" aria-label="{{ __('Close') }}" class="hc-icon-button hc-focusable absolute top-3 end-3 hc-text-secondary"><x-chat.icon name="x" :size="18" /></button>
                    <x-chat.wave-divider class="w-full h-3 absolute top-0 left-0 opacity-70" />
                    <h2 class="hc-font-display text-lg font-semibold mt-2" style="color: var(--hc-text);">{{ __('Save this in your personal profile') }}</h2>
                    <p class="text-sm hc-text-secondary mt-1 mb-4">{{ __('Register to save your details and this conversation in your personal profile, and get personalized property recommendations.') }}</p>

                    <form wire:submit.prevent="submitLead" class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input type="text" wire:model="leadFirstName" placeholder="{{ __('First name') }}"
                                    class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                                @error('leadFirstName') <p class="text-xs mt-1" style="color: var(--hc-coral);">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="text" wire:model="leadLastName" placeholder="{{ __('Last name') }}"
                                    class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                                @error('leadLastName') <p class="text-xs mt-1" style="color: var(--hc-coral);">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <input type="text" wire:model="leadPhone" placeholder="{{ __('Mobile number') }}"
                            class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                        <input type="email" wire:model="leadEmail" placeholder="{{ __('Email') }}"
                            class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                        @error('leadPhone') <p class="text-xs" style="color: var(--hc-coral);">{{ $message }}</p> @enderror
                        @error('leadEmail') <p class="text-xs" style="color: var(--hc-coral);">{{ $message }}</p> @enderror

                        <div>
                            <select wire:model="leadCountry"
                                class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                                <option value="">{{ __('Country') }}</option>
                                @foreach(\App\Support\Countries::list() as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                            @error('leadCountry') <p class="text-xs mt-1" style="color: var(--hc-coral);">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <input type="password" wire:model="leadPassword" placeholder="{{ __('Create a password') }}"
                                class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                            <p class="text-xs hc-text-secondary mt-1">{{ __('Use this to see your past conversations next time.') }}</p>
                            @error('leadPassword') <p class="text-xs mt-1" style="color: var(--hc-coral);">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="hc-focusable w-full hc-primary-bg font-medium rounded-xl py-2.5">{{ __('Save & Continue') }}</button>
                        <button type="button" wire:click="dismissLeadModal" class="hc-focusable w-full text-sm hc-text-secondary py-2">{{ __('Continue without saving') }}</button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Sign-in modal: returning visitor looking up their own past conversations --}}
        @if($showSignIn)
            <div class="fixed inset-0 bg-black/30 flex items-center justify-center z-50 px-4" wire:click.self="toggleSignIn">
                <div class="rounded-2xl hc-shadow max-w-sm w-full p-6 relative hc-surface">
                    <button type="button" wire:click="toggleSignIn" aria-label="{{ __('Close') }}" class="hc-focusable absolute top-4 right-4 hc-text-secondary hover:opacity-70">
                        <x-chat.icon name="x" :size="18" />
                    </button>
                    <h2 class="hc-font-display text-lg font-semibold" style="color: var(--hc-text);">{{ __('Sign in') }}</h2>
                    <p class="text-sm hc-text-secondary mt-1 mb-4">{{ __('Enter the phone/email and password you used to save a conversation.') }}</p>

                    <form wire:submit.prevent="attemptSignIn" class="space-y-3">
                        <input type="text" wire:model="signInIdentifier" placeholder="{{ __('Phone or email') }}"
                            class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                        @error('signInIdentifier') <p class="text-xs" style="color: var(--hc-coral);">{{ $message }}</p> @enderror

                        <input type="password" wire:model="signInPassword" placeholder="{{ __('Password') }}"
                            class="hc-focusable w-full rounded-xl border hc-border px-4 py-2.5" style="background: var(--hc-bg); color: var(--hc-text);">
                        @error('signInPassword') <p class="text-xs" style="color: var(--hc-coral);">{{ $message }}</p> @enderror

                        <button type="submit" class="hc-focusable w-full hc-primary-bg font-medium rounded-xl py-2.5">{{ __('Sign in') }}</button>
                    </form>
                    <details class="text-sm hc-text-secondary mt-3"><summary class="cursor-pointer">{{ __('Forgot password?') }}</summary><form method="post" action="{{ route('lead.password.request') }}" class="flex gap-2 mt-2">@csrf<input type="email" name="email" required placeholder="{{ __('Email') }}" class="flex-1 min-w-0 rounded-xl border hc-border px-3 py-2" style="background:var(--hc-bg);color:var(--hc-text)"><button class="hc-primary-bg rounded-xl px-3">{{ __('Send link') }}</button></form></details>
                </div>
            </div>
        @endif
    </div>
</div>
