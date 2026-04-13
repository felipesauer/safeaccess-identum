<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\CPF;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;

/**
 * Validates Brazilian CPF (Cadastro de Pessoas Físicas) numbers.
 *
 * Applies Mod11 check-digit algorithm with two verification digits.
 *
 * @api
 */
final class CPFValidation extends AbstractValidatableDocument
{
    /**
     * Domain validation for CPF:
     * - Must have 11 digits
     * - Must not be a repeated sequence (e.g., 000..., 111..., ...)
     * - Must match both check digits (Mod11)
     *
     * @return bool
     */
    protected function doValidate(): bool
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        // CPF must have exactly 11 digits
        if (strlen($digits) !== 11) {
            return false;
        }

        // Guard: Receita Federal (Brazilian tax authority) reserves all 11-same-digit sequences
        // (e.g., 000...000, 111...111) as invalid forever — no valid CPF exists with all same digits.
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        // ===== First Verification Digit (DV1) =====
        // Algorithm: weighted sum of first 9 digits (weights 10 down to 2) modulo 11.
        // If remainder < 2, DV1 = 0; otherwise DV1 = 11 - remainder.
        $sum = 0;
        for ($i = 0, $w = 10; $i < 9; $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }
        $rest = $sum % 11;
        $dv1  = ($rest < 2) ? 0 : 11 - $rest;

        // ===== Second Verification Digit (DV2) =====
        // Algorithm: weighted sum of first 9 digits PLUS DV1 (weights 11 down to 2) modulo 11.
        // If remainder < 2, DV2 = 0; otherwise DV2 = 11 - remainder.
        // Note: the 10th digit slot (index 9) in the weighted sum is DV1, followed by original digits 10–11 (indices 8–9).
        $sum = 0;
        for ($i = 0, $w = 11; $i < 10; $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }
        $rest = $sum % 11;
        $dv2  = ($rest < 2) ? 0 : 11 - $rest;

        // Final verification: check if the computed DV1/DV2 match the digits at positions 9 and 10
        if ($digits[9] !== (string) $dv1 || $digits[10] !== (string) $dv2) {
            return false;
        }

        return true;
    }
}
