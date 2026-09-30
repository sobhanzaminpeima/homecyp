<?php

namespace App\Livewire;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Services\AbTestService;
use App\Services\LeadCaptureService;
use App\Services\RagPipelineService;
use App\Services\WorkflowAutomationService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChatWindow extends Component
{
    use WithFileUploads;

    public ?int $conversationId = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $attachment = null;

    public array $messages = [];

    public string $input = '';

    public bool $thinking = false;

    public ?int $pendingUserMessageId = null;

    public bool $showLeadModal = false;

    public bool $leadPromptDismissed = false;

    public string $leadFirstName = '';

    public string $leadLastName = '';

    public string $leadPhone = '';

    public string $leadEmail = '';

    public string $leadCountry = '';

    public string $leadPassword = '';

    public bool $leadCaptured = false;

    public ?array $welcomeVariant = null;

    public bool $viewingRequested = false;

    public ?int $leadId = null;

    public array $conversationHistory = [];

    public bool $showSignIn = false;

    public string $signInIdentifier = '';

    public string $signInPassword = '';

    public function mount(): void
    {
        $this->leadId = session('homecyp_lead_id');

        $uuid = session('homecyp_conversation_uuid');
        $conversation = $uuid ? Conversation::where('uuid', $uuid)->first() : null;

        if (!$conversation) {
            $conversation = Conversation::create([
                'locale' => app()->getLocale(),
                'lead_id' => $this->leadId,
            ]);
            session(['homecyp_conversation_uuid' => $conversation->uuid]);
        }

        $this->conversationId = $conversation->id;
        $this->leadCaptured = (bool) $conversation->lead_id;
        $this->leadId ??= $conversation->lead_id;
        $this->assignWelcomeVariant($conversation);
        $this->loadMessages();
        $this->loadConversationHistory();
    }

    protected function loadConversationHistory(): void
    {
        if (!$this->leadId) {
            $this->conversationHistory = [];
            return;
        }

        $this->conversationHistory = Conversation::where('lead_id', $this->leadId)
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn (Conversation $c) => [
                'uuid' => $c->uuid,
                'is_current' => $c->id === $this->conversationId,
                'title' => $c->title ?: ($c->messages()->where('role', 'user')->value('content') ?? __('New conversation')),
            ])
            ->all();
    }

    /**
     * Starts a brand-new conversation for the current visitor (linked to their
     * lead if signed in, so it shows up in their history) without a full page
     * reload — resets all per-conversation component state in place.
     */
    public function newConversation(): void
    {
        $conversation = Conversation::create([
            'locale' => app()->getLocale(),
            'lead_id' => $this->leadId,
        ]);
        session(['homecyp_conversation_uuid' => $conversation->uuid]);

        $this->conversationId = $conversation->id;
        $this->leadCaptured = (bool) $this->leadId;
        $this->showLeadModal = false;
        $this->viewingRequested = false;
        $this->welcomeVariant = null;
        $this->assignWelcomeVariant($conversation);
        $this->loadMessages();
        $this->loadConversationHistory();
    }

    public function switchConversation(string $uuid): void
    {
        $conversation = Conversation::where('uuid', $uuid)
            ->where('lead_id', $this->leadId) // only ever switch to the signed-in lead's own conversations
            ->first();

        if (!$conversation) {
            return;
        }

        session(['homecyp_conversation_uuid' => $conversation->uuid]);
        $this->conversationId = $conversation->id;
        $this->leadCaptured = true;
        $this->showLeadModal = false;
        $this->loadMessages();
        $this->loadConversationHistory();
    }

    public function toggleSignIn(): void
    {
        $this->showSignIn = !$this->showSignIn;
    }

    public function dismissLeadModal(): void
    {
        $this->showLeadModal = false;
        $this->leadPromptDismissed = true;
    }

    public function attemptSignIn(): void
    {
        $this->validate([
            'signInIdentifier' => 'required|string',
            'signInPassword' => 'required|string',
        ]);

        $lead = app(LeadCaptureService::class)->attemptSignIn($this->signInIdentifier, $this->signInPassword);

        if (!$lead) {
            $this->addError('signInPassword', __('No account found with that phone/email and password.'));
            return;
        }

        $this->leadId = $lead->id;
        session(['homecyp_lead_id' => $lead->id]);

        // Link the conversation being viewed right now to the signed-in lead too.
        $conversation = Conversation::findOrFail($this->conversationId);
        if (!$conversation->lead_id) {
            $conversation->update(['lead_id' => $lead->id]);
        }

        $this->leadCaptured = true;
        $this->showSignIn = false;
        $this->signInIdentifier = '';
        $this->signInPassword = '';
        $this->loadConversationHistory();
    }

    public function signOut(): void
    {
        session()->forget('homecyp_lead_id');

        // Do not leave a signed-in conversation active in the same browser
        // session after logout. Start a fresh anonymous conversation so a
        // shared device cannot reopen the previous lead's messages.
        $conversation = Conversation::create([
            'locale' => app()->getLocale(),
            'lead_id' => null,
        ]);
        session(['homecyp_conversation_uuid' => $conversation->uuid]);

        $this->leadId = null;
        $this->conversationId = $conversation->id;
        $this->leadCaptured = false;
        $this->showSignIn = false;
        $this->showLeadModal = false;
        $this->viewingRequested = false;
        $this->welcomeVariant = null;
        $this->assignWelcomeVariant($conversation);
        $this->loadMessages();
        $this->conversationHistory = [];
    }

    /**
     * A/B test the empty-state welcome message (spec section 8). The picked
     * variant key is stuck in conversation.memory so a lead conversion later
     * in the same session credits the right bucket without re-rolling.
     */
    protected function assignWelcomeVariant(Conversation $conversation): void
    {
        $abTest = app(AbTestService::class);
        $test = $abTest->activeTestFor('welcome_message');
        if (!$test) {
            return;
        }

        $assignedKey = $conversation->memory['ab_variants']['welcome_message'] ?? null;

        if ($assignedKey) {
            $variant = collect($test->variants)->firstWhere('key', $assignedKey);
            $this->welcomeVariant = $variant['content'] ?? null;
            return;
        }

        $variant = $abTest->pickVariant($test);
        if (!$variant) {
            return;
        }

        $this->welcomeVariant = $variant['content'];
        $conversation->rememberFact('ab_variants', array_merge(
            $conversation->memory['ab_variants'] ?? [],
            ['welcome_message' => $variant['key']]
        ));
    }

    public function startWithPrompt(string $prompt): void
    {
        $this->input = $prompt;
        $this->send();
    }

    public function updatedAttachment(): void
    {
        $this->validate([
            'attachment' => 'file|mimes:jpg,jpeg,png,webp,pdf|max:10240', // 10MB
        ], [], ['attachment' => __('Attach file')]);
    }

    public function removeAttachment(): void
    {
        $this->attachment = null;
    }

    /**
     * Fast step (optimistic UI): persist the user's message and show it +
     * the typing indicator immediately. The slow AI call happens in
     * processReply(), triggered automatically right after this returns —
     * see the 'message-sent' JS listener in chat-window.blade.php. Without
     * this split, users saw no feedback for several seconds and would
     * re-click/re-ask, thinking nothing happened.
     */
    public function send(): void
    {
        // Lead capture is a soft prompt, not a wall — a dismissed modal must
        // never block the user from continuing the conversation.
        if ($this->showLeadModal) {
            $this->showLeadModal = false;
            $this->leadPromptDismissed = true;
        }

        $text = trim($this->input);
        if ($text === '' && !$this->attachment) {
            return;
        }

        // A file with no caption still needs some text for intent detection/history.
        if ($text === '' && $this->attachment) {
            $text = '['.__('Shared a file').': '.$this->attachment->getClientOriginalName().']';
        }

        // Every send() triggers a paid LLM call — cap abuse/runaway cost per session.
        $rateLimitKey = 'chat-send:'.session()->getId();
        if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
            $this->messages[] = [
                'id' => 0, 'role' => 'assistant', 'feedback' => null,
                'content' => __("You're sending messages a bit fast — please wait a moment and try again."),
                'suggested_questions' => [], 'quick_replies' => [], 'property_cards' => [], 'sponsored_cards' => [], 'widget' => null,
                'attachment_url' => null, 'attachment_name' => null, 'attachment_type' => null,
            ];
            return;
        }
        RateLimiter::hit($rateLimitKey, 60);

        $this->input = '';
        $this->thinking = true;

        $attachmentData = null;
        if ($this->attachment) {
            $path = $this->attachment->store('chat-attachments', 'public');
            $attachmentData = [
                'path' => $path,
                'name' => $this->attachment->getClientOriginalName(),
                'type' => str_starts_with($this->attachment->getMimeType(), 'image/') ? 'image' : 'pdf',
            ];
            $this->attachment = null;
        }

        $conversation = Conversation::findOrFail($this->conversationId);
        $userMessage = app(RagPipelineService::class)->recordUserTurn($conversation, $text, $attachmentData);
        $this->pendingUserMessageId = $userMessage->id;

        $this->loadMessages();
        $this->dispatch('message-sent');
    }

    /**
     * Slow step: the actual LLM call. Runs immediately after send() via a
     * client-side call so the fast step above can render first.
     */
    public function processReply(): void
    {
        if (!$this->pendingUserMessageId) {
            return;
        }

        $conversation = Conversation::findOrFail($this->conversationId);
        $userMessage = Message::findOrFail($this->pendingUserMessageId);
        $userText = $userMessage->content;
        $this->pendingUserMessageId = null;

        app(RagPipelineService::class)->respond($conversation, $userMessage);

        $this->loadMessages();
        $this->thinking = false;

        $leadCapture = app(LeadCaptureService::class);

        if ($leadCapture->isViewingRequest($userText)) {
            $this->viewingRequested = true;
        }

        if (!$this->leadCaptured && $leadCapture->shouldPrompt($conversation->fresh(), $userText)) {
            $this->showLeadModal = true;
        }

        $this->dispatch('reply-received');
    }

    public function submitLead(): void
    {
        $this->validate([
            'leadFirstName' => 'required|string|min:2|max:255',
            'leadLastName' => 'required|string|min:2|max:255',
            'leadCountry' => 'required|string|max:255',
            'leadPhone' => 'required_without:leadEmail|nullable|string|max:50',
            'leadEmail' => 'required_without:leadPhone|nullable|email|max:255',
            'leadPassword' => 'required|string|min:6',
        ]);

        $conversation = Conversation::findOrFail($this->conversationId);

        $lead = app(LeadCaptureService::class)->capture($conversation, [
            'first_name' => $this->leadFirstName,
            'last_name' => $this->leadLastName,
            'phone' => $this->leadPhone ?: null,
            'email' => $this->leadEmail ?: null,
            'country' => $this->leadCountry ?: null,
            'password' => $this->leadPassword,
        ]);

        $this->leadId = $lead->id;
        session(['homecyp_lead_id' => $lead->id]);
        $this->loadConversationHistory();

        if ($variantKey = $conversation->memory['ab_variants']['welcome_message'] ?? null) {
            app(AbTestService::class)->recordConversion('welcome_message', $variantKey);
        }

        // Workflow automation (spec section 11): booking a viewing creates an
        // agent task + confirmation email as soon as we have contact details.
        if ($this->viewingRequested) {
            $property = $this->lastShownProperty();
            if ($property) {
                $lead->update(['property_id' => $property->id]);
            }
            app(WorkflowAutomationService::class)->requestViewing($lead, $property, $conversation);
        }

        $this->leadCaptured = true;
        $this->showLeadModal = false;
    }

    protected function lastShownProperty(): ?Property
    {
        $lastId = collect($this->messages)
            ->reverse()
            ->flatMap(fn ($m) => $m['property_cards'] ?? [])
            ->first();

        return $lastId ? Property::find($lastId) : null;
    }

    public function rateMessage(int $messageId, int $rating): void
    {
        Message::where('id', $messageId)
            ->where('conversation_id', $this->conversationId)
            ->update(['feedback' => $rating]);

        $this->loadMessages();
    }

    protected function loadMessages(): void
    {
        $this->messages = Conversation::findOrFail($this->conversationId)
            ->messages()
            ->orderBy('id')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'suggested_questions' => $m->suggested_questions ?? [],
                'quick_replies' => $m->quick_replies ?? [],
                'property_cards' => $m->property_cards ?? [],
                'sponsored_cards' => $m->sponsored_cards ?? [],
                'widget' => $m->widget,
                'feedback' => $m->feedback,
                'attachment_url' => $m->attachment_path ? Storage::disk('public')->url($m->attachment_path) : null,
                'attachment_name' => $m->attachment_name,
                'attachment_type' => $m->attachment_type,
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.chat-window');
    }
}
