<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class AlRule extends AbstractStateRule
{
    /**
     * Entry point for Alagoas IE validation.
     *
     * Requirements:
     * - Must have exactly 9 digits after normalization.
     * - Must start with prefix "24".
     * - Not all digits can be the same.
     * - Single check digit at position 9.
     *
     * @param string $ie Raw IE string (masked or unmasked)
     * @return bool True if valid, false otherwise
     */
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

    /**
     * Validates AL 9-digit IE.
     *
     * Algorithm:
     *  - sum = Σ(base8[i] * weights[i]) with weights [9,8,7,6,5,4,3,2]
     *  - dv  = (sum * 10) % 11
     *  - if dv == 10 then dv = 0
     *  - compare with last digit
     *
     * @param string $digits Numeric string with 9 digits
     * @return bool
     */
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
