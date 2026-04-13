<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE\Rules;

use SafeAccess\Identum\Assets\IE\AbstractStateRule;

/**
 * Validates Bahia (BA) IE numbers.
 *
 * BA uses two different check-digit methods depending on d[1]:
 *  - d[1] ∈ {0,1,2,3,4,5,8} → Mod-10 digit-sum (Luhn-style)
 *  - d[1] ∈ {6,7,9}          → Mod-11 (result ≥ 10 → DV = 0)
 *
 * Accepts 8 or 9 digit formats. DV2 is computed first, then DV1.
 */
final class BaRule extends AbstractStateRule
{
    public function execute(string $ie): bool
    {
        $digits = $this->digits($ie);
        $len = strlen($digits);

        if ($digits === '' || ($len !== 8 && $len !== 9) || $this->allSameDigits($digits)) {
            return false;
        }

        $bodyLen = $len - 2;
        $body = substr($digits, 0, $bodyLen);
        $second = (int)$digits[1];
        $useMod10 = in_array($second, [0, 1, 2, 3, 4, 5, 8], true);

        // DV2
        $w2 = $this->weightsDesc($bodyLen + 1, 2);
        $dv2 = $useMod10
            ? $this->dvMod10DigitSum($this->toIntArray($body), $w2) // phpcs:ignore
            : $this->dvMod11Ge10Eq0($this->toIntArray($body), $w2); // phpcs:ignore

        // DV1 (with DV2 appended)
        $w1 = $this->weightsDesc($bodyLen + 2, 2);
        $dv1 = $useMod10
            ? $this->dvMod10DigitSum($this->toIntArray($body . $dv2), $w1) // phpcs:ignore
            : $this->dvMod11Ge10Eq0($this->toIntArray($body . $dv2), $w1); // phpcs:ignore

        return (int) $digits[$bodyLen] === $dv1 && (int) $digits[$bodyLen + 1] === $dv2;
    }

    /**
     * Returns descending weights from $from to $to (inclusive).
     *
     * @param int $from
     * @param int $to
     * @return array<int,int>
     */
    private function weightsDesc(int $from, int $to): array
    {
        $w = [];
        for ($i = $from; $i >= $to; $i--) {
            $w[] = $i;
        }
        return $w;
    }

    /**
     * Mod 11: dv = 11 - (sum % 11); if dv >= 10 => 0.
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod11Ge10Eq0(array $digits, array $weights): int
    {
        $sum  = self::sumProducts($digits, $weights);
        $rest = $sum % 11;
        $dv   = 11 - $rest;
        return ($dv >= 10) ? 0 : $dv;
    }

    /**
     * Mod 10 with digit-sum on products (Luhn-like aggregation).
     *
     * @param array<int,int> $digits
     * @param array<int,int> $weights
     * @return int
     */
    private function dvMod10DigitSum(array $digits, array $weights): int
    {
        $sum = 0;
        $n = min(count($digits), count($weights));
        for ($i = 0; $i < $n; $i++) {
            $prod = $digits[$i] * $weights[$i];
            $sum += array_sum(array_map('intval', str_split((string)$prod)));
        }
        return (10 - ($sum % 10)) % 10;
    }
}
