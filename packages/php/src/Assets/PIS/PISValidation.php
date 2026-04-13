<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\PIS;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian PIS/PASEP (Programa de Integração Social) numbers.
 *
 * @api
 */
final class PISValidation extends AbstractValidatableDocument
{
    protected function doValidate(): bool
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // PIS must have exactly 11 digits
        if (strlen($digits) !== 11) {
            return false;
        }

        // Guard: CEF (Caixa Econômica Federal) reserves all 11-same-digit sequences
        // as invalid forever—no valid PIS exists with all same digits.
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        // ===== Verification Digit (DV) =====
        // Algorithm: weighted sum of first 10 digits (weights [3,2,9,8,7,6,5,4,3,2]) modulo 11.
        // DV = 11 - remainder; if DV is 10 or 11 (not representable as a single digit), DV becomes 0.
        $w = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $digits[$i]) * $w[$i];
        }

        $rest = $sum % 11;
        $dv = 11 - $rest;

        // Edge case: if DV is 10 or 11, it cannot fit in a single digit → set to 0
        if ($dv === 10 || $dv === 11) {
            $dv = 0;
        }

        // Final verification: check if the computed DV matches the digit at position 10
        return (string) $dv === $digits[10];
    }
}
