<?php

namespace App\Services;

use App\Models\RecommendationRule;
use Illuminate\Support\Facades\Cache;

/**
 * Reads the admin-editable recommendation_rules table (spec 5.9) and matches
 * free text against it — no hardcoded logic in the prompt, so an admin can add
 * new inference rules without a code change.
 */
class AdaptiveRecommendationService
{
    /**
     * @return array<string, float> boosted_attribute => cumulative weight
     */
    public function match(string $text, ?string $intent = null): array
    {
        $lower = mb_strtolower($text);
        $boosts = [];

        foreach ($this->activeRules() as $rule) {
            if ($rule['intent'] && $rule['intent'] !== $intent) {
                continue;
            }

            foreach ($rule['trigger_keywords'] as $keyword) {
                if (str_contains($lower, mb_strtolower($keyword))) {
                    $boosts[$rule['boosted_attribute']] = ($boosts[$rule['boosted_attribute']] ?? 0) + $rule['weight'];
                    break;
                }
            }
        }

        return $boosts;
    }

    protected function activeRules(): array
    {
        return Cache::remember('recommendation_rules_active', 300, function () {
            return RecommendationRule::where('is_active', true)->get()->toArray();
        });
    }
}
