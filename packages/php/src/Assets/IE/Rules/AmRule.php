<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Amazonas (AM) IE numbers.
 *
 * 9 digits, prefix 04. Single Mod-11 DV (rest < 2 → 0).
 * Weights [9,8,7,6,5,4,3,2] over first 8 digits.
 */
final class AmRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || strlen($digits) !== 9 || $this->allSameDigits($digits)) {
            return false;
        }

        // must start with '04'
        if (substr($digits, 0, 2) !== '04') {
            return false;
        }

        return $this->validate9($digits);
    }

    private function validate9(string $digits): bool
    {
        $base8 = substr($digits, 0, 8);

        $dv = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base8),
            [9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int)$digits[8] === $dv;
    }

    /**
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum  = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;

        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
