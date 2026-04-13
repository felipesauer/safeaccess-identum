<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates São Paulo (SP) IE numbers.
 *
 * Two variants (12 digits each):
 *  - Commercial/Industrial: two DVs. DV policy: (sum % 11); if 10 → 0.
 *    DV1 weights [1,3,4,5,6,7,8,10]; DV2 weights [3,2,10,9,8,7,6,5,4,3,2].
 *  - Rural producer: IE string starts with 'P'. Single DV over the 8-digit numeric base.
 */
final class SpRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $raw = strtoupper(trim($ie));

        // Rural producer IEs start with 'P'
        if ($raw !== '' && $raw[0] === 'P') {
            return $this->validateRuralProducer($raw);
        }

        return $this->validateCommercialIndustrial($raw);
    }

    private function validateCommercialIndustrial(string $raw): bool
    {
        $d = $this->digits($raw);
        if (strlen($d) !== 12 || $this->allSameDigits($d)) {
            return false;
        }

        // DV1 (position 9): weights [1,3,4,5,6,7,8,10]; dv = (sum % 11); 10 => 0
        $dv1 = $this->dvSpResto($this->toIntArray(substr($d, 0, 8)), [1, 3, 4, 5, 6, 7, 8, 10]);
        if ((int)$d[8] !== $dv1) {
            return false;
        }

        // DV2 (position 12): weights [3,2,10,9,8,7,6,5,4,3,2] over digits 0-7 + dv1 + digits 9-10
        $body2 = substr($d, 0, 8) . $dv1 . substr($d, 9, 2);
        $dv2 = $this->dvSpResto($this->toIntArray($body2), [3, 2, 10, 9, 8, 7, 6, 5, 4, 3, 2]);

        return (int)$d[11] === $dv2;
    }

    private function validateRuralProducer(string $raw): bool
    {
        // Strip non-digits — the 'P' prefix is not part of the numeric base
        $digits = $this->digits($raw);

        if (strlen($digits) !== 12 || $this->allSameDigits($digits)) {
            return false;
        }

        // Single DV at position 9, computed over the first 8 digits
        $base8 = substr($digits, 0, 8);
        $dv = $this->dvSpResto($this->toIntArray($base8), [1, 3, 4, 5, 6, 7, 8, 10]);

        return (int)$digits[8] === $dv;
    }

    /**
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     */
    private function dvSpResto(array $digits, array $weights): int
    {
        // SP uses (sum % 11) directly as DV; remainder 10 → 0
        $rest = $this->sumProducts($digits, $weights) % 11;

        return ($rest === 10) ? 0 : $rest;
    }
}
