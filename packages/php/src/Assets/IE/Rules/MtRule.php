<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Mato Grosso (MT) IE numbers.
 *
 * 11 digits, single Mod-11 DV: dv = 11 - rest; if dv ≥ 10 → 0.
 * Weights [3,2,9,8,7,6,5,4,3,2] over first 10 digits.
 */
final class MtRule extends AbstractStateRule
{
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
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     */
    private function dvMod11Ge10Eq0(array $digits, array $weights): int
    {
        $sum = self::sumProducts($digits, $weights);
        $rest = $sum % 11;
        $dv = 11 - $rest;
        return ($dv >= 10) ? 0 : $dv;
    }
}
