<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class ApRule extends AbstractStateRule
{
    /**
     * Entry point for Amapá IE validation.
     *
     * Requirements:
     * - Must have exactly 9 digits after normalization.
     * - Must start with prefix "03".
     * - Single check digit (index 8) with AP range constants p/d.
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

        if (substr($digits, 0, 2) !== '03') {
            return false;
        }

        $base8 = (int)substr($digits, 0, 8);
        $p = 0;
        $dConst = 0;

        if ($base8 >= 3000001 && $base8 <= 3017000) {
            $p = 5;
            $dConst = 0;
        } elseif ($base8 >= 3017001 && $base8 <= 3019022) {
            $p = 9;
            $dConst = 1;
        }

        $sum = self::sumProducts($this->toIntArray(substr($digits, 0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
        $dv = 11 - (($sum + $p) % 11);

        if ($dv === 10) {
            $dv = 0;
        } elseif ($dv === 11) {
            $dv = $dConst;
        }

        return (int) $digits[8] === $dv;
    }
}
