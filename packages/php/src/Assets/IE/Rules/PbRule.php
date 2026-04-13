<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class PbRule extends AbstractStateRule
{
    /**
     * Entry point for Paraíba IE validation (9 digits).
     *
     * @param string $ie
     * @return bool
     */
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        if ($digits === '' || strlen($digits) !== 9 || $this->allSameDigits($digits)) {
            return false;
        }

        $dv = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);

        return (int) $digits[8] === $dv;
    }

    /**
     * Mod 11 helper.
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum = self::sumProducts($digits, $weights);
        $rest = $sum % 11;
        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
