<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Acre (AC) IE numbers.
 *
 * 13 digits, prefix 01. Two Mod-11 DVs (rest < 2 → 0):
 *  - DV1: weights [4,3,2,9,8,7,6,5,4,3,2] over first 11 digits
 *  - DV2: weights [5,4,3,2,9,8,7,6,5,4,3,2] over first 11 digits + DV1
 */
final class AcRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || strlen($digits) !== 13 || $this->allSameDigits($digits)) {
            return false;
        }

        // prefix must be "01"
        if (substr($digits, 0, 2) !== '01') {
            return false;
        }

        return $this->validate13($digits);
    }

    private function validate13(string $digits): bool
    {
        // DV1 (index 11)
        $base11 = substr($digits, 0, 11);
        $dv1 = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base11),
            [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        if ((int)$digits[11] !== $dv1) {
            return false;
        }

        // DV2 (index 12) — base = 11 digits + dv1
        $base12 = $base11 . (string)$dv1;
        $dv2 = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base12),
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int)$digits[12] === $dv2;
    }

    /**
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;

        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
