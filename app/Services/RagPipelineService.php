<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\Llm\LlmManager;
use App\Services\PropertySearchService;
use App\Services\Search\HybridSearchService;
use App\Services\Tools\AreaAdvisorService;
use App\Services\Tools\CompareService;
use App\Services\Tools\MortgageCalculatorService;
use App\Services\Tools\ResidencyAdvisorService;
use App\Services\Tools\RoiCalculatorService;
use App\Services\Tools\TimelineService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class RagPipelineService
{
    protected const PROPERTY_INTENTS = ['property_search', 'investment', 'daily_rental', 'rental', 'comparison'];

    public function __construct(
        protected LlmManager $llm,
        protected HybridSearchService $search,
        protected PropertySearchService $properties,
        protected MortgageCalculatorService $mortgage,
        protected RoiCalculatorService $roi,
        protected CompareService $compare,
        protected AreaAdvisorService $areaAdvisor,
        protected ResidencyAdvisorService $residencyAdvisor,
        protected TimelineService $timeline,
        protected SponsoredRecommendationService $sponsored,
    ) {
    }

    /**
     * Fast, no-LLM step: persist the user's message so the UI can render it
     * immediately (optimistic send). Call respond() right after with the
     * returned message to do the actual (slow) AI work.
     */
    public function recordUserTurn(Conversation $conversation, string $userText, ?array $attachment = null): Message
    {
        return $conversation->messages()->create([
            'role' => 'user',
            'content' => $userText,
            'attachment_path' => $attachment['path'] ?? null,
            'attachment_name' => $attachment['name'] ?? null,
            'attachment_type' => $attachment['type'] ?? null,
            'attachment_text' => $attachment['text'] ?? null,
        ]);
    }

    /**
     * Run the full pipeline for one user turn and persist the assistant's reply.
     * The user's own message must already be persisted — see recordUserTurn().
     *
     * @return Message the assistant's persisted message
     */
    public function respond(Conversation $conversation, Message $userMessage): Message
    {
        $userText = $userMessage->content;

        $intent = $this->detectIntent($userText);
        $context = $this->search->search($userText);
        $known = $conversation->memory ?? [];
        $searchText = trim($userText.' '.implode(' ', array_filter([
            $known['area'] ?? null,
            isset($known['budget']) ? 'budget '.$known['budget'] : null,
            isset($known['bedrooms']) ? $known['bedrooms'].' bedroom' : null,
            $known['purpose'] ?? null,
        ])));
        $matchedProperties = in_array($intent, self::PROPERTY_INTENTS, true)
            ? $this->properties->search($searchText, 6, $intent)
            : collect();

        // Spec section 6: the AI's reply language always follows the language the
        // user is currently typing in, independent of the site's UI locale — so a
        // Turkish-typing visitor on the English-UI site still gets Turkish replies.
        $detectedLanguage = $this->detectLanguage($userText);
        if ($conversation->locale !== $detectedLanguage) {
            $conversation->update(['locale' => $detectedLanguage]);
        }

        $deterministicFacts = $this->factsFromMessage($userText, $intent);
        if ($deterministicFacts) {
            $conversation->update(['memory' => array_merge($conversation->memory ?? [], $deterministicFacts)]);
        }

        $history = $conversation->messages()
            ->latest()
            ->take(10)
            ->get()
            ->reverse()
            ->map(fn (Message $m) => [
                'role' => $m->role,
                'content' => $m->content.($m->attachment_text ? "\n\nVerified text extracted from the attached file:\n".$m->attachment_text : ''),
            ])
            ->values()
            ->all();

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt($conversation, $context, $intent, $matchedProperties, $detectedLanguage)]],
            $history,
        );

        $structured = $matchedProperties->isEmpty() && in_array($intent, self::PROPERTY_INTENTS, true)
            ? $this->noPropertyMatch($detectedLanguage)
            : $this->callLlm($messages, $detectedLanguage);

        // Never trust the LLM to invent property ids: constrain whatever it returned
        // to the ids we actually matched in the DB for this turn.
        $validIds = $matchedProperties->pluck('id')->all();
        $propertyCards = array_values(array_intersect($structured['property_cards'] ?? [], $validIds));
        if (empty($propertyCards) && !empty($validIds) && in_array($intent, self::PROPERTY_INTENTS, true)) {
            $propertyCards = $validIds;
        }

        // Math and legally-sensitive tools are computed deterministically here, never
        // trusted to the LLM (spec 5.4/5.7): whatever the model returned in "widget"
        // for these intents is discarded and replaced with the real calculation.
        // Widgets drive calculations and structured UI. Only server-side tools
        // may create them; accepting arbitrary LLM-shaped widgets can render
        // incomplete data and crash the conversation view.
        $widget = $this->buildWidget($intent, $userText, $conversation, $matchedProperties);

        // Sponsored results (spec 9): always alongside organic matches, never
        // instead of them, and only when there actually were organic property
        // results this turn — a sponsor card never appears standalone.
        $sponsoredCards = [];
        if (!empty($propertyCards) && in_array($intent, self::PROPERTY_INTENTS, true)) {
            $filters = $this->properties->extractFilters($userText);
            $sponsoredCards = $this->sponsored->match($conversation, $filters)
                ->map(fn ($m) => ['property_id' => $m['property']->id, 'campaign_id' => $m['campaign']->id])
                ->reject(fn ($s) => in_array($s['property_id'], $propertyCards, true))
                ->values()
                ->all();
        }

        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $structured['answer'],
            'suggested_questions' => $structured['suggested_questions'] ?? [],
            'quick_replies' => $structured['quick_replies'] ?? [],
            'property_cards' => $propertyCards,
            'sponsored_cards' => $sponsoredCards,
            'widget' => $widget,
            'intent' => $intent,
        ]);

        $memory = $conversation->memory ?? [];

        if (!empty($structured['memory_updates']) && is_array($structured['memory_updates'])) {
            $memory = array_merge($memory, array_filter($structured['memory_updates']));
        }

        // Personalization (spec 5.6): remember what the user has actually seen so
        // future turns and future sessions don't re-show or re-ask about it.
        if (!empty($propertyCards)) {
            $memory['viewed_properties'] = array_values(array_unique(array_merge(
                $memory['viewed_properties'] ?? [],
                $propertyCards,
            )));
        }

        if ($memory !== ($conversation->memory ?? [])) {
            $conversation->update(['memory' => $memory]);
        }

        $conversation->update(['last_message_at' => now()]);

        return $assistantMessage;
    }

    /**
     * Cheap script/keyword heuristic — just enough to label conversation.locale
     * for bookkeeping (voice recognition lang code, mail language) and to name
     * the language in the system prompt. The LLM does the actual translation —
     * it's naturally multilingual, so this doesn't need to be exhaustive; it
     * only needs to recognize this platform's actual buyer markets. Add a new
     * script/keyword branch here (no other structural change) to support more.
     */
    protected function detectLanguage(string $text): string
    {
        // Persian: Arabic-script letters that don't appear in Arabic itself.
        if (preg_match('/[پچژگ]/u', $text) || preg_match('/(?:سلام|ملک|خرید|فروش|اجاره|قیمت|بودجه|خانه|میخواهم|می‌خواهم)/u', $text)) {
            return 'fa';
        }

        // Any Cyrillic text — Russian is this platform's only Cyrillic market.
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $text)) {
            return 'ru';
        }

        // German: ß is unique to German among this platform's Latin-script
        // markets (Turkish uses ö/ü too, so check those only after ruling out ß).
        if (preg_match('/[ß]/u', $text) || preg_match('/\b(und|nicht|ich|sie|haben|möchte|wohnung)\b/iu', $text)) {
            return 'de';
        }

        if (preg_match('/[çğıöşüÇĞİÖŞÜ]/u', $text)) {
            return 'tr';
        }

        $turkishWords = ['merhaba', 'evet', 'hayır', 'nasıl', 'nerede', 'için', 'kaç', 'fiyat', 'yatırım', 'ev', 'daire', 'kiralık'];
        $lower = mb_strtolower($text);
        foreach ($turkishWords as $word) {
            if (preg_match('/\b'.preg_quote($word, '/').'\b/u', $lower)) {
                return 'tr';
            }
        }

        // Arabic script left over after ruling out Persian-specific letters.
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return 'ar';
        }

        return 'en';
    }

    protected function detectIntent(string $text): string
    {
        $text = mb_strtolower($text);

        return match (true) {
            (bool) preg_match('/(roi|return on investment|yield|بازده|سود سرمایه|بازگشت سرمایه|getiri|rendite|доходност)/u', $text) => 'roi',
            (bool) preg_match('/(compare|vs|versus|مقایسه|مقابل|karşılaştır|сравни|vergleich)/u', $text) => 'comparison',
            (bool) preg_match('/(mortgage|loan|finance|وام|قسط|تسهیلات|ipotek|кредит|hypothek)/u', $text) => 'mortgage',
            (bool) preg_match('/(residency|permit|citizenship|اقامت|شهروندی|ikamet|гражданств|aufenthalt)/u', $text) => 'residency',
            (bool) preg_match('/\b(next steps?|process|how does it work|timeline|what happens next)\b/u', $text) => 'timeline',
            (bool) preg_match('/\b(best area|which area|near (?:school|university|beach)|quiet area|neighbou?rhood)\b/u', $text) => 'area_advisor',
            (bool) preg_match('/(invest|investment|سرمایه.?گذاری|yatırım|инвестиц|investition)/u', $text) => 'investment',
            (bool) preg_match('/(airbnb|daily rental|nightly|اجاره روزانه|کوتاه.?مدت|günlük kiralık|посуточн|tagesmiete)/u', $text) => 'daily_rental',
            (bool) preg_match('/(rent|rental|long.?term|اجاره|رهن|kiralık|аренд|miete)/u', $text) => 'rental',
            (bool) preg_match('/(bedroom|villa|apartment|buy|sell|price|budget|property|properties|اتاق|ویلا|آپارتمان|خرید|فروش|قیمت|بودجه|ملک|خانه|daire|satılık|villa|fiyat|недвижим|квартир|купить|immobilie|wohnung|kaufen)/u', $text) => 'property_search',
            default => 'general',
        };
    }

    protected function factsFromMessage(string $text, string $intent): array
    {
        $filters = $this->properties->extractFilters($text);
        $facts = array_filter([
            'budget' => $filters['budget_max'],
            'area' => $filters['region'],
            'bedrooms' => $filters['bedrooms'],
            'purpose' => $intent === 'investment' ? 'investment' : null,
        ], fn ($value) => $value !== null && $value !== '');

        return $facts;
    }

    /**
     * Deterministic tool widgets, never LLM-computed math or invented legal facts.
     * Returns null when the intent isn't tool-related, or when a tool needs more
     * input first (e.g. mortgage still missing rate/term) — the conversational
     * layer keeps asking via quick_replies in that case.
     */
    protected function buildWidget(string $intent, string $userText, Conversation $conversation, Collection $matchedProperties): ?array
    {
        $memory = $conversation->memory ?? [];

        return match ($intent) {
            'roi' => $matchedProperties->isNotEmpty()
                ? $this->roi->calculate($matchedProperties->first())
                : null,

            'mortgage' => ($inputs = $this->mortgage->extractInputs($userText, $memory)) !== null
                ? $this->mortgage->calculate($inputs['price'], $inputs['downPaymentPercent'], $inputs['interestRate'], $inputs['termYears'])
                : null,

            'residency' => $this->residencyAdvisor->advise($memory['purpose'] ?? 'investment'),

            'area_advisor' => $this->areaAdvisor->recommend($userText),

            'comparison' => $matchedProperties->count() >= 2
                ? $this->compare->compare($matchedProperties->pluck('id')->all())
                : null,

            'timeline' => $this->timeline->build($conversation),

            default => null,
        };
    }

    protected function systemPrompt(Conversation $conversation, array $context, string $intent, Collection $matchedProperties, string $language = 'en'): string
    {
        $contextText = collect($context)
            ->map(fn ($c, $i) => "[Source ".($i + 1)."] {$c['text']}")
            ->implode("\n\n");

        $memory = $conversation->memory ? json_encode($conversation->memory) : '{}';
        $languageName = match ($language) {
            'tr' => 'Turkish',
            'fa' => 'Persian (Farsi)',
            'ru' => 'Russian',
            'de' => 'German',
            'ar' => 'Arabic',
            default => 'English',
        };

        $propertiesText = $matchedProperties->isEmpty()
            ? '(none matched in the database for this turn)'
            : $matchedProperties->map(fn ($p) => json_encode([
                'id' => $p->id,
                'title' => $p->title,
                'category' => $p->category,
                'type' => $p->type,
                'price' => $p->price,
                'currency' => $p->currency,
                'bedrooms' => $p->bedrooms,
                'region' => $p->region,
                'is_airbnb' => $p->is_airbnb,
            ]))->implode("\n");

        return <<<PROMPT
You are the AI Real Estate Expert for HomeCyp, a property platform focused exclusively on North Cyprus
(Iskele, Kyrenia, Famagusta, Nicosia). You help users find properties, evaluate investments, understand
residency and legal processes, and compare projects.

Rules:
- Respond in the same language the user's most recent message is written in (best guess: {$languageName},
  but trust your own reading of their actual message over this guess — it covers Persian, Turkish, English,
  Russian, German, Arabic, and any other language a visitor writes in). This must match regardless of what
  language earlier turns in this conversation used. Never mix languages in one reply. Translate every
  common real-estate term into that language; keep another language only for an official property, place,
  or brand name. For Persian, use natural Iranian Persian vocabulary and Persian script only (apart from
  official names such as Airbnb). Never insert Arabic, Korean, Turkish, Russian, or German vocabulary into
  a Persian sentence.
