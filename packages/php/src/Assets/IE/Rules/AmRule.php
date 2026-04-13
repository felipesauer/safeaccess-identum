<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class AmRule extends AbstractStateRule
{
    /**
     * Entry point for Amazonas IE validation.
     *
     * Requirements:
     * - Must have exactly 9 digits after normalization.
     * - Must start with prefix "04".
     * - Not all digits can be the same.
     * - Single check digit at position 9 (index 8).
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

        // must start with '04'
        if (substr($digits, 0, 2) !== '04') {
            return false;
        }

        return $this->validate9($digits);
    }

    /**
     * Validates AM 9-digit IE.
     *
     * Algorithm:
     *  - sum = Σ(base8[i] * weights[i]) with weights [9,8,7,6,5,4,3,2]
     *  - rest = sum % 11
     *  - dv = (rest < 2) ? 0 : (11 - rest)
     *  - compare with last digit
     *
     * @param string $digits Numeric string with 9 digits
     * @return bool
     */
    private function validate9(string $digits): bool
    {
        $base8 = substr($digits, 0, 8);

        $dv = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base8),
            [9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int)$digits[8] === $dv;
    }

    /**
     * Mod 11 helper: if rest < 2 => 0; else 11 - rest.
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum  = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;

        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
