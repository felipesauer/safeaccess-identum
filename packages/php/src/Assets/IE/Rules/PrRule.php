<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Paraná (PR) IE numbers.
 *
 * 10 digits, two Mod-11 DVs (rest < 2 → 0):
 *  - DV1: weights [3,2,7,6,5,4,3,2] over first 8 digits
 *  - DV2: weights [4,3,2,7,6,5,4,3,2] over first 9 digits
 */
final class PrRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        if ($digits === '' || strlen($digits) !== 10 || $this->allSameDigits($digits)) {
            return false;
        }

        $dv1 = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 8)), [3, 2, 7, 6, 5, 4, 3, 2]);
        if ((int) $digits[8] !== $dv1) {
            return false;
        }

        $dv2 = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 9)), [4, 3, 2, 7, 6, 5, 4, 3, 2]);
        return (int) $digits[9] === $dv2;
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
