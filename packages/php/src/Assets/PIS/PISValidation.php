<?php

declare(strict_types=1);

namespace SafeAccess\Identum\Assets\PIS;

use SafeAccess\Identum\Contracts\AbstractValidatableDocument;
use SafeAccess\Identum\Contracts\ReasonCode;

/**
 * Validates Brazilian PIS/PASEP (Programa de Integração Social) numbers.
 *
 * @api
 */
final class PISValidation extends AbstractValidatableDocument
{
    protected function documentName(): string
    {
        return 'pis';
    }

    /**
     * Generates a valid PIS/PASEP.
     *
     * @param bool $formatted When true, returns the masked form (000.00000.00-0).
     */
    public static function generate(bool $formatted = false): string
    {
        $w = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        do {
            $base = '';
            for ($i = 0; $i < 10; $i++) {
                $base .= random_int(0, 9);
            }
        } while (preg_match('/^(\d)\1{9}$/', $base) === 1);

        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $base[$i]) * $w[$i];
        }
        $dv = 11 - ($sum % 11);
        if ($dv === 10 || $dv === 11) {
            $dv = 0;
        }

        $value = $base . $dv;

        return $formatted ? (new self($value))->format() : $value;
    }

    protected function doValidate(): ?ReasonCode
    {
        // Strip all non-digit characters to get a clean numeric string
        $digits = $this->sanitize($this->raw());

        // PIS must have exactly 11 digits
        if (strlen($digits) !== 11) {
            return ReasonCode::WrongLength;
        }

        // Guard: CEF (Caixa Econômica Federal) reserves all 11-same-digit sequences
        // as invalid forever—no valid PIS exists with all same digits.
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return ReasonCode::KnownInvalid;
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
        return (string) $dv === $digits[10] ? null : ReasonCode::BadCheckDigit;
    }

    /** Canonical PIS mask: 000.00000.00-0. */
    protected function mask(string $stripped): string
    {
        return preg_replace('/^(\d{3})(\d{5})(\d{2})(\d{1})$/', '$1.$2.$3-$4', $stripped) ?? $stripped;
    }
}
