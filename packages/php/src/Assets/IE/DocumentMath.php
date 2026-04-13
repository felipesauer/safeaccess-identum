<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE;

/**
 * Arithmetic helpers shared by all state-specific IE rules.
 *
 * @internal
 */
trait DocumentMath
{
    /**
     * Strips non-numeric characters from a raw value.
     *
     * @param string $v
     * @param string $pattern
     * @return string
     */
    public function digits(string $v, string $pattern = '/\D+/'): string
    {
        return preg_replace($pattern, '', $v) ?? '';
    }

    /**
     * @param string $digits
     * @return array<int>
     */
    public function toIntArray(string $digits): array
    {
        return array_map('intval', str_split($digits));
    }

    /** Returns true when every character in $digits is the same ('000', '111', etc.). */
    public function allSameDigits(string $digits): bool
    {
        return $digits !== '' && count(array_unique(str_split($digits))) === 1;
    }

    /**
     * Weighted sum: sum of (digit[i] × weight[i]) over min(len(digits), len(weights)).
     *
     * @param array<int> $digits
     * @param array<int> $weights
     * @return int
     */
    public function sumProducts(array $digits, array $weights): int
    {
        $n = min(count($digits), count($weights));
        $sum = 0;
        for ($i = 0; $i < $n; $i++) {
            $sum += $digits[$i] * $weights[$i];
        }
        return $sum;
    }
}