- Answer only using the CONTEXT and MATCHED PROPERTIES below and the conversation history. Never invent
  property details, prices, or legal facts that aren't in them. If you don't know, say so and offer to
  connect the user with an agent.
- Property search is always against the MATCHED PROPERTIES list — never suggest properties from outside
  it, and never invent a property id. Only put ids from that list into "property_cards".
- Never fabricate or output raw third-party links (e.g. Airbnb URLs) in text — the UI renders dedicated
  "Book on Airbnb" buttons for those from structured data instead. Never print an airbnb.com URL yourself.
- Act like a real advisor: guide the conversation step by step. If you don't yet know the user's budget,
  purpose (investment/living/holiday home), preferred area, or bedroom count, ask ONE of those next and
  offer it as short "quick_replies" options (e.g. ["Investment", "Living", "Holiday Home"]) instead of a
  free-text question alone. Never re-ask something already present in "Known facts" below. Once you have
  enough to search, stop asking and show matched properties instead.
- Whenever the user states a new fact (budget, purpose, preferred area, bedrooms, timeline), extract it
  into "memory_updates" so it isn't asked again.
- Always respond with a single JSON object, no markdown fences, matching this schema:
  {
    "answer": "string, the conversational reply",
    "quick_replies": ["short single-tap answer options for a guided question this turn, or [] if none"],
    "suggested_questions": ["up to 5 short follow-up questions the user might separately want to ask"],
    "property_cards": [numeric ids from MATCHED PROPERTIES to render as cards, if any are relevant],
    "widget": null or {"type": "roi|mortgage|residency|area_advisor|compare|timeline", ...structured fields},
    "memory_updates": {"budget": null, "purpose": null, "area": null, "bedrooms": null} — only include keys
      you actually learned this turn, omit or null the rest
  }
