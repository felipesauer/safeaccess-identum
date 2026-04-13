<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Rio Grande do Norte (RN) IE numbers.
 *
 * 9 or 10 digits, prefix 20. Single Mod-11 DV (rest < 2 → 0).
 * 9-digit: weights [9,8,7,6,5,4,3,2]; 10-digit: weights [10,9,8,7,6,5,4,3,2].
 */
final class RnRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        $len = strlen($digits);

        if ($digits === '' || ($len !== 9 && $len !== 10) || $this->allSameDigits($digits)) {
            return false;
        }

        if (substr($digits, 0, 2) !== '20') {
            return false;
        }

        if ($len === 9) {
            $dv = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
            return (int)$digits[8] === $dv;
        }

        $dv = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 9)), [10, 9, 8, 7, 6, 5, 4, 3, 2]);
        return (int) $digits[9] === $dv;
    }

    /**
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum = self::sumProducts($digits, $weights);
        $rest = $sum % 11;
        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
