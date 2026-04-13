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
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        if (strlen($digits) !== 11) {
            return false;
        }

        // Receita Federal: any 11-same-digit sequence is reserved and permanently invalid
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        // DV1: Mod 11 over the first 9 digits, weights 10..2
        $sum = 0;

        for ($i = 0, $w = 10; $i < 9; $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }

        $rest = $sum % 11;
        $dv1  = ($rest < 2) ? 0 : 11 - $rest;

        // DV2: Mod 11 over the first 10 digits (including DV1), weights 11..2
        $sum = 0;

        for ($i = 0, $w = 11; $i < 10; $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }

        $rest = $sum % 11;
        $dv2  = ($rest < 2) ? 0 : 11 - $rest;

        if ($digits[9] !== (string) $dv1 || $digits[10] !== (string) $dv2) {
            return false;
        }

        return true;
    }
}
