<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class GoRule extends AbstractStateRule
{
    /**
     * Entry point for Goiás IE validation (9 digits).
     *
     * Special rule when remainder == 1 for range [10103105 .. 10119997].
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

        $base8 = substr($digits, 0, 8);
        $sum = self::sumProducts($this->toIntArray($base8), [9, 8, 7, 6, 5, 4, 3, 2]);
        $rest = $sum % 11;
        $dv = 11 - $rest;

        if ($rest === 0) {
            $dv = 0;
        } elseif ($rest === 1) {
            $num = (int)$base8;
            $dv = ($num >= 10103105 && $num <= 10119997) ? 1 : 0;
        } elseif ($dv >= 10) {
            $dv = 0; // @codeCoverageIgnore
        }

        return (int) $digits[8] === $dv;
    }
}
