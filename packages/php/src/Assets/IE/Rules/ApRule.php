<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Amapá (AP) IE numbers.
 *
 * 9 digits, prefix 03. DV = 11 - ((sum + p) % 11) with range-based offsets p/d:
 *  - [3000001..3017000]: p=5, d=0
 *  - [3017001..3019022]: p=9, d=1
 *  - otherwise: p=0, d=0
 * DV=10 → 0; DV=11 → d.
 */
final class ApRule extends AbstractStateRule
{
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
