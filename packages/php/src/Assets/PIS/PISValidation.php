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
        $digits = preg_replace('/\D+/', '', $this->raw()) ?? '';

        if (strlen($digits) !== 11) {
            return false;
        }

        // CEF: homogeneous sequences are reserved and always invalid
        if (preg_match('/^(\d)\1{10}$/', $digits) === 1) {
            return false;
        }

        // Mod 11 with weights [3,2,9,8,7,6,5,4,3,2] over the first 10 digits
        $w = [3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += ((int) $digits[$i]) * $w[$i];
        }

        $rest = $sum % 11;
        $dv = 11 - $rest;

        // Remainder 10 or 11 are not representable as a single digit — DV becomes 0
        if ($dv === 10 || $dv === 11) {
            $dv = 0;
        }

        return (string) $dv === $digits[10];
    }
}
