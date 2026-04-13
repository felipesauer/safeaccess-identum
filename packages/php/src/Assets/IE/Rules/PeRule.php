<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Pernambuco (PE) IE numbers.
 *
 * Two accepted formats:
 *  - 14 digits (current): single DV, Mod-11 rest < 2 → 0, weights [5,4,3,2,9,8,7,6,5,4,3,2,9]
 *  - 9 digits (legacy): two DVs, both " ≥ 10 → 0", weights [8,7,6,5,4,3,2] and [9,8,7,6,5,4,3,2]
 */
final class PeRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || $this->allSameDigits($digits)) {
            return false;
        }

        $length = strlen($digits);

        if ($length === 14) {
            return $this->validateCurrent14($digits);
        }

        if ($length === 9) {
            return $this->validateLegacy9($digits);
        }

        return false;
    }

    private function validateCurrent14(string $digits): bool
    {
        $base13 = substr($digits, 0, 13);

        $dvCalc = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base13),
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, 9]
        );

        return (int) $digits[13] === $dvCalc;
    }

    private function validateLegacy9(string $digits): bool
    {
        $base7 = substr($digits, 0, 7);

        // D1
        $d1Calc = $this->dvMod11Ge10Eq0(
            $this->toIntArray($base7),
            [8, 7, 6, 5, 4, 3, 2]
        );

        if ((int) $digits[7] !== $d1Calc) {
            return false;
        }

        // D2 (base7 + D1)
        $d2Calc = $this->dvMod11Ge10Eq0(
            array_merge($this->toIntArray($base7), [$d1Calc]),
            [9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int) $digits[8] === $d2Calc;
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

    /**
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     */
    private function dvMod11Ge10Eq0(array $digits, array $weights): int
    {
        $sum = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;
        $dv = 11 - $rest;

        return ($dv >= 10) ? 0 : $dv;
    }
}
