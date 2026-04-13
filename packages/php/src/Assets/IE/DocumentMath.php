<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\IE;

/**
 * Reusable arithmetic helpers for document check-digit calculations.
 *
 * Provides digit extraction, weighted sums, and repeated-sequence checks
 * used across all state-specific IE validation rules.
 *
 * @internal
 */
trait DocumentMath
{
    /**
     * Remove all non-numeric characters, returning a string of digits only.
     *
     * Example: "12.345-67" → "1234567"
     *
     * @param string $v Raw value.
     * @param string $patters
     * @return string Digits-only string.
     */
    public function digits(string $v, string $patters = '/\D+/'): string
    {
        return preg_replace($patters, '', $v) ?? '';
    }

    /**
     * Convert a string of digits into an array of integers.
     *
     * Example: "1234" → [1, 2, 3, 4]
     *
     * @param string $digits Digits-only string.
     * @return array<int> List of integers corresponding to each digit.
     */
    public function toIntArray(string $digits): array
    {
        return array_map('intval', str_split($digits));
    }

    /**
     * Check whether all digits in a string are the same.
     *
     * Examples:
     * - "000000" → true
     * - "111111" → true
     * - "123456" → false
     *
     * @param string $digits Digits-only string.
     * @return bool True if all digits are identical, false otherwise.
     */
    public function allSameDigits(string $digits): bool
    {
        return $digits !== '' && count(array_unique(str_split($digits))) === 1;
    }

    /**
     * Compute the weighted sum of digit × weight products.
     *
     * Example:
     * digits = [1, 2, 3], weights = [2, 3, 4]
     * → (1×2) + (2×3) + (3×4) = 20
     *
     * The computation uses the shortest length between the digits and weights arrays.
     *
     * @param array<int> $digits Array of integers representing digits.
     * @param array<int> $weights Array of integers representing weights.
     * @return int Weighted sum.
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
