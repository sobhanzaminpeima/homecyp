<?php

namespace App\Services\Tools;

use App\Models\Area;

class AreaAdvisorService
{
    protected const PRIORITY_KEYWORDS = [
        'beach' => ['beach', 'sea', 'coast'],
        'university' => ['university', 'student', 'college'],
        'school' => ['school', 'kids', 'children'],
        'hospital' => ['hospital', 'retired', 'healthcare', 'medical'],
        'nightlife' => ['nightlife', 'restaurants', 'bars', 'social'],
        'quiet' => ['quiet', 'peaceful', 'calm'],
        'remote_work_ready' => ['remote', 'work from home', 'internet', 'wifi'],
        'roi_growth' => ['roi', 'investment', 'growth', 'appreciation'],
    ];

    protected const LEVEL_SCORE = ['low' => 1, 'medium' => 2, 'high' => 3];

    public function recommend(string $text, int $limit = 2): array
    {
        $priorities = $this->extractPriorities($text);

        $areas = Area::where('is_active', true)->with('translations')->get();

        $scored = $areas->map(function (Area $area) use ($priorities) {
            $highlights = $area->translation()?->highlights ?? [];
            $score = 0;
            $reasons = [];

            foreach ($priorities as $priority) {
                $level = $highlights[$priority] ?? 'low';
                $score += self::LEVEL_SCORE[$level] ?? 1;
                if (($level ?? 'low') !== 'low') {
                    $reasons[] = ucfirst(str_replace('_', ' ', $priority))." is {$level} here";
                }
            }

            return [
                'slug' => $area->slug,
                'name' => $area->translation()?->title ?? $area->name,
                'overview' => $area->translation()?->overview,
                'score' => $score,
                'reasons' => $reasons,
            ];
        })->sortByDesc('score')->values();

        return [
            'type' => 'area_advisor',
            'priorities' => $priorities,
            'recommendations' => $scored->take($limit)->all(),
        ];
    }

    protected function extractPriorities(string $text): array
    {
        $lower = mb_strtolower($text);
        $matched = [];

        foreach (self::PRIORITY_KEYWORDS as $key => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $matched[] = $key;
                    break;
                }
            }
        }

        return $matched ?: ['roi_growth', 'beach'];
    }
}
