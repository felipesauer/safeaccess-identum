<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class MtRule extends AbstractStateRule
{
    /**
     * Entry point for Mato Grosso IE validation (11 digits).
     * Policy: dv = 11 - rest; if dv >= 10 => 0 (GE10_EQ0).
     *
     * @param string $ie
     * @return bool
     */
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        if ($digits === '' || strlen($digits) !== 11 || $this->allSameDigits($digits)) {
            return false;
        }

        $dv = $this->dvMod11Ge10Eq0($this->toIntArray(substr($digits, 0, 10)), [3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);

        return (int) $digits[10] === $dv;
    }

    /**
     * Mod 11: dv = 11 - rest; if dv >= 10 => 0.
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod11Ge10Eq0(array $digits, array $weights): int
    {
        $sum = self::sumProducts($digits, $weights);
        $rest = $sum % 11;
        $dv = 11 - $rest;
        return ($dv >= 10) ? 0 : $dv;
    }
}
