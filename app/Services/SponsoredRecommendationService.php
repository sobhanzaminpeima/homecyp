<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\SponsorCampaign;
use Illuminate\Support\Collection;

/**
 * Injects sponsored properties ALONGSIDE organic results, never replacing them,
 * always transparently labeled (spec section 9). Ranking quality of the organic
 * results is never touched by this — sponsorship only adds a clearly separate
 * "Featured Partner" card.
 */
class SponsoredRecommendationService
{
    /**
     * @return Collection<int, array{campaign: SponsorCampaign, property: \App\Models\Property}>
     */
    public function match(Conversation $conversation, array $filters, int $limit = 1): Collection
    {
        $memory = $conversation->memory ?? [];

        $campaigns = SponsorCampaign::active()
            ->with('property')
            ->whereNotNull('property_id')
            ->orderByDesc('priority')
            ->get()
            ->filter(function (SponsorCampaign $c) use ($filters, $memory, $conversation) {
                if (!$c->property || $c->property->status !== 'active') {
                    return false;
                }

                if ($c->target_region && $filters['region'] && !str_contains(
                    mb_strtolower($c->target_region), mb_strtolower($filters['region'])
                )) {
                    return false;
                }

                if ($c->target_property_type && $filters['category'] && $c->target_property_type !== $filters['category']) {
                    return false;
                }

                $budget = $memory['budget'] ?? $filters['budget_max'] ?? null;
                if ($budget && $c->target_budget_min && $budget < $c->target_budget_min) {
                    return false;
                }
                if ($budget && $c->target_budget_max && $budget > $c->target_budget_max) {
                    return false;
                }

                if (!empty($c->target_languages) && !in_array($conversation->locale, $c->target_languages, true)) {
                    return false;
                }

                return true;
            })
            ->take($limit);

        // Impression tracking: a match here means it was actually shown to the user.
        $campaigns->each(fn (SponsorCampaign $c) => $c->increment('impressions'));

        return $campaigns->map(fn (SponsorCampaign $c) => ['campaign' => $c, 'property' => $c->property])->values();
    }
}
