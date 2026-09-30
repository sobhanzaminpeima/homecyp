<?php

namespace App\Services;

use App\Models\AbTest;
use Illuminate\Support\Facades\DB;

/**
 * Weighted-random bucketing for welcome_message / suggested_questions / cta
 * variants (spec section 8). The chosen variant key must be persisted by the
 * caller (e.g. conversation.memory) so recordConversion() can credit the right
 * bucket without re-rolling the random pick.
 */
class AbTestService
{
    public function activeTestFor(string $subject): ?AbTest
    {
        return AbTest::where('subject', $subject)->where('status', 'active')->first();
    }

    /**
     * @return array{key: string, content: mixed}|null
     */
    public function pickVariant(AbTest $test): ?array
    {
        $variants = $test->variants ?? [];
        if (empty($variants)) {
            return null;
        }

        $totalWeight = array_sum(array_column($variants, 'weight')) ?: count($variants);
        $roll = mt_rand(1, (int) ($totalWeight * 100)) / 100;

        $cumulative = 0;
        foreach ($variants as $variant) {
            $cumulative += $variant['weight'] ?? (1 / count($variants));
            if ($roll <= $cumulative) {
                $this->recordImpression($test, $variant['key']);
                return ['key' => $variant['key'], 'content' => $variant['content'] ?? null];
            }
        }

        $last = end($variants);
        $this->recordImpression($test, $last['key']);
        return ['key' => $last['key'], 'content' => $last['content'] ?? null];
    }

    public function recordImpression(AbTest $test, string $variantKey): void
    {
        $this->bumpResult($test, $variantKey, 'impressions');
    }

    public function recordConversion(string $subject, string $variantKey): void
    {
        if ($test = AbTest::where('subject', $subject)->first()) {
            $this->bumpResult($test, $variantKey, 'conversions');
        }
    }

    protected function bumpResult(AbTest $test, string $variantKey, string $metric): void
    {
        DB::transaction(function () use ($test, $variantKey, $metric) {
            $fresh = AbTest::lockForUpdate()->find($test->id);
            $results = $fresh->results ?? [];
            $results[$variantKey][$metric] = ($results[$variantKey][$metric] ?? 0) + 1;
            $fresh->update(['results' => $results]);
        });
    }
}
