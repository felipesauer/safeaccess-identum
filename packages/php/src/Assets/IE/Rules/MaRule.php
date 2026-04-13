<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/** Validates Maranhão (MA) IE numbers. 9 digits, prefix 12, single Mod-11 DV (rest < 2 → 0). */
final class MaRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        if ($digits === '' || strlen($digits) !== 9 || $this->allSameDigits($digits)) {
            return false;
        }

        if (substr($digits, 0, 2) !== '12') {
            return false;
        }

        $dv = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
        return (int) $digits[8] === $dv;
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
