<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Rondônia (RO) IE numbers.
 *
 * Two accepted formats:
 *  - 14 digits (current):  single DV, Mod-11 rest < 2 → 0, weights [6,5,4,3,2,9,8,7,6,5,4,3,2]
 *  - 9 digits (legacy): 3-digit municipality code + 5-digit company code + 1 DV.
 *    DV = 11 - (sum % 11); if DV ≥ 10, subtract 10 (maps to 0 or 1).
 */
final class RoRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || $this->allSameDigits($digits)) {
            return false;
        }

        $len = strlen($digits);

        // Current (14-digit) or legacy (9-digit) format
        if ($len === 14) {
            return $this->validateCurrent14($digits);
        }
        if ($len === 9) {
            return $this->validateLegacy9($digits);
        }

        return false;
    }

    private function validateCurrent14(string $digits): bool
    {
        $base13 = substr($digits, 0, 13);
        $dvCalc = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base13),
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int)$digits[13] === $dvCalc;
    }

    private function validateLegacy9(string $digits): bool
    {
        $empresa5 = substr($digits, 3, 5);
        $sum = $this->sumProducts($this->toIntArray($empresa5), [6, 5, 4, 3, 2]);
        $rest = $sum % 11;

        $dv = 11 - $rest;
        if ($dv >= 10) {
            $dv -= 10; // 10->0, 11->1
        }

        return (int)$digits[8] === $dv;
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