- Detected intent for this turn: {$intent}
- Known facts about this user so far (JSON): {$memory}

MATCHED PROPERTIES (from internal database, JSON lines):
{$propertiesText}

CONTEXT:
{$contextText}

{$this->adminPromptOverride()}
PROMPT;
    }

    /**
     * Admin-configurable extra instructions (LLM Settings page), appended so ops
     * can adjust tone or mention current promotions without a code change.
     */
    protected function adminPromptOverride(): string
    {
        $override = \App\Models\SiteSetting::get('ai_system_prompt_override');

        return $override ? "ADDITIONAL INSTRUCTIONS FROM HOMECYP TEAM:\n{$override}" : '';
    }

    /**
     * @return array{answer:string, suggested_questions:array, property_cards:array, widget:?array}
     */
    protected function callLlm(array $messages, string $language = 'en'): array
    {
        try {
            $raw = $this->llm->chat($messages, ['response_format' => 'json']);
            $decoded = json_decode($raw, true);

            if (is_array($decoded) && isset($decoded['answer'])) {
                return $decoded;
            }

            return $this->emptyStructured($raw ?: $this->fallbackAnswer($language));
        } catch (Throwable $e) {
            Log::warning('RAG pipeline LLM call failed', ['error' => $e->getMessage()]);

            return $this->emptyStructured($this->fallbackAnswer($language));
        }
    }

    protected function emptyStructured(string $answer): array
    {
        return [
            'answer' => $answer,
            'quick_replies' => [],
            'suggested_questions' => [],
            'property_cards' => [],
            'widget' => null,
            'memory_updates' => [],
        ];
    }

    protected function noPropertyMatch(string $language): array
    {
        [$answer, $quickReplies] = match ($language) {
            'fa' => [
                'در حال حاضر ملک فعالی مطابق بودجه، منطقه و نوع درخواست شما در فهرست ما پیدا نشد. می‌توانم محدودهٔ قیمت یا منطقه را تغییر دهم، یا درخواست شما را برای بررسی دستی به مشاور منتقل کنم. کدام را ترجیح می‌دهید؟',
                ['تغییر بودجه', 'تغییر منطقه', 'ارتباط با مشاور'],
            ],
            'tr' => [
                'Şu anda bütçenize, bölgenize ve talep türünüze uyan aktif bir ilan bulamadım. Fiyat aralığını veya bölgeyi değiştirebilir ya da talebinizi danışman incelemesine iletebilirim. Hangisini tercih edersiniz?',
                ['Bütçeyi değiştir', 'Bölgeyi değiştir', 'Danışmana bağlan'],
            ],
            'ru' => [
                'Сейчас в нашей базе нет активного объекта, который соответствует вашему бюджету, району и типу запроса. Я могу изменить бюджет или район либо передать запрос консультанту для ручной проверки. Что вы предпочитаете?',
                ['Изменить бюджет', 'Изменить район', 'Связаться с консультантом'],
            ],
            'de' => [
                'Aktuell gibt es in unserem Bestand kein aktives Objekt, das zu Ihrem Budget, der Region und der Anfrageart passt. Ich kann den Preisrahmen oder die Region ändern oder Ihre Anfrage zur manuellen Prüfung an einen Berater weitergeben. Was bevorzugen Sie?',
                ['Budget ändern', 'Region ändern', 'Berater kontaktieren'],
            ],
            'ar' => [
                'لا يوجد حالياً عقار نشط في قائمتنا يطابق ميزانيتك والمنطقة ونوع الطلب. يمكنني تغيير نطاق السعر أو المنطقة، أو إحالة طلبك إلى مستشار للمراجعة اليدوية. ماذا تفضل؟',
                ['تغيير الميزانية', 'تغيير المنطقة', 'التواصل مع مستشار'],
            ],
            default => [
                'I could not find an active listing that matches your budget, area, and request type. I can broaden the budget or area, or send your request to an agent for a manual check. Which would you prefer?',
                ['Change budget', 'Change area', 'Talk to an agent'],
            ],
        };

        return [
            'answer' => $answer,
            'quick_replies' => $quickReplies,
            'suggested_questions' => [],
            'property_cards' => [],
            'widget' => null,
            'memory_updates' => [],
        ];
    }

    protected function fallbackAnswer(string $language = 'en'): string
    {
        return match ($language) {
            'fa' => 'در حال حاضر ارتباط با سرویس هوش مصنوعی برقرار نشد. لطفاً کمی بعد دوباره امتحان کنید یا اطلاعات تماس خود را بگذارید تا مشاور ما پیگیری کند.',
            'ar' => 'تعذر الاتصال بخدمة الذكاء الاصطناعي حالياً. يرجى المحاولة بعد قليل أو ترك بياناتك ليتواصل معك أحد مستشارينا.',
            'tr' => 'Şu anda yapay zekâ hizmetine ulaşılamıyor. Lütfen biraz sonra tekrar deneyin veya danışmanımızın size ulaşması için bilgilerinizi bırakın.',
            'ru' => 'Сейчас не удаётся связаться с сервисом ИИ. Попробуйте ещё раз немного позже или оставьте контакты, и наш консультант свяжется с вами.',
            'de' => 'Der KI-Dienst ist derzeit nicht erreichbar. Bitte versuchen Sie es gleich noch einmal oder hinterlassen Sie Ihre Kontaktdaten.',
            default => "I'm having trouble reaching the AI service right now. Please try again in a moment, or leave your details and one of our agents will follow up.",
        };
    }
}
