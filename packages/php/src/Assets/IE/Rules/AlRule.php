<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Alagoas (AL) IE numbers.
 *
 * 9 digits, prefix 24. DV = (sum × 10) % 11; if result is 10 → DV = 0.
 * Weights [9,8,7,6,5,4,3,2] over first 8 digits.
 */
final class AlRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || strlen($digits) !== 9 || $this->allSameDigits($digits)) {
            return false;
        }

        // must start with '24'
        if (substr($digits, 0, 2) !== '24') {
            return false;
        }

        return $this->validate9($digits);
    }

    private function validate9(string $digits): bool
    {
        $base8 = substr($digits, 0, 8);

        $sum = $this->sumProducts(
            $this->toIntArray($base8),
            [9, 8, 7, 6, 5, 4, 3, 2]
        );

        $dv = ($sum * 10) % 11;
        if ($dv === 10) {
            $dv = 0;
        }

        return (int)$digits[8] === $dv;
    }
}
