<?php

namespace App\Services\Tools;

use App\Services\Search\HybridSearchService;

/**
 * Residency requirements are legally sensitive, so this only ever surfaces what's
 * actually indexed in the knowledge base (spec 5.7) — it never invents document
 * lists or steps. If nothing is indexed yet, it says so and offers an agent handoff.
 */
class ResidencyAdvisorService
{
    public function __construct(protected HybridSearchService $search)
    {
    }

    public function advise(string $purpose = 'investment'): array
    {
        $results = $this->search->search("residency permit North Cyprus requirements documents {$purpose}", 5);

        if (empty($results)) {
            return [
                'type' => 'residency',
                'available' => false,
                'message' => 'Residency requirements have not been added to the knowledge base yet. Please leave your details and a legal advisor will confirm the exact steps for you.',
            ];
        }

        return [
            'type' => 'residency',
            'available' => true,
            'purpose' => $purpose,
            'checklist' => collect($results)->map(fn ($r, $i) => [
                'step' => $i + 1,
                'text' => $r['text'],
            ])->all(),
        ];
    }
}
