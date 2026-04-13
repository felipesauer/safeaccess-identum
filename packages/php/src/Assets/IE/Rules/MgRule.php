<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class MgRule extends AbstractStateRule
{
    /**
     * Entry point for Minas Gerais IE validation (13 digits).
     *
     * Rules:
     * - D1: Insert '0' at position 4 over the first 11 digits (making 12),
     *      then Mod 10 (digit-sum) with alternating 1/2 multipliers.
     * - D2: Mod 11 with weights [3,2,11,10,9,8,7,6,5,4,3,2] over (first 11 digits + D1).
     *
     * @param string $ie
     * @return bool
     */
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        if ($digits === '' || strlen($digits) !== 13 || $this->allSameDigits($digits)) {
            return false;
        }

        $base11 = substr($digits, 0, 11);
        $aux = substr($base11, 0, 3) . '0' . substr($base11, 3); // 12 digits
        // D1: alternating 1 and 2 (sum of digits of products)
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $mult = ($i % 2 === 0) ? 1 : 2;
            $prod = (int) $aux[$i] * $mult;
            $sum += array_sum(array_map('intval', str_split((string)$prod)));
        }

        $d1 = (10 - ($sum % 10)) % 10;
        if ((int) $digits[11] !== $d1) {
            return false;
        }

        // D2
        $weights = [3, 2, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum2 = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum2 += (int) ($i < 11 ? $digits[$i] : $d1) * $weights[$i];
        }
        $rest = $sum2 % 11;
        $d2 = ($rest < 2) ? 0 : 11 - $rest;

        return (int) $digits[12] === $d2;
    }
}
