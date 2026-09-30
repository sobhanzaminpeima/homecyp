<?php

namespace Database\Seeders;

use App\Models\RecommendationRule;
use Illuminate\Database\Seeder;

class RecommendationRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['label' => 'Has children', 'trigger_keywords' => ['kids', 'children', 'two kids', 'my son', 'my daughter', 'school age'], 'intent' => null, 'boosted_attribute' => 'near_school', 'weight' => 1.5],
            ['label' => 'Remote worker', 'trigger_keywords' => ['remote', 'work from home', 'wfh', 'work remotely', 'digital nomad'], 'intent' => null, 'boosted_attribute' => 'remote_work_ready', 'weight' => 1.3],
            ['label' => 'Retired', 'trigger_keywords' => ['retired', 'retirement', 'pension'], 'intent' => null, 'boosted_attribute' => 'near_hospital', 'weight' => 1.4],
            ['label' => 'Investment focus', 'trigger_keywords' => ['investment', 'investor', 'roi', 'rental yield', 'return'], 'intent' => 'investment', 'boosted_attribute' => 'high_roi', 'weight' => 1.6],
        ];

        foreach ($rules as $rule) {
            RecommendationRule::updateOrCreate(
                ['label' => $rule['label']],
                $rule + ['is_active' => true]
            );
        }
    }
}
