<?php

namespace App\Services\Tools;

class MortgageCalculatorService
{
    /**
     * Extract loan amount, down payment %, interest rate, and term (years) from free
     * text plus previously remembered facts. Returns null if not enough is known yet —
     * the conversational layer keeps asking rather than the widget guessing.
     */
    public function extractInputs(string $text, array $memory = []): ?array
    {
        $lower = mb_strtolower($text);

        $price = null;
        if (preg_match('/(?:£|\$|€)?\s*([\d,]+(?:\.\d+)?)\s*(k|thousand|m|million)?/u', $lower, $m) && preg_match('/\b(price|property|loan|worth|value|cost)\b/u', $lower)) {
            $amount = (float) str_replace(',', '', $m[1]);
            $unit = $m[2] ?? '';
            $price = match (true) {
                in_array($unit, ['k', 'thousand']) => $amount * 1000,
                in_array($unit, ['m', 'million']) => $amount * 1_000_000,
                default => $amount,
            };
        }
        $price ??= $memory['mortgage_price'] ?? null;

        $downPaymentPercent = null;
        if (preg_match('/(\d{1,2})\s*%\s*(?:down|deposit)/u', $lower, $m)) {
            $downPaymentPercent = (float) $m[1];
        }
        $downPaymentPercent ??= $memory['mortgage_down_payment_percent'] ?? 30.0; // sensible North Cyprus default

        $interestRate = null;
        if (preg_match('/(\d{1,2}(?:\.\d+)?)\s*%\s*(?:interest|rate)/u', $lower, $m)) {
            $interestRate = (float) $m[1];
        }
        $interestRate ??= $memory['mortgage_interest_rate'] ?? null;

        $termYears = null;
        if (preg_match('/(\d{1,2})\s*(?:year|yr)s?\s*(?:term|mortgage|loan)?/u', $lower, $m)) {
            $termYears = (int) $m[1];
        }
        $termYears ??= $memory['mortgage_term_years'] ?? null;

        if ($price === null || $interestRate === null || $termYears === null) {
            return null;
        }

        return compact('price', 'downPaymentPercent', 'interestRate', 'termYears');
    }

    /**
     * @return array{type: string, price: float, down_payment: float, loan_amount: float,
     *   interest_rate: float, term_years: int, monthly_payment: float, total_interest: float, total_paid: float}
     */
    public function calculate(float $price, float $downPaymentPercent, float $interestRate, int $termYears): array
    {
        $downPayment = round($price * $downPaymentPercent / 100, 2);
        $loanAmount = round($price - $downPayment, 2);
        $monthlyRate = ($interestRate / 100) / 12;
        $months = $termYears * 12;

        $monthlyPayment = $monthlyRate > 0
            ? $loanAmount * ($monthlyRate * (1 + $monthlyRate) ** $months) / (((1 + $monthlyRate) ** $months) - 1)
            : $loanAmount / $months;

        $totalPaid = $monthlyPayment * $months;
        $totalInterest = $totalPaid - $loanAmount;

        return [
            'type' => 'mortgage',
            'price' => $price,
            'down_payment' => $downPayment,
            'loan_amount' => $loanAmount,
            'interest_rate' => $interestRate,
            'term_years' => $termYears,
            'monthly_payment' => round($monthlyPayment, 2),
            'total_interest' => round($totalInterest, 2),
            'total_paid' => round($totalPaid, 2),
        ];
    }
}
