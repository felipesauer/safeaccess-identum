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

        // length
        if (strlen($digits) !== 11) {
            return false;
        }

        // repeated sequence
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        // 1st check digit
        $sum = 0;

        for ($i = 0, $w = 10; $i < 9; $i++, $w--) {
            $sum += ((int) $digits[$i]) * $w;
        }

        $rest = $sum % 11;
        $dv1  = ($rest < 2) ? 0 : 11 - $rest;

        // 2nd check digit
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
