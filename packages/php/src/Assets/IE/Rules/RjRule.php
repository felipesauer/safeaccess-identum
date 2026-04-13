<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/** Validates Rio de Janeiro (RJ) IE numbers. 8 digits, single Mod-11 DV (rest < 2 → 0). */
final class RjRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        if ($digits === '' || strlen($digits) !== 8 || $this->allSameDigits($digits)) {
            return false;
        }

        $dv = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 7)), [2, 7, 6, 5, 4, 3, 2]);
        return (int) $digits[7] === $dv;
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
