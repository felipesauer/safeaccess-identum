<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class AcRule extends AbstractStateRule
{
    /**
     * Entry point for Acre IE validation.
     *
     * Requirements:
     * - Must have 13 digits after normalization.
     * - Must start with prefix "01".
     * - Not all digits can be equal.
     * - Two check digits (positions 12 and 13) using Mod 11 (rest < 2 => 0; else 11 - rest).
     *
     * @param string $ie Raw IE string (masked or unmasked)
     * @return bool True if valid, false otherwise
     */
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);

        if ($digits === '' || strlen($digits) !== 13 || $this->allSameDigits($digits)) {
            return false;
        }

        // prefix must be "01"
        if (substr($digits, 0, 2) !== '01') {
            return false;
        }

        return $this->validate13($digits);
    }

    /**
     * Validates AC 13-digit IE.
     *
     * DV1:
     *   - base: first 11 digits
     *   - weights: [4,3,2,9,8,7,6,5,4,3,2]
     *   - policy: rest < 2 => 0; else 11 - rest
     * DV2:
     *   - base: first 11 digits + DV1 (total 12 digits)
     *   - weights: [5,4,3,2,9,8,7,6,5,4,3,2]
     *   - policy: rest < 2 => 0; else 11 - rest
     *
     * @param string $digits Numeric string with 13 digits
     * @return bool
     */
    private function validate13(string $digits): bool
    {
        // DV1 (index 11)
        $base11 = substr($digits, 0, 11);
        $dv1 = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base11),
            [4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        if ((int)$digits[11] !== $dv1) {
            return false;
        }

        // DV2 (index 12) — base = 11 digits + dv1
        $base12 = $base11 . (string)$dv1;
        $dv2 = $this->dvMod11Lt2Eq0(
            $this->toIntArray($base12),
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int)$digits[12] === $dv2;
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
        $sum = $this->sumProducts($digits, $weights);
        $rest = $sum % 11;

        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
