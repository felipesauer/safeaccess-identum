<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

final class ToRule extends AbstractStateRule
{
    /**
     * Entry point for Tocantins IE validation.
     * Accepted lengths: 9 or 11.
     * For 11-digit IE, ignore positions 3-4 if they are in {01,02,03,99} when computing the DV.
     *
     * @param string $ie
     * @return bool
     */
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        $len = strlen($digits);
        if ($digits === '' || ($len !== 9 && $len !== 11) || $this->allSameDigits($digits)) {
            return false;
        }

        if ($len === 9) {
            $dv = $this->dvMod11Lt2Eq0($this->toIntArray(substr($digits, 0, 8)), [9, 8, 7, 6, 5, 4, 3, 2]);
            return (int)$digits[8] === $dv;
        }

        $mid = substr($digits, 2, 2);
        if (!in_array($mid, ['01', '02', '03', '99'], true)) {
            return false;
        }

        $calc = substr($digits, 0, 2) . substr($digits, 4, 6); // remove pos 3-4
        $dv = $this->dvMod11Lt2Eq0($this->toIntArray($calc), [9, 8, 7, 6, 5, 4, 3, 2]);

        return (int)$digits[10] === $dv;
    }

    /**
     * Mod 11 helper.
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod11Lt2Eq0(array $digits, array $weights): int
    {
        $sum = self::sumProducts($digits, $weights);
        $rest = $sum % 11;
        return ($rest < 2) ? 0 : 11 - $rest;
    }
}
