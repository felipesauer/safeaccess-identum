<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Distrito Federal (DF) IE numbers.
 *
 * 13 digits, prefix 07. Two Mod-11 DVs (rest < 2 → 0):
 *  - DV1: weights [4,3,2,9,8,7,6,5,4,3,2] over first 11 digits
 *  - DV2: weights [5,4,3,2,9,8,7,6,5,4,3,2] over first 11 digits + DV1
 */
final class DfRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || strlen($digits) !== 13 || $this->allSameDigits($digits)) {
            return false;
        }

        if (substr($digits, 0, 2) !== '07') {
            return false;
        }

        $base11 = substr($digits, 0, 11);
        $dv1 = $this->dvMod11Lt2Eq0($this->toIntArray($base11), [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        if ((int) $digits[11] !== $dv1) {
            return false;
        }

        $base12 = $base11 . (string)$dv1;
        $dv2 = $this->dvMod11Lt2Eq0($this->toIntArray($base12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return (int) $digits[12] === $dv2;
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
