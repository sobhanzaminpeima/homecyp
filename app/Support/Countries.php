<?php

namespace App\Support;

class Countries
{
    /**
     * Common countries for HomeCyp's actual buyer markets, listed first, then
     * the rest alphabetically. Kept as a plain array (no external package) so
     * it works offline on a bare cPanel host.
     */
    public static function list(): array
    {
        $priority = [
            'United Kingdom', 'Turkey', 'Germany', 'Russia', 'Israel', 'Iran',
            'Cyprus', 'Ukraine', 'Netherlands', 'Sweden',
        ];

        $rest = [
            'Australia', 'Austria', 'Azerbaijan', 'Belgium', 'Bulgaria', 'Canada',
            'China', 'Denmark', 'Egypt', 'Finland', 'France', 'Georgia', 'Greece',
            'India', 'Iraq', 'Ireland', 'Italy', 'Jordan', 'Kazakhstan', 'Kuwait',
            'Lebanon', 'Norway', 'Pakistan', 'Poland', 'Portugal', 'Qatar',
            'Romania', 'Saudi Arabia', 'South Africa', 'Spain', 'Switzerland',
            'Syria', 'United Arab Emirates', 'United States', 'Uzbekistan', 'Other',
        ];

        return array_merge($priority, $rest);
    }
}
