<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class PeRule extends AbstractStateRule
{
    /**
     * Entry point for Pernambuco IE validation.
     *
     * - 14 digits: current format (single DV at the 14th position).
     * - 9 digits: legacy format (two DVs at positions 8 and 9).
     *
     * @param string $ie Raw IE string (masked or unmasked)
     * @return bool True if valid, false otherwise
     */
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || $this->allSameDigits($digits)) {
            return false;
        }

        $length = strlen($digits);

        if ($length === 14) {
            return $this->validateCurrent14($digits);
        }

        if ($length === 9) {
            return $this->validateLegacy9($digits);
        }

        return false;
    }

    /**
     * Current PE format (14 digits).
     * DV calculation: Mod 11 with policy "rest < 2 => 0; else 11 - rest".
     * Weights applied over the first 13 digits:
     * [5,4,3,2,9,8,7,6,5,4,3,2,9]
     *
     * @param string $digits 14-digit numeric IE
     * @return bool
     */
    private function validateCurrent14(string $digits): bool
    {
        $base13 = substr($digits, 0, 13);

        $dvCalc = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base13),
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2, 9]
        );

        return (int) $digits[13] === $dvCalc;
    }

    /**
     * Legacy PE format (9 digits): 7 digits core + 2 check digits.
     * D1: Mod 11 with ">= 10 => 0", weights [8,7,6,5,4,3,2] over base7.
     * D2: Mod 11 with ">= 10 => 0", weights [9,8,7,6,5,4,3,2] over (base7 + D1).
     *
     * @param string $digits 9-digit numeric IE
     * @return bool
     */
    private function validateLegacy9(string $digits): bool
    {
        $base7 = substr($digits, 0, 7);

        // D1
        $d1Calc = $this->dvMod11Ge10Eq0(
            $this->toIntArray($base7),
            [8, 7, 6, 5, 4, 3, 2]
        );

        if ((int) $digits[7] !== $d1Calc) {
            return false;
        }

        // D2 (base7 + D1)
        $d2Calc = $this->dvMod11Ge10Eq0(
            array_merge($this->toIntArray($base7), [$d1Calc]),
            [9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int) $digits[8] === $d2Calc;
    }

    /**
     * Mod 11: if rest < 2 => 0; else 11 - rest.
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;

        return ($rest < 2) ? 0 : 11 - $rest;
    }

    /**
     * Mod 11: dv = 11 - rest; if dv >= 10 => 0.
     * (Common rule used in PE legacy check digits.)
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod11Ge10Eq0(array $digits, array $weights): int
    {
        $sum = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;
        $dv = 11 - $rest;

        return ($dv >= 10) ? 0 : $dv;
    }
}
