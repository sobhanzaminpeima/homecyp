<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Lead;
use Illuminate\Support\Facades\Hash;

/**
 * Decides when to surface the lead capture modal: never on first load and
 * never after a single reply — the visitor gets a few real exchanges first
 * (3–4 assistant replies), or an immediate prompt when they ask for something
 * high-value. The modal stays skippable: dismissing it keeps the conversation
 * going and the soft CTA simply resurfaces later.
 */
class LeadCaptureService
{
    /** Assistant replies after which the soft sign-up prompt may appear. */
    protected const PROMPT_AFTER_REPLIES = 3;

    /** After the first prompt, re-surface it every this many assistant replies (3 → 6 → 9 …). */
    protected const REPROMPT_EVERY = 3;
    protected const HIGH_VALUE_PATTERN = '/\b(book (?:a )?viewing|full details|full brochure|investment proposal|contact (?:an )?agent|speak to (?:an )?agent|call me|arrange a viewing)\b/iu';

    protected const VIEWING_PATTERN = '/\b(book (?:a )?viewing|arrange a viewing|schedule a viewing|view the property|see the property in person)\b/iu';

    public function shouldPrompt(Conversation $conversation, string $lastUserText): bool
    {
        if ($conversation->lead_id) {
            return false;
        }

        if (preg_match(self::HIGH_VALUE_PATTERN, $lastUserText)) {
            return true;
        }

        $assistantReplies = $conversation->messages()->where('role', 'assistant')->count();

        if ($assistantReplies < self::PROMPT_AFTER_REPLIES) {
            return false;
        }

        // Fire when the count crosses the threshold, then re-surface gently on
        // a fixed cadence so a dismissed modal doesn't nag on every message.
        return ($assistantReplies - self::PROMPT_AFTER_REPLIES) % self::REPROMPT_EVERY === 0;
    }

    public function isViewingRequest(string $text): bool
    {
        return (bool) preg_match(self::VIEWING_PATTERN, $text);
    }

    public function capture(Conversation $conversation, array $data): Lead
    {
        $memory = $conversation->memory ?? [];

        $name = trim(($data['first_name'] ?? '').' '.($data['last_name'] ?? ''));

        $lead = Lead::create([
            'name' => $name !== '' ? $name : trim($data['name'] ?? ''),
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'password' => $data['password'] ?? null,
            'type' => ($memory['purpose'] ?? null) === 'investment' ? 'investment' : 'contact',
            'status' => 'new',
            'message' => 'Captured from AI chat conversation '.$conversation->uuid,
            'source' => 'ai_chat',
            'preferred_language' => $conversation->locale,
            'meta_data' => [
                'conversation_uuid' => $conversation->uuid,
                'budget' => $memory['budget'] ?? null,
                'purpose' => $memory['purpose'] ?? null,
                'area' => $memory['area'] ?? null,
                'bedrooms' => $memory['bedrooms'] ?? null,
            ],
        ]);

        $conversation->update(['lead_id' => $lead->id]);

        return $lead;
    }

    /**
     * Lightweight returning-visitor sign-in: phone or email + the password set
     * during lead capture, no email verification or session tokens beyond
     * Laravel's normal session.
     */
    public function attemptSignIn(string $identifier, string $password): ?Lead
    {
        $lead = Lead::where('email', $identifier)->orWhere('phone', $identifier)->whereNotNull('password')->first();

        if ($lead && Hash::check($password, $lead->password)) {
            return $lead;
        }

        return null;
    }
}
