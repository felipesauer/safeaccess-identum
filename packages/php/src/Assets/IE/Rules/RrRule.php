<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Roraima (RR) IE numbers.
 *
 * 9 digits. DV = sum % 9 with weights [1,2,3,4,5,6,7,8] over first 8 digits.
 */
final class RrRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || strlen($digits) !== 9 || $this->allSameDigits($digits)) {
            return false;
        }

        $sum = self::sumProducts($this->toIntArray(substr($digits, 0, 8)), [1, 2, 3, 4, 5, 6, 7, 8]);
        $dv = $sum % 9;

        return (int)$digits[8] === $dv;
    }
}
